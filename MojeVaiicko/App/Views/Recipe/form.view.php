<?php
/** @var \App\Models\Recipe $recipe */
/** @var \Framework\Support\LinkGenerator $link */
?>

<main class="main-container">
    <div class="container mt-4">
        <h2><?= $recipe->getId() ? 'Upraviť recept' : 'Pridať nový recept' ?></h2>

        <form action="<?= $link->url('recipe.save') ?>" method="post" class="mt-3">
            <?php if ($recipe->getId()): ?>
                <input type="hidden" name="id" value="<?= $recipe->getId() ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="title" class="form-label">Názov receptu:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($recipe->getTitle()) ?>" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Kategória:</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="Raňajky" <?= $recipe->getCategory() === 'Raňajky' ? 'selected' : '' ?>>Raňajky</option>
                    <option value="Obed" <?= $recipe->getCategory() === 'Obed' ? 'selected' : '' ?>>Obed</option>
                    <option value="Večera" <?= $recipe->getCategory() === 'Večera' ? 'selected' : '' ?>>Večera</option>
                    <option value="Dezert" <?= $recipe->getCategory() === 'Dezert' ? 'selected' : '' ?>>Dezert</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="prep_time" class="form-label">Čas prípravy (v minútach):</label>
                <input type="number" id="prep_time" name="prep_time" class="form-control" value="<?= e($recipe->getPrepTime()) ?>" required>
            </div>

            <div class="mb-3">
                <label for="calories" class="form-label">Kalórie (kcal):</label>
                <input type="number" id="calories" name="calories" class="form-control" value="<?= e($recipe->getCalories()) ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Uložiť</button>
            <a href="<?= $link->url('recipe.index') ?>" class="btn btn-secondary">Zrušiť</a>
        </form>
    </div>
</main>