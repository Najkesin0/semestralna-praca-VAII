<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\MealPlan;
use App\Models\Recipe;

/**
 * Logika týždenného plánu: mriežka dní × jedál, nákupný zoznam a tipy.
 */
class PlanService
{
    /** Bežné suroviny zo špajze, ktoré nemá zmysel spomínať v tipe „využiješ v…“ */
    private const STAPLES = ['olivový olej', 'maslo', 'soľ', 'cukor', 'hladká múka', 'med', 'cesnak'];

    /** Zjednotenie jednotiek pri sčítavaní rovnakých surovín */
    private const UNIT_ALIASES = ['strúčik' => 'strúčiky', 'plátok' => 'plátky'];

    /**
     * Mriežka plánu: [Y-m-d][meal_type] => MealPlan
     *
     * @param MealPlan[] $entries
     * @return MealPlan
     */
    public static function grid(\DateTimeImmutable $monday, array $entries): array
    {
        $grid = [];
        for ($i = 0; $i < 7; $i++) {
            $grid[$monday->modify("+$i days")->format('Y-m-d')] = [];
        }
        foreach ($entries as $entry) {
            $grid[$entry->getPlanDate()][$entry->getMealType()] = $entry;
        }
        return $grid;
    }

    /**
     * ID receptov, ktoré má používateľ naplánované v danom týždni.
     *
     * @return array<int, true>
     */
    public static function recipeIdsInWeek(?int $userId, ?\DateTimeImmutable $monday = null): array
    {
        if ($userId === null) {
            return [];
        }
        $monday ??= Ui::mondayOf('today');
        $ids = [];
        foreach (MealPlan::forRange($userId, $monday->format('Y-m-d'), $monday->modify('+6 days')->format('Y-m-d')) as $e) {
            $ids[$e->getRecipeId()] = true;
        }
        return $ids;
    }

    /**
     * Nákupný zoznam zo všetkých naplánovaných jedál. Rovnaké suroviny (názov + jednotka) sa sčítajú
     * a množstvá sa prepočítajú podľa počtu porcií v pláne.
     *
     * @param MealPlan[] $entries
     * @return array<string, array{label: string, items: array<int, array{key: string, name: string, amount: string, unit: string}>}>
     */
    public static function shoppingList(array $entries): array
    {
        $sums = [];
        foreach ($entries as $entry) {
            $recipe = $entry->getRecipe();
            if ($recipe === null) {
                continue;
            }
            $factor = $entry->getServings() / $recipe->getServings();
            foreach ($recipe->getIngredients() as $ingredient) {
                if ($ingredient->getAmount() === null) {
                    continue; // „podľa chuti“ – soľ, korenie…
                }
                $unit = self::UNIT_ALIASES[$ingredient->getUnit()] ?? $ingredient->getUnit();
                $name = mb_strtolower(trim($ingredient->getName()));
                $key = $name . '|' . $unit;
                if (!isset($sums[$key])) {
                    $sums[$key] = [
                        'name' => Ui::ucfirst($name),
                        'unit' => $unit,
                        'amount' => 0.0,
                        'category' => array_key_exists($ingredient->getShopCategory(), Ui::SHOP_CATEGORIES)
                            ? $ingredient->getShopCategory() : 'ine',
                    ];
                }
                $sums[$key]['amount'] += $ingredient->getAmount() * $factor;
            }
        }

        $groups = [];
        foreach (Ui::SHOP_CATEGORIES as $code => $label) {
            $groups[$code] = ['label' => $label, 'items' => []];
        }
        foreach ($sums as $key => $item) {
            $groups[$item['category']]['items'][] = [
                'key' => $key,
                'name' => $item['name'],
                'amount' => self::roundAmount($item['amount'], $item['unit']),
                'unit' => self::unitLabel($item['amount'], $item['unit']),
            ];
        }
        foreach ($groups as $code => &$group) {
            usort($group['items'], fn($a, $b) => strcoll($a['name'], $b['name']));
        }
        unset($group);
        return array_filter($groups, fn($g) => $g['items'] !== []);
    }

