<?php
/**
 * Vyhľadávanie + bočné filtre + mriežka kariet + stránkovanie.
 * Zdieľané obrazovkami Recepty, Obľúbené a Moje recepty.
 *
 * @var \App\Services\RecipeQuery $query
 * @var \App\Models\Recipe[] $recipes
 * @var int $total
 * @var int $pageCount
 * @var array<string, int> $categoryCounts
 * @var array<int, true> $favoriteIds
 * @var array<int, true> $plannedIds
 * @var \Framework\Support\LinkGenerator $link
 * @var \Framework\Auth\AppUser $user
 * @var string $route          napr. 'recipe.index'
 * @var string $listTitle
 * @var string $searchPlaceholder
 * @var string $emptyTitle
 * @var string $emptyText
 * @var bool $ownerActions
 */

use App\Helpers\Ui;
use App\Services\RecipeQuery;

[$controllerName, $actionName] = explode('.', $route);
$returnUrl = $link->url($route, $query->params());
$ownerActions = $ownerActions ?? false;
?>
<div class="listing">
<form method="get" action="" id="recipe-filters" data-autosubmit>
    <input type="hidden" name="c" value="<?= e($controllerName) ?>">
    <?php if ($actionName !== 'index'): ?>
        <input type="hidden" name="a" value="<?= e($actionName) ?>">
    <?php endif; ?>
    <?php if ($query->category !== ''): ?>
        <input type="hidden" name="category" value="<?= e($query->category) ?>">
    <?php endif; ?>

    <div class="search-bar">
        <label class="search-field">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Hľadať</span>
            <input type="search" name="q" value="<?= e($query->q) ?>" placeholder="<?= e($searchPlaceholder) ?>"
                   autocomplete="off">
        </label>
        <button type="submit" class="btn btn-brand">Vyhľadať recept</button>
        <a href="<?= $link->url('recipe.create') ?>" class="btn btn-light-outline"><i class="bi bi-plus-lg"></i> Pridať recept</a>
    </div>
</form>

    <div class="listing-layout">
        <aside class="filters" aria-label="Filtre">
            <div class="filters-head">
                <h2>Filtre</h2>
                <a href="<?= $link->url($route) ?>" class="link-brand small">Vymazať</a>
            </div>

            <h3 class="filters-label">Kategórie</h3>
            <ul class="category-list">
                <?php
                $categories = ['' => 'Všetky recepty'] + Ui::CATEGORY_LABELS;
                foreach ($categories as $value => $label):
                    $isActive = $query->category === $value;
                    $url = $link->url($route, $query->params(['category' => $value ?: null, 'page' => null]));
                    ?>
                    <li>
                        <a href="<?= $url ?>" class="<?= $isActive ? 'active' : '' ?>">
                            <span><?= e($label) ?></span>
                            <span class="count"><?= $categoryCounts[$value] ?? 0 ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3 class="filters-label">Stravovanie</h3>
            <div class="check-list">
                <?php foreach (RecipeQuery::DIETS as $key => [$label]): ?>
                    <label class="check">
                        <input type="checkbox" form="recipe-filters" name="diet[]" value="<?= $key ?>" <?= in_array($key, $query->diets, true) ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <h3 class="filters-label">Čas prípravy</h3>
            <select name="time" form="recipe-filters" class="form-select form-select-soft" aria-label="Čas prípravy">
                <?php foreach (RecipeQuery::TIMES as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $query->time === (string)$value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" form="recipe-filters" class="btn btn-brand btn-sm mt-2 w-100">Použiť filtre</button></noscript>

            <div class="tip-card">
                <i class="bi bi-flower2" aria-hidden="true"></i>
                <strong>Var múdro, neplytvaj.</strong>
                <p>Vyhľadaj suroviny, ktoré už máš doma – napríklad „špenát“ alebo „cícer“.</p>
            </div>
        </aside>

        <section class="listing-results" aria-labelledby="listing-title">
            <div class="results-head">
                <h2 id="listing-title"><?= e($listTitle) ?>
                    <span class="muted-count"><?= $total ?> <?= Ui::plural($total, 'recept', 'recepty', 'receptov') ?></span>
                </h2>
                <label class="sort-select">
                    <span>Zoradiť:</span>
                    <select name="sort" form="recipe-filters">
                        <?php foreach (RecipeQuery::SORTS as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $query->sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <?php if ($recipes === []): ?>
                <div class="empty-state">
                    <i class="bi bi-basket" aria-hidden="true"></i>
                    <h3><?= e($query->hasActiveFilters() ? 'Nič sme nenašli' : $emptyTitle) ?></h3>
                    <p><?= e($query->hasActiveFilters() ? 'Skús zmeniť filtre alebo hľadaný výraz.' : $emptyText) ?></p>
                    <?php if ($query->hasActiveFilters()): ?>
                        <a href="<?= $link->url($route) ?>" class="btn btn-light-outline">Zrušiť filtre</a>
                    <?php elseif (!empty($emptyAction)): ?>
                        <?= $emptyAction ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <?php require __DIR__ . '/recipe-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($pageCount > 1): ?>
                    <nav class="pager" aria-label="Stránkovanie">
                        <?php
                        $current = $query->page;
                        $pages = array_unique(array_filter(
                            [1, $current - 1, $current, $current + 1, $pageCount, $current <= 2 ? 3 : null],
                            fn($p) => $p !== null && $p >= 1 && $p <= $pageCount
                        ));
                        sort($pages);
                        $prev = 0;
                        ?>
                        <?php if ($current > 1): ?>
                            <a href="<?= $link->url($route, $query->params(['page' => $current - 1 > 1 ? $current - 1 : null])) ?>"
                               class="pager-arrow" aria-label="Predchádzajúca strana"><i class="bi bi-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php foreach ($pages as $p): ?>
                            <?php if ($p - $prev > 1): ?><span class="pager-dots">…</span><?php endif; ?>
                            <a href="<?= $link->url($route, $query->params(['page' => $p > 1 ? $p : null])) ?>"
                               class="<?= $p === $current ? 'active' : '' ?>" <?= $p === $current ? 'aria-current="page"' : '' ?>><?= $p ?></a>
                            <?php $prev = $p; ?>
                        <?php endforeach; ?>
                        <?php if ($current < $pageCount): ?>
                            <a href="<?= $link->url($route, $query->params(['page' => $current + 1])) ?>"
                               class="pager-arrow" aria-label="Ďalšia strana"><i class="bi bi-chevron-right"></i></a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>
