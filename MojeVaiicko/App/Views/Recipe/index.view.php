<?php
/**
 * Obrazovka „Recepty“.
 *
 * @var \App\Services\RecipeQuery $query
 * @var \App\Models\Recipe[] $recipes
 * @var \App\Models\Recipe|null $heroRecipe
 * @var \Framework\Support\LinkGenerator $link
 * @var \Framework\Auth\AppUser $user
 */

use App\Helpers\Ui;

Ui::setTitle('Recepty');
$route = 'recipe.index';
$listTitle = 'Inšpirácia na každý deň';
$searchPlaceholder = 'Hľadaj podľa názvu receptu alebo ingrediencie...';
$emptyTitle = 'Zatiaľ tu nie sú žiadne recepty';
$emptyText = 'Buď prvý a zverejni svoj obľúbený recept.';
$emptyAction = '<a href="' . $link->url('recipe.create') . '" class="btn btn-brand">Pridať recept</a>';
$heroImage = $heroRecipe !== null ? Ui::imageUrl($heroRecipe, $link) : null;
?>
<div class="page-container">
    <section class="hero">
        <div class="hero-text">
            <p class="eyebrow">Dobré jedlo začína dobrým nápadom</p>
            <h1>Čo dobré si dnes uvaríš?</h1>
            <p class="hero-lead">Objav jednoduché recepty, naplánuj si týždeň a nakupuj bez zbytočností.</p>
            <p class="hero-season"><i class="bi bi-flower1"></i> <?= e(Ui::seasonText()) ?></p>
        </div>
        <div class="hero-media">
            <?php if ($heroImage !== null): ?>
                <a href="<?= $link->url('recipe.show', ['id' => $heroRecipe->getId()]) ?>">
                    <img src="<?= e($heroImage) ?>" alt="<?= e($heroRecipe->getTitle()) ?>">
                </a>
            <?php else: ?>
                <svg viewBox="0 0 420 240" role="img" aria-label="Miska plná zeleniny" preserveAspectRatio="xMidYMid slice">
                    <rect width="420" height="240" fill="#efe9dc"/>
                    <path d="M0 170 Q120 140 230 175 T420 160 V240 H0Z" fill="#e6dfcf"/>
                    <circle cx="210" cy="122" r="98" fill="#d9d2c2" opacity=".55"/>
                    <circle cx="206" cy="116" r="96" fill="#fbfaf6"/>
                    <circle cx="206" cy="116" r="74" fill="#f1ede3"/>
                    <circle cx="206" cy="116" r="64" fill="#6f9b5a"/>
                    <g fill="#8fb86f"><ellipse cx="180" cy="92" rx="20" ry="9" transform="rotate(-30 180 92)"/><ellipse cx="236" cy="140" rx="22" ry="9" transform="rotate(25 236 140)"/><ellipse cx="226" cy="84" rx="16" ry="7" transform="rotate(40 226 84)"/><ellipse cx="178" cy="142" rx="18" ry="8" transform="rotate(-50 178 142)"/></g>
                    <g fill="#d9573b"><circle cx="196" cy="104" r="9"/><circle cx="232" cy="112" r="8"/><circle cx="210" cy="140" r="9"/><circle cx="170" cy="118" r="7"/><circle cx="244" cy="90" r="6"/></g>
                    <g fill="#e9c46a"><circle cx="214" cy="96" r="5"/><circle cx="222" cy="128" r="5"/><circle cx="188" cy="128" r="5"/><circle cx="200" cy="80" r="4"/><circle cx="252" cy="126" r="4"/></g>
                    <g fill="#fff8ec"><circle cx="206" cy="118" r="7"/><circle cx="240" cy="100" r="5"/><circle cx="182" cy="104" r="5"/></g>
                    <rect x="330" y="52" width="10" height="150" rx="5" fill="#c8c1b0"/>
                    <rect x="352" y="52" width="12" height="150" rx="6" fill="#c8c1b0"/>
                    <circle cx="70" cy="60" r="28" fill="#6f9b5a" opacity=".25"/>
                    <circle cx="58" cy="196" r="20" fill="#d9573b" opacity=".18"/>
                </svg>
            <?php endif; ?>
        </div>
    </section>

    <?php require __DIR__ . '/../Partials/recipe-listing.php'; ?>
</div>
