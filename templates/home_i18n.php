<!DOCTYPE html>
<html lang="<?= TemplateHelper::currentLocale() ?>" dir="<?= TemplateHelper::textDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= TemplateHelper::trans('common.welcome') ?> - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 1rem 0; margin-bottom: 2rem; }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #3498db; }
        .stat-label { color: #7f8c8d; margin-top: 5px; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { margin-bottom: 15px; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: <?= TemplateHelper::textAlign('left') ?>; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { display: inline-block; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #2980b9; }
        .nav { display: flex; gap: 20px; }
        .nav a { color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; }
        .nav a:hover { background: rgba(255,255,255,0.1); }
        .tribe-badge { padding: 2px 8px; border-radius: 12px; font-size: 0.8em; color: white; }
        .tribe-romanos { background: #3498db; }
        .tribe-teutoes { background: #e74c3c; }
        .tribe-galias { background: #2ecc71; }
        .language-selector { margin-left: 20px; }
        .language-selector select { padding: 5px; border-radius: 4px; border: none; }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1><?= APP_NAME ?></h1>
            <nav class="nav">
                <a href="/"><?= TemplateHelper::trans('common.home') ?></a>
                <a href="/users"><?= TemplateHelper::trans('common.users') ?></a>
                <a href="/stats"><?= TemplateHelper::trans('common.statistics') ?></a>
                <a href="/test"><?= TemplateHelper::trans('common.about') ?></a>
                <div class="language-selector">
                    <?= TemplateHelper::languageSelector() ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Estatísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= TemplateHelper::formatNumber($totalUsers) ?></div>
                <div class="stat-label"><?= TemplateHelper::trans('users.total_users') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= TemplateHelper::formatNumber($totalVillages) ?></div>
                <div class="stat-label"><?= TemplateHelper::trans('users.total_villages') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= TemplateHelper::formatNumber($onlineUsers) ?></div>
                <div class="stat-label"><?= TemplateHelper::trans('users.online_users') ?></div>
            </div>
        </div>

        <!-- Usuários Recentes -->
        <div class="section">
            <h2><?= TemplateHelper::trans('users.user_list') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= TemplateHelper::trans('users.id') ?></th>
                        <th><?= TemplateHelper::trans('users.username') ?></th>
                        <th><?= TemplateHelper::trans('users.tribe') ?></th>
                        <th><?= TemplateHelper::trans('users.created_at') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?= $user->id ?></td>
                        <td>
                            <a href="/users/<?= $user->id ?>" class="btn">
                                <?= $this->escape($user->username) ?>
                            </a>
                        </td>
                        <td>
                            <span class="tribe-badge tribe-<?= $user->tribe == 1 ? 'romanos' : ($user->tribe == 2 ? 'teutoes' : 'galias') ?>">
                                <?= TemplateHelper::trans('users.tribes.' . $user->tribe) ?>
                            </span>
                        </td>
                        <td><?= TemplateHelper::formatDate($user->created_at) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Aldeias Principais -->
        <div class="section">
            <h2><?= TemplateHelper::trans('users.capital_village') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= TemplateHelper::trans('common.name') ?></th>
                        <th><?= TemplateHelper::trans('users.population') ?></th>
                        <th><?= TemplateHelper::trans('common.owner') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($capitalVillages as $village): ?>
                    <tr>
                        <td><?= $this->escape($village->name) ?></td>
                        <td><?= TemplateHelper::formatNumber($village->population) ?></td>
                        <td>
                            <?php 
                            $owner = $village->getOwner();
                            echo $owner ? $this->escape($owner->username) : TemplateHelper::trans('common.not_found');
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mensagem de Boas-vindas -->
        <div class="section">
            <h2><?= TemplateHelper::trans('common.welcome') ?></h2>
            <p><?= TemplateHelper::trans('common.saved_successfully') ?></p>
            <p><?= TemplateHelper::transChoice('common.items_count', $totalUsers) ?></p>
            <p><?= TemplateHelper::trans('users.new_users_today') ?>: <?= TemplateHelper::formatNumber(5) ?></p>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #7f8c8d;">
        <p>&copy; 2024 <?= APP_NAME ?> - <?= TemplateHelper::trans('common.about') ?></p>
    </footer>
</body>
</html>
