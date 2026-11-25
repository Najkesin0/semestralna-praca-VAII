<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */

use App\Helpers\Ui;

// Aktívna položka navigácie podľa aktuálneho kontroléra / akcie
$controller = strtolower($_GET['c'] ?? 'recipe');
$action = $_GET['a'] ?? 'index';
$active = match (true) {
    $controller === 'plan' => 'plan',
    $controller === 'favorite' => 'favorite',
    $controller === 'recipe' && in_array($action, ['mine', 'create', 'edit', 'save'], true) => 'mine',
    $controller === 'review' => 'review',
    default => 'recipe',
};
$identity = $user->getIdentity();
$flash = Ui::takeFlash();

$navItems = [
        'recipe' => ['Recepty', $link->url('recipe.index')],
        'plan' => ['Môj plán', $link->url('plan.index')],
        'favorite' => ['Obľúbené', $link->url('favorite.index')],
        'mine' => ['Moje recepty', $link->url('recipe.mine')],
];
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(Ui::title()) ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous" defer></script>

    <?php // ?v=… podľa času poslednej zmeny súboru – prehliadač tak nikdy nepoužije starú verziu z cache
    $assetVersion = fn(string $file) => (string)@filemtime(dirname(__DIR__, 3) . '/public/' . $file); ?>
    <link rel="stylesheet" href="<?= $link->asset('css/style.css') ?>?v=<?= $assetVersion('css/style.css') ?>">
    <script src="<?= $link->asset('js/script.js') ?>?v=<?= $assetVersion('js/script.js') ?>" defer></script>
</head>
<body>

<header class="site-header">
    <div class="page-container header-inner">
        <a class="brand" href="<?= $link->url('recipe.index') ?>">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                     stroke-linejoin="round"><path d="M12 21v-9"/><path d="M12 12c0-4.2 3-6.6 7.5-6.6 0 4.2-3 6.6-7.5 6.6z"/><path d="M12 14.5c0-3.4-2.6-5.4-6.4-5.4 0 3.4 2.6 5.4 6.4 5.4z"/></svg>
            </span>
            <span class="brand-name">SmartRecipe <span>Hub</span></span>
        </a>

        <nav class="main-nav" aria-label="Hlavná navigácia">
            <?php foreach ($navItems as $key => [$label, $url]): ?>
                <a href="<?= $url ?>" class="<?= $active === $key ? 'active' : '' ?>"
                        <?= $active === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <?php if ($identity !== null): ?>
                <a class="icon-btn" href="<?= $link->url('plan.index') ?>" title="Tvoj plán na tento týždeň"
                   aria-label="Upozornenia a plán"><i class="bi bi-bell"></i></a>
                <div class="dropdown">
                    <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar"><?= e($identity->getInitials()) ?></span>
                        <span class="user-chip-name"><?= e($identity->getFirstName()) ?></span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><span class="dropdown-item-text small text-muted">Prihlásený ako <strong><?= e($identity->getUsername()) ?></strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $link->url('recipe.mine') ?>"><i class="bi bi-journal-text"></i> Moje recepty</a></li>
                        <li><a class="dropdown-item" href="<?= $link->url('recipe.create') ?>"><i class="bi bi-plus-lg"></i> Pridať recept</a></li>
                        <li><a class="dropdown-item" href="<?= $link->url('favorite.index') ?>"><i class="bi bi-heart"></i> Obľúbené</a></li>
                        <li><a class="dropdown-item" href="<?= $link->url('plan.index') ?>"><i class="bi bi-calendar3"></i> Môj plán</a></li>
                        <li><a class="dropdown-item" href="<?= $link->url('review.index') ?>"><i class="bi bi-star"></i> Hodnotenia</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= $link->url('auth.logout') ?>"><i class="bi bi-box-arrow-right"></i> Odhlásiť sa</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= $link->url('auth.register') ?>">Registrácia</a>
                <a class="btn btn-brand btn-sm" href="<?= $link->url('auth.login') ?>">Prihlásiť sa</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flash !== null): ?>
    <div class="page-container">
        <div class="flash flash-<?= e($flash['type']) ?>" role="status" data-flash>
            <i class="bi <?= $flash['type'] === 'danger' ? 'bi-exclamation-circle' : 'bi-check-circle' ?>"></i>
            <span><?= e($flash['message']) ?></span>
            <button type="button" class="flash-close" aria-label="Zavrieť" data-flash-close><i class="bi bi-x"></i></button>
        </div>
    </div>
<?php endif; ?>

<main class="page">
    <?= $contentHTML ?>
</main>

<footer class="site-footer">
    <div class="page-container footer-inner">
        <span>&copy; <?= date('Y') ?> SmartRecipe Hub</span>
        <span class="footer-claim">Menej starostí. Viac radosti z jedla.</span>
        <span class="footer-links">
            <a href="<?= $link->url('review.index') ?>">Hodnotenia</a>
            <a href="<?= $link->url('recipe.create') ?>">Pridať recept</a>
        </span>
    </div>
</footer>

<div class="toast-msg" id="toast" role="status" aria-live="polite"></div>
</body>
</html>
