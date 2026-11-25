<?php

namespace App\Services;

use App\Models\Recipe;
use Framework\Http\Request;

/**
 * Spoločné vyhľadávanie, filtrovanie, triedenie a stránkovanie receptov.
 * Používajú ho obrazovky Recepty, Obľúbené aj Moje recepty – líšia sa len „rozsahom“ (scope).
 */
class RecipeQuery
{
    public const PER_PAGE = 6;

    public const SORTS = [
        'odporucane' => 'Odporúčané',
        'najnovsie' => 'Najnovšie',
        'najrychlejsie' => 'Najrýchlejšie',
        'kalorie' => 'Najmenej kalórií',
        'hodnotenie' => 'Najlepšie hodnotené',
    ];

    public const DIETS = [
        'vegetarian' => ['Vegetariánske', '`is_vegetarian` = 1'],
        'vegan' => ['Vegánske', '`is_vegan` = 1'],
        'gluten' => ['Bez lepku', '`is_gluten_free` = 1'],
        'lactose' => ['Bez laktózy', '`is_lactose_free` = 1'],
    ];

    public const TIMES = [
        '' => 'Ľubovoľný čas',
        '15' => 'Do 15 minút',
        '30' => 'Do 30 minút',
        '60' => 'Do 60 minút',
    ];

    public string $q = '';
    public string $category = '';
    /** @var string[] */
    public array $diets = [];
    public string $time = '';
    public string $sort = 'odporucane';
    public int $page = 1;

    private ?string $scopeWhere = null;
    private array $scopeParams = [];

    public static function fromRequest(Request $request): self
    {
        $query = new self();
        $query->q = trim((string)$request->get('q'));
        $category = (string)$request->get('category');
        $query->category = in_array($category, Recipe::CATEGORIES, true) ? $category : '';
        $diets = $request->get('diet');
        $query->diets = is_array($diets) ? array_values(array_intersect(array_keys(self::DIETS), $diets)) : [];
        $time = (string)$request->get('time');
        $query->time = array_key_exists($time, self::TIMES) ? $time : '';
        $sort = (string)$request->get('sort');
        $query->sort = array_key_exists($sort, self::SORTS) ? $sort : 'odporucane';
        $query->page = max(1, (int)$request->get('page'));
        return $query;
    }

    /**
     * Obmedzí výsledky, napr. na obľúbené alebo recepty jedného autora.
     */
    public function scope(string $where, array $params = []): self
    {
        $this->scopeWhere = $where;
        $this->scopeParams = $params;
        return $this;
    }

    public function hasActiveFilters(): bool
    {
        return $this->q !== '' || $this->category !== '' || $this->diets !== [] || $this->time !== '';
    }

    /**
     * Podmienky WHERE okrem kategórie (tie sa použijú aj na počty v bočnom paneli).
     *
     * @return array{0: string[], 1: array}
     */
    private function baseConditions(): array
    {
        $where = [];
        $params = [];
        if ($this->scopeWhere !== null) {
            $where[] = '(' . $this->scopeWhere . ')';
            $params = array_merge($params, $this->scopeParams);
        }
        if ($this->q !== '') {
            $like = '%' . $this->q . '%';
            $where[] = '(`title` LIKE ? OR `summary` LIKE ? OR `id` IN (SELECT `recipe_id` FROM `ingredients` WHERE `name` LIKE ?))';
            array_push($params, $like, $like, $like);
        }
        foreach ($this->diets as $diet) {
            $where[] = self::DIETS[$diet][1];
        }
        if ($this->time !== '') {
            $where[] = '(`prep_time` + `cook_time`) <= ?';
            $params[] = (int)$this->time;
        }
        return [$where, $params];
    }

    /** @return array{0: string|null, 1: array} */
    private function fullConditions(): array
    {
        [$where, $params] = $this->baseConditions();
        if ($this->category !== '') {
            $where[] = '`category` = ?';
            $params[] = $this->category;
        }
        return [$where ? implode(' AND ', $where) : null, $params];
    }

    private function orderBy(): string
    {
        $avg = '(SELECT AVG(`rating`) FROM `reviews` WHERE `reviews`.`recipe_id` = `recipes`.`id`)';
        return match ($this->sort) {
            'najnovsie' => '`created_at` DESC, `id` DESC',
            'najrychlejsie' => '(`prep_time` + `cook_time`) ASC, `id` DESC',
            'kalorie' => '`calories` ASC, `id` DESC',
            'hodnotenie' => "COALESCE($avg, 0) DESC, `id` ASC",
            // odporúčané = súčet hodnotení (zohľadní kvalitu aj počet), potom najnovšie
            default => '(SELECT COALESCE(SUM(`rating`), 0) FROM `reviews` WHERE `reviews`.`recipe_id` = `recipes`.`id`) DESC, `created_at` DESC',
        };
    }

    public function total(): int
    {
        [$where, $params] = $this->fullConditions();
        return Recipe::getCount($where, $params);
    }

    public function pageCount(): int
    {
        return max(1, (int)ceil($this->total() / self::PER_PAGE));
    }

    /** @return Recipe[] */
    public function results(): array
    {
        [$where, $params] = $this->fullConditions();
        $this->page = min($this->page, $this->pageCount());
        return Recipe::getAll($where, $params, $this->orderBy(), self::PER_PAGE, ($this->page - 1) * self::PER_PAGE);
    }

    /**
     * Počty receptov v jednotlivých kategóriách (rešpektujú ostatné filtre).
     *
     * @return array<string, int> kľúč '' = všetky
     */
    public function categoryCounts(): array
    {
        [$where, $params] = $this->baseConditions();
        $sql = 'SELECT `category`, COUNT(*) AS `cnt` FROM `recipes`'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' GROUP BY `category`';
        $counts = array_fill_keys(Recipe::CATEGORIES, 0);
        $all = 0;
        foreach (Recipe::executeRawSQL($sql, $params) as $row) {
            $counts[$row['category']] = (int)$row['cnt'];
            $all += (int)$row['cnt'];
        }
        return ['' => $all] + $counts;
    }

    /**
     * Parametre aktuálneho filtra pre odkazy (stránkovanie, kategórie…).
     */
    public function params(array $override = []): array
    {
        $params = [
            'q' => $this->q !== '' ? $this->q : null,
            'category' => $this->category !== '' ? $this->category : null,
            'diet' => $this->diets ?: null,
            'time' => $this->time !== '' ? $this->time : null,
            'sort' => $this->sort !== 'odporucane' ? $this->sort : null,
            'page' => $this->page > 1 ? $this->page : null,
        ];
        $params = array_merge($params, $override);
        return array_filter($params, fn($v) => $v !== null && $v !== '' && $v !== []);
    }
}