    /**
     * Skloňovanie počítateľných jednotiek: 1 strúčik, 2 strúčiky, 5 strúčikov.
     */
    private static function unitLabel(float $amount, string $unit): string
    {
        $forms = ['strúčiky' => ['strúčik', 'strúčiky', 'strúčikov'], 'plátky' => ['plátok', 'plátky', 'plátkov']];
        return isset($forms[$unit]) ? Ui::plural(round($amount * 4) / 4, ...$forms[$unit]) : $unit;
    }

    private static function roundAmount(float $amount, string $unit): string
    {
        if (in_array($unit, ['g', 'ml'], true) && $amount >= 20) {
            return Ui::number(round($amount / 5) * 5);
        }
        return Ui::number(round($amount * 4) / 4); // štvrtiny: 0,25 / 0,5 / 0,75
    }

    /**
     * Tip do banera „Jeden nákup, viac dobrých jedál“ – surovina použitá vo viacerých receptoch.
     *
     * @param MealPlan[] $entries
     */
    public static function sharedIngredientTip(array $entries): ?string
    {
        $usage = [];
        foreach ($entries as $entry) {
            $recipe = $entry->getRecipe();
            if ($recipe === null) {
                continue;
            }
            foreach ($recipe->getIngredients() as $ingredient) {
                $name = mb_strtolower($ingredient->getName());
                if ($ingredient->getAmount() === null || in_array($name, self::STAPLES, true)) {
                    continue;
                }
                $usage[$name][$recipe->getId()] = $recipe->getTitle();
            }
        }
        uasort($usage, fn($a, $b) => count($b) <=> count($a));
        foreach ($usage as $name => $recipes) {
            if (count($recipes) >= 2) {
                $titles = array_map(fn($t) => '„' . $t . '“', array_slice(array_values($recipes), 0, 2));
                return Ui::ucfirst($name) . ' využiješ v receptoch ' . implode(' aj ', $titles)
                    . '. Menej zvyškov v chladničke, viac času pre teba.';
            }
        }
        return null;
    }

    /**
     * Budúce (a dnešné) položky plánu pre konkrétny recept – pre baner na detaile receptu.
     *
     * @return MealPlan[]
     */
    public static function upcomingForRecipe(int $userId, int $recipeId): array
    {
        return MealPlan::getAll(
            '`user_id` = ? AND `recipe_id` = ? AND `plan_date` >= ?',
            [$userId, $recipeId, Ui::mondayOf('today')->format('Y-m-d')],
            '`plan_date` ASC',
            14
        );
    }

    /**
     * Text pod banerom „Tento recept už máš v pláne“, napr. „Utorok a štvrtok · obed · 2 porcie na každý deň“.
     *
     * @param MealPlan[] $entries
     */
    public static function describeEntries(array $entries): string
    {
        $days = [];
        $meals = [];
        $servings = [];
        foreach ($entries as $i => $entry) {
            $date = new \DateTimeImmutable($entry->getPlanDate());
            $day = Ui::dayName($date);
            if ($date >= Ui::mondayOf('today')->modify('+7 days')) {
                $day .= ' ' . $date->format('j. n.');
            }
            $days[] = $i === 0 ? $day : mb_strtolower($day);
            $meals[$entry->getMealType()] = mb_strtolower(Ui::MEAL_LABELS[$entry->getMealType()] ?? '');
            $servings[$entry->getServings()] = true;
        }
        $parts = [Ui::joinWords(array_unique($days)), implode(' / ', $meals)];
        if (count($servings) === 1) {
            $n = array_key_first($servings);
            $parts[] = Ui::servings($n) . (count($entries) > 1 ? ' na každý deň' : '');
        }
        return implode(' · ', $parts);
    }
}
