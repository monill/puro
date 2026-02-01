<!DOCTYPE html>
<html lang="<?= locale() ?? 'pt-br' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? config('name', ' Puro') ?></title>

    <!-- Meta Tags -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?= $description ?? 'Jogo de estratégia online -  Puro' ?>">
    <meta name="keywords" content=", jogo, estratégia, online">
    <meta name="author" content="<?= config('name', ' Puro') ?>">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">

    <!-- CSS -->
    <link href="/assets/css/main.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/game.css" rel="stylesheet" type="text/css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet" type="text/css">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- JavaScript Head -->
    <script src="/assets/js/config.js" type="text/javascript"></script>
    <?php if (isset($extra_js_head)): ?>
        <?php foreach ($extra_js_head as $js): ?>
            <script src="<?= $js ?>" type="text/javascript"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="<?= $body_class ?? '' ?>">
    <?php if (config('maintenance_mode', false)): ?>
        <div class="maintenance-notice">
            ⚠️ Sistema em manutenção
        </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/">
                        <img src="/assets/images/logo.png" alt="<?= config('name', ' Puro') ?>">
                    </a>
                </div>

                <nav class="main-nav">
                    <?php if (auth()->check()): ?>
                        <ul>
                            <li><a href="/dashboard">Dashboard</a></li>
                            <li><a href="/villages">Aldeias</a></li>
                            <li><a href="/map">Mapa</a></li>
                            <li><a href="/alliances">Alianças</a></li>
                            <li><a href="/messages">Mensagens</a></li>
                            <li><a href="/profile">Perfil</a></li>
                            <li><a href="/logout">Sair</a></li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li><a href="/">Início</a></li>
                            <li><a href="/about">Sobre</a></li>
                            <li><a href="/register">Registrar</a></li>
                            <li><a href="/login">Login</a></li>
                        </ul>
                    <?php endif; ?>
                </nav>

                <div class="user-info">
                    <?php if (auth()->check()): ?>
                        <div class="user-stats">
                            <span class="username"><?= auth()->user()->username ?></span>
                            <span class="population">Pop: <?= auth()->user()->population ?></span>
                            <span class="gold">Ouro: <?= auth()->user()->gold ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (session()->has('success')): ?>
        <div class="flash-message success">
            <?= session()->get('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="flash-message error">
            <?= session()->get('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('warning')): ?>
        <div class="flash-message warning">
            <?= session()->get('warning') ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content">
        <div class="container">
