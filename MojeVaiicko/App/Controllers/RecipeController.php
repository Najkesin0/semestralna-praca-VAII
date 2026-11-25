<?php

namespace App\Controllers;

use App\Core\AppController;
use App\Helpers\Ui;
use App\Models\Favorite;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use App\Services\PlanService;
use App\Services\RecipeQuery;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Recepty: zoznam (Recepty), detail, Moje recepty a vytváranie / úprava / mazanie vlastných receptov.
 */
class RecipeController extends AppController
{
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function authorize(Request $request, string $action): bool
    {
        // Prehliadanie receptov je verejné, ostatné akcie vyžadujú prihlásenie
        return in_array($action, ['index', 'show'], true) || $this->user->isLoggedIn();
    }

    /**
     * Obrazovka „Recepty“ – všetky zverejnené recepty s filtrami.
     */
    public function index(Request $request): Response
    {
        $query = RecipeQuery::fromRequest($request);
        $recipes = $query->results();

        // Do hero sekcie použijeme fotku najnovšieho receptu, ktorý ju má
        $heroRecipes = Recipe::getAll('`image` IS NOT NULL', [], '`created_at` DESC', 1);

        return $this->html([
            'query' => $query,
            'recipes' => $recipes,
            'total' => $query->total(),
            'pageCount' => $query->pageCount(),
            'categoryCounts' => $query->categoryCounts(),
            'favoriteIds' => Favorite::recipeIdsForUser($this->userId()),
            'plannedIds' => PlanService::recipeIdsInWeek($this->userId()),
            'heroRecipe' => $heroRecipes[0] ?? null,
        ]);
    }

    /**
     * Detail receptu.
     */
    public function show(Request $request): Response
    {
        $recipe = Recipe::getOne((int)$request->get('id'));
        if ($recipe === null) {
            throw new HttpException(404, 'Recept sa nenašiel.');
        }

        $userId = $this->userId();
        $planned = $userId !== null ? PlanService::upcomingForRecipe($userId, $recipe->getId()) : [];
        $planServings = $userId !== null ? (User::getOne($userId)?->getPlanServings() ?? 2) : 2;

        return $this->html([
            'recipe' => $recipe,
            'isFavorite' => $userId !== null && Favorite::findFor($userId, $recipe->getId()) !== null,
            'planned' => $planned,
            'planServings' => $planServings,
            'isOwner' => $this->canManage($recipe),
        ]);
    }

    /**
     * Obrazovka „Moje recepty“ – recepty, ktoré zverejnil prihlásený používateľ.
     */
    public function mine(Request $request): Response
    {
        $query = RecipeQuery::fromRequest($request);
        if ($request->get('sort') === null) {
            $query->sort = 'najnovsie';
        }
        $query->scope('`user_id` = ?', [$this->userId()]);
        $recipes = $query->results();

        return $this->html([
            'query' => $query,
            'recipes' => $recipes,
            'total' => $query->total(),
            'pageCount' => $query->pageCount(),
            'categoryCounts' => $query->categoryCounts(),
            'favoriteIds' => Favorite::recipeIdsForUser($this->userId()),
            'plannedIds' => PlanService::recipeIdsInWeek($this->userId()),
        ]);
    }

    public function create(Request $request): Response
    {
        $recipe = new Recipe();
        return $this->html([
            'recipe' => $recipe,
            'ingredients' => [['amount' => '', 'unit' => 'g', 'name' => '', 'shop_category' => 'zelenina']],
            'steps' => [['title' => '', 'description' => '']],
            'errors' => [],
        ], 'form');
    }

    public function edit(Request $request): Response
    {
        $recipe = $this->findOwnRecipe((int)$request->get('id'));
        if ($recipe === null) {
            return $this->redirect($this->url('recipe.mine'));
        }

        return $this->html([
            'recipe' => $recipe,
            'ingredients' => array_map(fn(Ingredient $i) => [
                'amount' => $i->getAmount() === null ? '' : Ui::number($i->getAmount()),
                'unit' => $i->getUnit(),
                'name' => $i->getName(),
                'shop_category' => $i->getShopCategory(),
            ], $recipe->getIngredients()),
            'steps' => array_map(fn(Step $s) => [
                'title' => $s->getTitle(),
                'description' => $s->getDescription(),
            ], $recipe->getSteps()),
            'errors' => [],
        ], 'form');
    }

