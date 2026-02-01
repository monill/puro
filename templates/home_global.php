<!DOCTYPE html>
<html lang="<?= locale() ?>" dir="<?= TemplateHelper::textDirection() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('common.welcome') ?> - <?= config('name', ' Puro') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }

        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn:hover {
            background: #2980b9;
        }

        .nav {
            display: flex;
            gap: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tribe-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            color: white;
        }

        .tribe-romanos {
            background: #3498db;
        }

        .tribe-teutoes {
            background: #e74c3c;
        }

        .tribe-galias {
            background: #2ecc71;
        }

        .language-selector {
            margin-left: 20px;
        }

        .language-selector select {
            padding: 5px;
            border-radius: 4px;
            border: none;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            <h1><?= config('name', ' Puro') ?></h1>
            <nav class="nav">
                <a href="/"><?= __('common.home') ?></a>
                <a href="/users"><?= __('common.users') ?></a>
                <a href="/stats"><?= __('common.statistics') ?></a>
                <a href="/test"><?= __('common.about') ?></a>
                <?php if (is_logged_in()): ?>
                    <a href="/logout"><?= __('common.logout') ?></a>
                <?php else: ?>
                    <a href="/login"><?= __('common.login') ?></a>
                <?php endif; ?>
                <div class="language-selector">
                    <?= language_selector() ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Flash Messages -->
        <?php $flash = flash();
        if (!empty($flash)): ?>
            <?php foreach ($flash as $type => $message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= $message ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= format_number($totalUsers) ?></div>
                <div class="stat-label"><?= __('users.total_users') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= format_number($totalVillages) ?></div>
                <div class="stat-label"><?= __('users.total_villages') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= format_number($onlineUsers) ?></div>
                <div class="stat-label"><?= __('users.online_users') ?></div>
            </div>
        </div>

        <!-- Usuários Recentes -->
        <div class="section">
            <h2><?= __('users.user_list') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= __('users.id') ?></th>
                        <th><?= __('users.username') ?></th>
                        <th><?= __('users.tribe') ?></th>
                        <th><?= __('users.created_at') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td>
                                <a href="/users/<?= $user->id ?>" class="btn">
                                    <?= sanitize($user->username) ?>
                                </a>
                            </td>
                            <td>
                                <span class="tribe-badge tribe-<?= $user->tribe == 1 ? 'romanos' : ($user->tribe == 2 ? 'teutoes' : 'galias') ?>">
                                    <?= __('users.tribes.' . $user->tribe) ?>
                                </span>
                            </td>
                            <td><?= format_date($user->created_at) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Aldeias Principais -->
        <div class="section">
            <h2><?= __('users.capital_village') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= __('common.name') ?></th>
                        <th><?= __('users.population') ?></th>
                        <th><?= __('common.owner') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($capitalVillages as $village): ?>
                        <tr>
                            <td><?= sanitize($village->name) ?></td>
                            <td><?= format_number($village->population) ?></td>
                            <td>
                                <?php
                                $owner = $village->getOwner();
                                echo $owner ? sanitize($owner->username) : __('common.not_found');
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mensagem de Boas-vindas -->
        <div class="section">
            <h2><?= __('common.welcome') ?></h2>
            <p><?= __('common.saved_successfully') ?></p>
            <p><?= trans_choice('common.items_count', $totalUsers) ?></p>
            <p><?= __('users.new_users_today') ?>: <?= format_number(5) ?></p>

            <?php if (is_debug()): ?>
                <hr>
                <h3>Debug Information</h3>
                <p><strong>Locale:</strong> <?= locale() ?></p>
                <p><strong>Environment:</strong> <?= env('APP_ENV', 'local') ?></p>
                <p><strong>Debug Mode:</strong> <?= is_debug() ? 'Yes' : 'No' ?></p>
                <p><strong>Current User:</strong> <?= is_logged_in() ? user()->username : 'Guest' ?></p>
                <p><strong>Storage Path:</strong> <?= storage_path() ?></p>
                <p><strong>Public Path:</strong> <?= public_path() ?></p>
                <p><strong>Base Path:</strong> <?= base_path() ?></p>
            <?php endif; ?>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #7f8c8d;">
        <p>&copy; <?= date('Y') ?> <?= config('name', ' Puro') ?> - <?= __('common.about') ?></p>
    </footer>

    <?php if (is_debug()): ?>
        <script>
            // Debug console
            console.log('=== DEBUG INFO ===');
            console.log('Locale:', '<?= locale() ?>');
            console.log('Environment:', '<?= env('APP_ENV', 'local') ?>');
            console.log('Debug Mode:', '<?= is_debug() ? 'true' : 'false' ?>');
            console.log('Current User:', '<?= is_logged_in() ? user()->username : 'Guest' ?>');
            console.log('Storage Path:', '<?= storage_path() ?>');
            console.log('Public Path:', '<?= public_path() ?>');
            console.log('Base Path:', '<?= base_path() ?>');
        </script>
    <?php endif; ?>
</body>

</html>
