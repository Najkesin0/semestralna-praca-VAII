<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? App\Configuration::APP_NAME ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>

    <link rel="stylesheet" href="<?= $link->asset('css/style.css') ?>">
    <script src="<?= $link->asset('js/script.js') ?>"></script>
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <span class="logo-icon">🌿</span>
            <h1>SmartRecipe Hub</h1>
        </div>

        <nav class="main-nav">
            <span class="nav-label">Navigácia stránky:</span>
            <ul>
                <li><a href="<?= $link->url('recipe.index') ?>">Recepty</a></li>
                <li><a href="<?= $link->url('review.index') ?>">Hodnotenia</a></li>
                <li><a href="#">Môj plán</a></li>
                <li><a href="#">Obľúbené</a></li>
                <li><a href="<?= $link->url('home.index') ?>">Moje recepty</a></li>
            </ul>
        </nav>

        <div class="user-profile">
            <img src="https://via.placeholder.com/40/10b981/ffffff?text=User" alt="Profilová fotka užívateľa" class="user-avatar-img">

            <details class="user-dropdown">
                <summary class="user-name">
                    User <span class="dropdown-arrow">▼</span>
                </summary>
                <ul class="dropdown-menu">
                    <li><a href="#">👤 Môj profil</a></li>
                    <li><a href="<?= $link->url('home.index') ?>">📖 Moje recepty</a></li>
                    <li><a href="#">⭐ Obľúbené</a></li>
                    <li><a href="#">📅 Môj plán</a></li>
                    <li><a href="#">⚙️ Nastavenia účtu</a></li>
                    <li class="divider"></li>
                    <li><a href="#" class="logout-link">Odhlásiť sa</a></li>
                </ul>
            </details>
        </div>
    </div>
</header>

<?= $contentHTML ?>

<footer class="main-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> SmartRecipe Hub. Všetky práva vyhradené. Semestrálna práca.</p>
    </div>
</footer>

</body>
</html>