<?php
/**
 * Obrazovka „Moje recepty“ – recepty zverejnené prihláseným používateľom (s úpravou a mazaním).
 *
 * @var \App\Services\RecipeQuery $query
 * @var \App\Models\Recipe[] $recipes
 * @var int $total
 * @var \Framework\Support\LinkGenerator $link
 * @var \Framework\Auth\AppUser $user
 */

use App\Helpers\Ui;

Ui::setTitle('Moje recepty');
$route = 'recipe.mine';
$listTitle = 'Recepty, ktoré si zverejnil(a)';
$searchPlaceholder = 'Hľadaj medzi svojimi receptami...';
$emptyTitle = 'Zatiaľ si nezverejnil(a) žiadny recept';
$emptyText = 'Podeľ sa o recept, ktorý doma varíš najradšej. Zvládneš to za pár minút.';
$emptyAction = '<a href="' . $link->url('recipe.create') . '" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Pridať prvý recept</a>';
$ownerActions = true;
?>
<div class="page-container">
    <div class="page-head">
        <div>
            <h1>Moje recepty</h1>
            <p>Tu spravuješ recepty, ktoré si zverejnil(a). Môžeš ich kedykoľvek upraviť alebo zmazať.</p>
        </div>
    </div>

    <?php require __DIR__ . '/../Partials/recipe-listing.php'; ?>
</div>
