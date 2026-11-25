<?php

/** @var string $contentHTML */
/** @var \Framework\Support\LinkGenerator $link */

use App\Helpers\Ui;
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(Ui::title()) ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php // ?v=… podľa času poslednej zmeny súboru – prehliadač tak nikdy nepoužije starú verziu z cache
    $assetVersion = fn(string $file) => (string)@filemtime(dirname(__DIR__, 3) . '/public/' . $file); ?>
    <link rel="stylesheet" href="<?= $link->asset('css/style.css') ?>?v=<?= $assetVersion('css/style.css') ?>">
</head>
<body class="auth-body">
<main class="auth-wrap">
    <a class="brand auth-brand" href="<?= $link->url('recipe.index') ?>">
        <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round"><path d="M12 21v-9"/><path d="M12 12c0-4.2 3-6.6 7.5-6.6 0 4.2-3 6.6-7.5 6.6z"/><path d="M12 14.5c0-3.4-2.6-5.4-6.4-5.4 0 3.4 2.6 5.4 6.4 5.4z"/></svg>
        </span>
        <span class="brand-name">SmartRecipe <span>Hub</span></span>
    </a>
    <?= $contentHTML ?>
</main>
</body>
</html>
