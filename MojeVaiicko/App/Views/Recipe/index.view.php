<?php
/** @var \App\Models\Recipe[] $recipes */
/** @var \Framework\Support\LinkGenerator $link */
?>

<main class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Zoznam receptov</h2>
        <a href="<?= $link->url('recipe.create') ?>" class="btn btn-success">+ Pridať nový recept</a>
    </div>

    <div class="recipes-grid">
        <?php foreach ($recipes as $recipe): ?>
            <article class="recipe-card">
                <div class="card-content">
                    <span class="recipe-type"><?= e($recipe->getCategory()) ?></span>
                    <h3 class="recipe-title"><?= e($recipe->getTitle()) ?></h3>
                    <div class="recipe-details">
                        <span>⏱ <?= e($recipe->getPrepTime()) ?> min</span>
                        <span>🔥 <?= e($recipe->getCalories()) ?> kcal</span>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <a href="<?= $link->url('recipe.edit', ['id' => $recipe->getId()]) ?>" class="btn btn-sm btn-primary">Upraviť</a>
                        <a href="<?= $link->url('recipe.delete', ['id' => $recipe->getId()]) ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Naozaj chcete vymazať tento recept?')">Vymazať</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>