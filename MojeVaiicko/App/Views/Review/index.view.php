<?php
/** @var \App\Models\Review[] $reviews */
/** @var \App\Models\Recipe|null $recipe */
/** @var \Framework\Support\LinkGenerator $link */
?>

<main class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <?php if ($recipe): ?>
                Hodnotenia receptu „<?= e($recipe->getTitle()) ?>“
            <?php else: ?>
                Všetky hodnotenia
            <?php endif; ?>
        </h2>
        <div class="d-flex gap-2">
            <?php if ($recipe): ?>
                <a href="<?= $link->url('review.index') ?>" class="btn btn-outline-secondary">Zobraziť všetky</a>
                <a href="<?= $link->url('review.create', ['recipe_id' => $recipe->getId()]) ?>" class="btn btn-success">+ Pridať hodnotenie</a>
            <?php else: ?>
                <a href="<?= $link->url('review.create') ?>" class="btn btn-success">+ Pridať hodnotenie</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
        <p>Zatiaľ tu nie sú žiadne hodnotenia.</p>
    <?php else: ?>
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>Recept</th>
                <th>Autor</th>
                <th>Hodnotenie</th>
                <th>Komentár</th>
                <th>Dátum</th>
                <th>Akcie</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reviews as $review): ?>
                <?php $reviewRecipe = $review->getRecipe(); ?>
                <tr>
                    <td>
                        <?php if ($reviewRecipe): ?>
                            <a href="<?= $link->url('review.index', ['recipe_id' => $reviewRecipe->getId()]) ?>">
                                <?= e($reviewRecipe->getTitle()) ?>
                            </a>
                        <?php else: ?>
                            –
                        <?php endif; ?>
                    </td>
                    <td><?= e($review->getAuthor()) ?></td>
                    <td title="<?= e($review->getRating()) ?>/5">
                        <?= str_repeat('★', (int)$review->getRating()) . str_repeat('☆', 5 - (int)$review->getRating()) ?>
                    </td>
                    <td><?= nl2br(e($review->getComment() ?? '')) ?></td>
                    <td><?= e($review->getCreatedAt() ? date('d.m.Y', strtotime($review->getCreatedAt())) : '') ?></td>
                    <td class="text-nowrap">
                        <a href="<?= $link->url('review.edit', ['id' => $review->getId()]) ?>" class="btn btn-sm btn-primary">Upraviť</a>
                        <a href="<?= $link->url('review.delete', ['id' => $review->getId()]) ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Naozaj chcete vymazať toto hodnotenie?')">Vymazať</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