    /**
     * Uloženie nového alebo upraveného receptu vrátane ingrediencií, krokov a fotky.
     */
    public function save(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('recipe.mine'));
        }

        $id = (int)$request->post('id');
        if ($id > 0) {
            $recipe = $this->findOwnRecipe($id);
            if ($recipe === null) {
                return $this->redirect($this->url('recipe.mine'));
            }
        } else {
            $recipe = new Recipe();
            $recipe->setUserId($this->userId());
            $recipe->setCreatedAt(date('Y-m-d H:i:s'));
        }

        $errors = [];

        // --- základné údaje
        $title = trim((string)$request->post('title'));
        $summary = trim((string)$request->post('summary'));
        $category = (string)$request->post('category');
        $difficulty = (string)$request->post('difficulty');

        $recipe->setTitle($title);
        $recipe->setSummary($summary);
        $recipe->setDescription(trim((string)$request->post('description')) ?: null);
        $recipe->setCategory($category);
        $recipe->setDifficulty($difficulty);
        $recipe->setPrepTime((int)$request->post('prep_time'));
        $recipe->setCookTime((int)$request->post('cook_time'));
        $recipe->setServings((int)$request->post('servings'));
        $recipe->setCalories((int)$request->post('calories'));
        $recipe->setProtein((int)$request->post('protein'));
        $recipe->setCarbs((int)$request->post('carbs'));
        $recipe->setFat((int)$request->post('fat'));
        $recipe->setVegan((bool)$request->post('is_vegan'));
        $recipe->setVegetarian((bool)$request->post('is_vegetarian') || (bool)$request->post('is_vegan'));
        $recipe->setGlutenFree((bool)$request->post('is_gluten_free'));
        $recipe->setLactoseFree((bool)$request->post('is_lactose_free'));
        $recipe->setTip(trim((string)$request->post('tip')) ?: null);

        if ($title === '' || mb_strlen($title) > 255) {
            $errors['title'] = 'Zadaj názov receptu (max. 255 znakov).';
        }
        if ($summary === '' || mb_strlen($summary) > 160) {
            $errors['summary'] = 'Zadaj krátky popis (max. 160 znakov).';
        }
        if (!in_array($category, Recipe::CATEGORIES, true)) {
            $errors['category'] = 'Vyber kategóriu.';
        }
        if (!in_array($difficulty, Recipe::DIFFICULTIES, true)) {
            $errors['difficulty'] = 'Vyber náročnosť.';
        }
        if ($recipe->getPrepTime() < 0 || $recipe->getPrepTime() > 1440
            || $recipe->getCookTime() < 0 || $recipe->getCookTime() > 1440) {
            $errors['time'] = 'Čas prípravy a varenia musí byť medzi 0 a 1440 minútami.';
        } elseif ($recipe->getTotalTime() === 0) {
            $errors['time'] = 'Zadaj aspoň čas prípravy.';
        }
        if ((int)$request->post('servings') < 1 || (int)$request->post('servings') > 20) {
            $errors['servings'] = 'Počet porcií musí byť 1 až 20.';
        }
        foreach (['calories' => 5000, 'protein' => 500, 'carbs' => 500, 'fat' => 500] as $field => $max) {
            $value = (int)$request->post($field);
            if ($value < 0 || $value > $max) {
                $errors['nutrition'] = 'Skontroluj nutričné hodnoty (nesmú byť záporné ani nereálne vysoké).';
            }
        }
        if ((int)$request->post('calories') <= 0) {
            $errors['nutrition'] = 'Zadaj kalórie na porciu.';
        }

        // --- ingrediencie
        $ingredientRows = [];
        foreach ((array)$request->post('ingredients') as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = trim((string)($row['name'] ?? ''));
            $amountRaw = str_replace(',', '.', trim((string)($row['amount'] ?? '')));
            if ($name === '' && $amountRaw === '') {
                continue; // prázdny riadok ignorujeme
            }
            $unit = trim((string)($row['unit'] ?? ''));
            $shop = (string)($row['shop_category'] ?? 'ine');
            $ingredientRows[] = [
                'amount' => trim((string)($row['amount'] ?? '')),
                'unit' => $unit,
                'name' => $name,
                'shop_category' => array_key_exists($shop, Ui::SHOP_CATEGORIES) ? $shop : 'ine',
            ];
            if ($name === '' || mb_strlen($name) > 120) {
                $errors['ingredients'] = 'Každá ingrediencia musí mať názov (max. 120 znakov).';
            }
            if ($amountRaw !== '' && (!is_numeric($amountRaw) || (float)$amountRaw <= 0 || (float)$amountRaw > 100000)) {
                $errors['ingredients'] = 'Množstvo musí byť kladné číslo (napr. 200 alebo 0,5), alebo nechaj prázdne pre „podľa chuti“.';
            }
            if (mb_strlen($unit) > 20) {
                $errors['ingredients'] = 'Jednotka môže mať najviac 20 znakov.';
            }
        }
        if ($ingredientRows === []) {
            $errors['ingredients'] = 'Pridaj aspoň jednu ingredienciu.';
        }

        // --- postup
        $stepRows = [];
        foreach ((array)$request->post('steps') as $row) {
            if (!is_array($row)) {
                continue;
            }
            $stepTitle = trim((string)($row['title'] ?? ''));
            $stepText = trim((string)($row['description'] ?? ''));
            if ($stepTitle === '' && $stepText === '') {
                continue;
            }
            $stepRows[] = ['title' => $stepTitle, 'description' => $stepText];
            if ($stepTitle === '' || $stepText === '' || mb_strlen($stepTitle) > 120) {
                $errors['steps'] = 'Každý krok musí mať nadpis (max. 120 znakov) aj popis.';
            }
        }
        if ($stepRows === []) {
            $errors['steps'] = 'Pridaj aspoň jeden krok postupu.';
        }

        // --- fotka
        $newImage = null;
        $upload = $request->file('image');
        if ($upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE) {
            $type = $upload->isOk() ? (new \finfo(FILEINFO_MIME_TYPE))->file($upload->getFileTempPath()) : null;
            if (!$upload->isOk()) {
                $errors['image'] = 'Fotku sa nepodarilo nahrať. Skús to znova.';
            } elseif ($upload->getSize() > self::MAX_IMAGE_SIZE) {
                $errors['image'] = 'Fotka môže mať najviac 5 MB.';
            } elseif (!isset(self::IMAGE_TYPES[$type])) {
                $errors['image'] = 'Podporované sú len obrázky JPG, PNG a WEBP.';
            } else {
                $newImage = $upload;
            }
        }

        if ($errors !== []) {
            return $this->html([
                'recipe' => $recipe,
                'ingredients' => $ingredientRows ?: [['amount' => '', 'unit' => 'g', 'name' => '', 'shop_category' => 'zelenina']],
                'steps' => $stepRows ?: [['title' => '', 'description' => '']],
                'errors' => $errors,
            ], 'form');
        }

        // --- uloženie fotky
        $oldImage = $recipe->getImage();
        if ($newImage !== null) {
            $fileName = 'recipe_' . bin2hex(random_bytes(8)) . '.' . self::IMAGE_TYPES[$type];
            if ($newImage->store(self::uploadDir() . $fileName)) {
                $recipe->setImage($fileName);
            }
        } elseif ($request->post('remove_image')) {
            $recipe->setImage(null);
        }

        $recipe->save();

        if ($oldImage !== null && $oldImage !== $recipe->getImage()) {
            self::deleteImage($oldImage);
        }

        // --- ingrediencie a kroky: staré zmažeme a uložíme aktuálne v zadanom poradí
        foreach (Ingredient::getAll('`recipe_id` = ?', [$recipe->getId()]) as $old) {
            $old->delete();
        }
        foreach ($ingredientRows as $i => $row) {
            $ingredient = new Ingredient();
            $ingredient->setRecipeId($recipe->getId());
            $ingredient->setPosition($i + 1);
            $amount = str_replace(',', '.', $row['amount']);
            $ingredient->setAmount($amount === '' ? null : (float)$amount);
            $ingredient->setUnit($amount === '' && $row['unit'] === '' ? 'podľa chuti' : $row['unit']);
            $ingredient->setName($row['name']);
            $ingredient->setShopCategory($row['shop_category']);
            $ingredient->save();
        }

        foreach (Step::getAll('`recipe_id` = ?', [$recipe->getId()]) as $old) {
            $old->delete();
        }
        foreach ($stepRows as $i => $row) {
            $step = new Step();
            $step->setRecipeId($recipe->getId());
            $step->setPosition($i + 1);
            $step->setTitle($row['title']);
            $step->setDescription($row['description']);
            $step->save();
        }

        $this->flash($id > 0 ? 'Recept bol upravený.' : 'Recept bol zverejnený. Ďakujeme, že sa delíš!');
        return $this->redirect($this->url('recipe.show', ['id' => $recipe->getId()]));
    }

    /**
     * Zmazanie vlastného receptu (len cez POST).
     */
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('recipe.mine'));
        }

        $recipe = $this->findOwnRecipe((int)$request->post('id'));
        if ($recipe !== null) {
            $image = $recipe->getImage();
            $title = $recipe->getTitle();
            $recipe->delete(); // ingrediencie, kroky, hodnotenia, obľúbené aj plán sa zmažú cez ON DELETE CASCADE
            if ($image !== null) {
                self::deleteImage($image);
            }
            $this->flash('Recept „' . $title . '“ bol zmazaný.');
        }

        return $this->redirect($this->url('recipe.mine'));
    }

    /**
     * Recept, ktorý patrí prihlásenému používateľovi; inak null + chybová správa.
     */
    private function findOwnRecipe(int $id): ?Recipe
    {
        $recipe = Recipe::getOne($id);
        if ($recipe === null || !$this->canManage($recipe)) {
            $this->flash('Tento recept nemôžeš upravovať – patrí inému používateľovi alebo neexistuje.', 'danger');
            return null;
        }
        return $recipe;
    }

    /**
     * Recept môže upravovať / mazať jeho autor alebo správca (používateľ „admin“).
     */
    private function canManage(Recipe $recipe): bool
    {
        $identity = $this->user->getIdentity();
        $isAdmin = $identity !== null && method_exists($identity, 'getUsername') && $identity->getUsername() === 'admin';
        return $isAdmin || $recipe->isOwnedBy($this->userId());
    }

    private static function uploadDir(): string
    {
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function deleteImage(string $fileName): void
    {
        $path = self::uploadDir() . basename($fileName);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
