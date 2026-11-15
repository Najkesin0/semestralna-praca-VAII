<?php

/** @var \App\Models\Review $review */
/** @var \App\Models\Recipe[] $recipes */
/** @var \Framework\Support\LinkGenerator $link */
?>

<main class="main-container">
    <div class="container mt-4">
        <h2><?= $review->getId() ? 'Upraviť hodnotenie' : 'Pridať nové hodnotenie' ?></h2>

        <?php if (empty($recipes)): ?>
            <p>Najprv je potrebné <a href="<?= $link->url('recipe.create') ?>">pridať recept</a>.</p>
        <?php else: ?>
        <form action="<?= $link->url('review.save') ?>" method="post" class="mt-3">
            <?php if ($review->getId()): ?>
                <input type="hidden" name="id" value="<?= $review->getId() ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="recipe_id" class="form-label">Recept:</label>
                <select id="recipe_id" name="recipe_id" class="form-select" required>
                    <option value="" disabled <?= $review->getRecipeId() ? '' : 'selected' ?>>-- vyberte recept --</option>
                    <?php foreach ($recipes as $recipe): ?>
                        <option value="<?= $recipe->getId() ?>" <?= $review->getRecipeId() === $recipe->getId() ? 'selected' : '' ?>>
                            <?= e($recipe->getTitle()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="author" class="form-label">Autor:</label>
                <input type="text" id="author" name="author" class="form-control" maxlength="100"
                       value="<?= e($review->getAuthor()) ?>" required>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Hodnotenie (1–5):</label>
                <select id="rating" name="rating" class="form-select" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= ($review->getRating() ?? 5) === $i ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) ?> (<?= $i ?>)
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="comment" class="form-label">Komentár:</label>
                <textarea id="comment" name="comment" class="form-control" rows="4"><?= e($review->getComment() ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Uložiť</button>
            <a href="<?= $link->url('review.index', $review->getRecipeId() ? ['recipe_id' => $review->getRecipeId()] : []) ?>"
               class="btn btn-secondary">Zrušiť</a>
        </form>
        <?php endif; ?>
    </div>
</main>
