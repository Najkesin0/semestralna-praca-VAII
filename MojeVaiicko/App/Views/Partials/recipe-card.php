<?php
/**
 * Karta receptu (Recepty, Obľúbené, Moje recepty).
 *
 * @var \App\Models\Recipe $recipe
 * @var \Framework\Support\LinkGenerator $link
 * @var \Framework\Auth\AppUser $user
 * @var array<int, true> $favoriteIds
 * @var array<int, true> $plannedIds
 * @var string $returnUrl
 * @var bool $ownerActions zobraziť tlačidlá Upraviť / Zmazať
 */

use App\Helpers\Ui;

$isFavorite = isset($favoriteIds[$recipe->getId()]);
$isPlanned = isset($plannedIds[$recipe->getId()]);
$detailUrl = $link->url('recipe.show', ['id' => $recipe->getId()]);
$dietTags = $recipe->getDietTags();
?>
<article class="recipe-card" data-recipe-card="<?= $recipe->getId() ?>">
    <div class="recipe-card-media">
        <?= Ui::recipeImage($recipe, $link) ?>
        <?php if ($isPlanned): ?>
            <span class="badge-plan"><i class="bi bi-calendar-check"></i> V tvojom pláne</span>
        <?php endif; ?>

        <?php if ($user->isLoggedIn()): ?>
            <form method="post" action="<?= $link->url('favorite.toggle') ?>" class="fav-form" data-favorite-form>
                <input type="hidden" name="recipe_id" value="<?= $recipe->getId() ?>">
                <input type="hidden" name="return" value="<?= e($returnUrl) ?>">
                <button type="submit" class="fav-btn <?= $isFavorite ? 'is-active' : '' ?>"
                        aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>"
                        aria-label="<?= $isFavorite ? 'Odobrať z obľúbených' : 'Pridať do obľúbených' ?>">
                    <i class="bi <?= $isFavorite ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
            </form>
        <?php else: ?>
            <a class="fav-btn" href="<?= $link->url('auth.login') ?>" aria-label="Prihlás sa a ulož si recept">
                <i class="bi bi-heart"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="recipe-card-body">
        <div class="tag-row">
            <span class="tag tag-green"><?= e($recipe->getCategory()) ?></span>
            <?php if ($dietTags): ?>
                <span class="tag tag-orange"><?= e($dietTags[0]) ?></span>
            <?php endif; ?>
        </div>
        <h3 class="recipe-card-title">
            <a href="<?= $detailUrl ?>" class="stretched-link"><?= e($recipe->getTitle()) ?></a>
        </h3>
        <p class="recipe-card-summary"><?= e($recipe->getSummary()) ?></p>

        <div class="recipe-card-meta">
            <span><i class="bi bi-clock"></i> <?= $recipe->getTotalTime() ?> min</span>
            <span><?= (int)$recipe->getCalories() ?> kcal / porcia</span>
            <i class="bi bi-arrow-up-right recipe-card-arrow" aria-hidden="true"></i>
        </div>

        <?php if (!empty($ownerActions)): ?>
            <div class="recipe-card-owner">
                <a href="<?= $link->url('recipe.edit', ['id' => $recipe->getId()]) ?>" class="btn btn-outline-brand btn-sm">
                    <i class="bi bi-pencil"></i> Upraviť
                </a>
                <form method="post" action="<?= $link->url('recipe.delete') ?>"
                      data-confirm="Naozaj chceš zmazať recept „<?= e($recipe->getTitle()) ?>“? Odstráni sa aj z plánov, obľúbených a zmažú sa jeho hodnotenia.">
                    <input type="hidden" name="id" value="<?= $recipe->getId() ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i> Zmazať</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</article>
