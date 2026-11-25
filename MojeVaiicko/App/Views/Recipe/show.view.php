<?php
/**
 * Detail receptu.
 *
 * @var \App\Models\Recipe $recipe
 * @var bool $isFavorite
 * @var bool $isOwner
 * @var \App\Models\MealPlan[] $planned
 * @var int $planServings
 * @var \Framework\Support\LinkGenerator $link
 * @var \Framework\Auth\AppUser $user
 */

use App\Helpers\Ui;
use App\Services\PlanService;

Ui::setTitle($recipe->getTitle());
$author = $recipe->getAuthor();
$rating = $recipe->getRatingStats();
$ingredients = $recipe->getIngredients();
$steps = $recipe->getSteps();
$selfUrl = $link->url('recipe.show', ['id' => $recipe->getId()]);
$categoryUrl = $link->url('recipe.index', ['category' => $recipe->getCategory()]);
$defaultMeal = match ($recipe->getCategory()) {
    'Raňajky' => 'ranajky',
    'Večera' => 'vecera',
    default => 'obed',
};
$isTeam = $author !== null && $author->getUsername() === 'smartrecipe';
?>
<div class="page-container detail">
    <div class="detail-topbar">
        <nav class="crumbs" aria-label="Omrvinková navigácia">
            <a href="<?= $link->url('recipe.index') ?>" class="crumbs-back"><i class="bi bi-arrow-left"></i> Späť na recepty</a>
            <span>/</span>
            <a href="<?= $categoryUrl ?>"><?= e($recipe->getCategory()) ?></a>
            <span>/</span>
            <span class="crumbs-current"><?= e($recipe->getTitle()) ?></span>
        </nav>
        <div class="detail-tools">
            <?php if ($isOwner): ?>
                <a href="<?= $link->url('recipe.edit', ['id' => $recipe->getId()]) ?>" class="icon-btn" title="Upraviť recept"
                   aria-label="Upraviť recept"><i class="bi bi-pencil"></i></a>
            <?php endif; ?>
            <button type="button" class="icon-btn" data-print title="Vytlačiť recept" aria-label="Vytlačiť recept"><i class="bi bi-printer"></i></button>
            <button type="button" class="icon-btn" data-share="<?= e($recipe->getTitle()) ?>" title="Zdieľať recept"
                    aria-label="Zdieľať recept"><i class="bi bi-share"></i></button>
        </div>
    </div>

    <section class="detail-hero">
        <div class="detail-media">
            <?= Ui::recipeImage($recipe, $link) ?>
            <span class="media-chip"><i class="bi bi-flower1"></i> Jednoducho. Čerstvo. Doma.</span>
        </div>

        <div class="detail-info">
            <div class="tag-row">
                <span class="tag tag-green"><?= e($recipe->getCategory()) ?></span>
                <?php foreach ($recipe->getDietTags() as $tag): ?>
                    <span class="tag tag-orange"><?= e($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <h1><?= e($recipe->getTitle()) ?></h1>
            <p class="detail-lead"><?= e($recipe->getDescription() ?: $recipe->getSummary()) ?></p>

            <ul class="detail-facts">
                <li><i class="bi bi-clock"></i><span><strong><?= $recipe->getTotalTime() ?> min</strong><small>Celkový čas</small></span></li>
                <li><i class="bi bi-fire"></i><span><strong><?= e($recipe->getDifficulty()) ?></strong><small>Náročnosť</small></span></li>
                <li><i class="bi bi-people"></i><span><strong><?= Ui::servings($recipe->getServings()) ?></strong><small>Množstvo</small></span></li>
            </ul>

            <div class="detail-actions">
                <?php if ($user->isLoggedIn()): ?>
                    <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addToPlan">
                        <i class="bi bi-calendar-plus"></i> Pridať do plánu
                    </button>
                    <form method="post" action="<?= $link->url('favorite.toggle') ?>" data-favorite-form data-favorite-labels="Uložiť recept|Uložené">
                        <input type="hidden" name="recipe_id" value="<?= $recipe->getId() ?>">
                        <input type="hidden" name="return" value="<?= e($selfUrl) ?>">
                        <button type="submit" class="btn btn-light-outline save-btn <?= $isFavorite ? 'is-active' : '' ?>"
                                aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>">
                            <i class="bi <?= $isFavorite ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            <span data-label><?= $isFavorite ? 'Uložené' : 'Uložiť recept' ?></span>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= $link->url('auth.login') ?>" class="btn btn-brand"><i class="bi bi-calendar-plus"></i> Pridať do plánu</a>
                    <a href="<?= $link->url('auth.login') ?>" class="btn btn-light-outline"><i class="bi bi-heart"></i> Uložiť recept</a>
                <?php endif; ?>
            </div>

            <p class="detail-byline">
                <?= $isTeam ? 'Od tímu SmartRecipe' : 'Od ' . e($author?->getName() ?? 'neznámeho autora') ?>
                <span class="dot">·</span>
                <a href="<?= $link->url('review.index', ['recipe_id' => $recipe->getId()]) ?>" class="rating-link">
                    <i class="bi bi-star-fill"></i> <?= Ui::rating($rating['avg']) ?>
                    <span>(<?= $rating['count'] ?> <?= Ui::plural($rating['count'], 'hodnotenie', 'hodnotenia', 'hodnotení') ?>)</span>
                </a>
            </p>
        </div>
    </section>

    <?php if ($planned !== []): ?>
        <section class="plan-banner">
            <span class="plan-banner-icon"><i class="bi bi-calendar-check"></i></span>
            <div>
                <strong>Tento recept už máš v pláne</strong>
                <span><?= e(PlanService::describeEntries($planned)) ?></span>
            </div>
            <a href="<?= $link->url('plan.index', ['week' => $planned[0]->getPlanDate(), 'highlight' => $recipe->getId()]) ?>"
               class="btn btn-white"><i class="bi bi-arrow-right"></i> Zobraziť môj plán</a>
        </section>
    <?php endif; ?>

    <div class="detail-body">
        <aside class="detail-side">
            <section class="card-box ingredients" data-ingredients data-base-servings="<?= $recipe->getServings() ?>">
                <div class="card-box-head">
                    <h2>Ingrediencie</h2>
                    <div class="stepper" aria-label="Počet porcií">
                        <button type="button" data-step="-1" aria-label="Menej porcií"><i class="bi bi-dash"></i></button>
                        <span data-servings-label><?= Ui::servings($recipe->getServings()) ?></span>
                        <button type="button" data-step="1" aria-label="Viac porcií"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
                <ul class="ingredient-list">
                    <?php foreach ($ingredients as $ingredient): ?>
                        <li>
                            <label>
                                <input type="checkbox">
                                <span class="ing-amount" <?= $ingredient->getAmount() !== null
                                    ? 'data-amount="' . $ingredient->getAmount() . '" data-unit="' . e($ingredient->getUnit()) . '"' : '' ?>>
                                    <?= $ingredient->getAmount() !== null
                                        ? Ui::number($ingredient->getAmount()) . ' ' . e($ingredient->getUnit())
                                        : e($ingredient->getUnit() ?: 'podľa chuti') ?>
                                </span>
                                <span class="ing-name"><?= e($ingredient->getName()) ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="card-note"><i class="bi bi-basket"></i> Pridaním do plánu sa suroviny automaticky objavia v nákupnom zozname.</p>
            </section>

            <section class="card-box nutrition">
                <div class="card-box-head">
                    <h2>Nutričné hodnoty</h2>
                    <small>na 1 porciu</small>
                </div>
                <p class="kcal"><strong><?= (int)$recipe->getCalories() ?></strong> <span>kcal</span></p>
                <dl class="macros">
                    <div><dt><?= $recipe->getProtein() ?> g</dt><dd>Bielkoviny</dd></div>
                    <div><dt><?= $recipe->getCarbs() ?> g</dt><dd>Sacharidy</dd></div>
                    <div><dt><?= $recipe->getFat() ?> g</dt><dd>Tuky</dd></div>
                </dl>
                <p class="card-note">Hodnoty sú orientačné a závisia od použitých surovín.</p>
            </section>
        </aside>

        <section class="detail-steps">
            <div class="steps-head">
                <h2>Poďme variť</h2>
                <span><i class="bi bi-clock"></i> Príprava <?= $recipe->getPrepTime() ?> min<?= $recipe->getCookTime() > 0 ? ' · Varenie ' . $recipe->getCookTime() . ' min' : '' ?></span>
            </div>
            <ol class="step-list">
                <?php foreach ($steps as $i => $step): ?>
                    <li>
                        <span class="step-num"><?= $i + 1 ?></span>
                        <div>
                            <h3><?= e($step->getTitle()) ?></h3>
                            <p><?= nl2br(e($step->getDescription())) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>

            <?php if ($recipe->getTip()): ?>
                <aside class="tip-box">
                    <i class="bi bi-lightbulb"></i>
                    <div>
                        <strong>Malý tip proti plytvaniu</strong>
                        <p><?= e($recipe->getTip()) ?></p>
                    </div>
                </aside>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php if ($user->isLoggedIn()): ?>
    <div class="modal fade" id="addToPlan" tabindex="-1" aria-labelledby="addToPlanTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="<?= $link->url('plan.add') ?>">
                <input type="hidden" name="recipe_id" value="<?= $recipe->getId() ?>">
                <input type="hidden" name="return" value="<?= e($selfUrl) ?>">
                <div class="modal-header">
                    <h2 class="modal-title" id="addToPlanTitle">Pridať do plánu</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavrieť"></button>
                </div>
                <div class="modal-body">
                    <p class="modal-recipe"><?= Ui::recipeImage($recipe, $link, 'thumb') ?> <strong><?= e($recipe->getTitle()) ?></strong></p>

                    <label class="form-label" for="planDate">Deň</label>
                    <select class="form-select" id="planDate" name="date">
                        <?php $day = new DateTimeImmutable('today'); ?>
                        <?php for ($i = 0; $i < 14; $i++, $day = $day->modify('+1 day')): ?>
                            <option value="<?= $day->format('Y-m-d') ?>">
                                <?= $i === 0 ? 'Dnes' : ($i === 1 ? 'Zajtra' : Ui::dayName($day)) ?>, <?= $day->format('j. n.') ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <span class="form-label mt-3 d-block">Jedlo</span>
                    <div class="segmented">
                        <?php foreach (Ui::MEAL_LABELS as $value => $label): ?>
                            <label>
                                <input type="radio" name="meal_type" value="<?= $value ?>" <?= $value === $defaultMeal ? 'checked' : '' ?>>
                                <span><i class="bi <?= Ui::MEAL_ICONS[$value] ?>"></i> <?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <label class="form-label mt-3" for="planServings">Počet porcií</label>
                    <input type="number" class="form-control" id="planServings" name="servings" min="1" max="20"
                           value="<?= $planServings ?>" data-servings-input>
                    <p class="form-text">Ak už máš na daný deň a jedlo niečo naplánované, nahradí sa týmto receptom.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-outline" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="submit" class="btn btn-brand"><i class="bi bi-calendar-plus"></i> Pridať do plánu</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
