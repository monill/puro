<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Página Inicial</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 1rem 0; margin-bottom: 2rem; }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #3498db; }
        .stat-label { color: #7f8c8d; margin-top: 5px; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { margin-bottom: 15px; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1><?= APP_NAME ?></h1>
            <nav class="nav">
                <a href="/">Início</a>
                <a href="/users">Usuários</a>
                <a href="/stats">Estatísticas</a>
                <a href="/test">Teste</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Estatísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalVillages ?></div>
                <div class="stat-label">Total de Aldeias</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $onlineUsers ?></div>
                <div class="stat-label">Usuários Online</div>
            </div>
        </div>

        <!-- Usuários Recentes -->
        <div class="section">
            <h2>Usuários Recentes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tripulação</th>
                        <th>Data de Criação</th>
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
                                <?= $user->tribe == 1 ? 'Romanos' : ($user->tribe == 2 ? 'Teutões' : 'Gálias') ?>
                            </span>
                        </td>
                        <td><?= $this->formatDate($user->created_at) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Aldeias Principais -->
        <div class="section">
            <h2>Maiores Aldeias</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>População</th>
                        <th>Dono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($capitalVillages as $village): ?>
                    <tr>
                        <td><?= $this->escape($village->name) ?></td>
                        <td><?= $this->formatNumber($village->population) ?></td>
                        <td>
                            <?php 
                            $owner = $village->getOwner();
                            echo $owner ? $this->escape($owner->username) : 'Desconhecido';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #7f8c8d;">
        <p>&copy; 2024 <?= APP_NAME ?> - Framework Custom PHP</p>
    </footer>
</body>
</html>
