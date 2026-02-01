<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 1rem 0; margin-bottom: 2rem; }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav { display: flex; gap: 20px; }
        .nav a { color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; }
        .nav a:hover { background: rgba(255,255,255,0.1); }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .filters button { padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .filters button:hover { background: #2980b9; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { display: inline-block; padding: 6px 12px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .tribe-badge { padding: 2px 8px; border-radius: 12px; font-size: 0.8em; color: white; }
        .tribe-romanos { background: #3498db; }
        .tribe-teutoes { background: #e74c3c; }
        .tribe-galias { background: #2ecc71; }
        .pagination { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .pagination a { padding: 8px 12px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        .pagination a:hover { background: #2980b9; }
        .pagination .current { background: #2c3e50; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { background: #f8f9fa; padding: 10px 15px; border-radius: 4px; }
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
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="section">
            <h2>Lista de Usuários</h2>
            
            <!-- Filtros -->
            <div class="stats">
                <div class="stat">
                    <strong>Total:</strong> <?= $totalUsers ?> usuários
                </div>
                <div class="stat">
                    <strong>Página:</strong> <?= $currentPage ?> de <?= $totalPages ?>
                </div>
            </div>

            <form method="GET" class="filters">
                <input type="text" name="search" placeholder="Buscar usuário..." value="<?= $this->escape($search) ?>">
                <select name="tribe">
                    <option value="">Todas as Tribos</option>
                    <option value="1" <?= $tribe == 1 ? 'selected' : '' ?>>Romanos</option>
                    <option value="2" <?= $tribe == 2 ? 'selected' : '' ?>>Teutões</option>
                    <option value="3" <?= $tribe == 3 ? 'selected' : '' ?>>Gálias</option>
                </select>
                <button type="submit">Filtrar</button>
                <a href="/users" class="btn">Limpar</a>
                <a href="/users/create" class="btn btn-success">Novo Usuário</a>
            </form>

            <!-- Tabela -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tripulação</th>
                        <th>População</th>
                        <th>Último Login</th>
                        <th>Criação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user->id ?></td>
                        <td><?= $this->escape($user->username) ?></td>
                        <td><?= $this->escape($user->email) ?></td>
                        <td>
                            <span class="tribe-badge tribe-<?= $user->tribe == 1 ? 'romanos' : ($user->tribe == 2 ? 'teutoes' : 'galias') ?>">
                                <?= $user->tribe == 1 ? 'Romanos' : ($user->tribe == 2 ? 'Teutões' : 'Gálias') ?>
                            </span>
                        </td>
                        <td><?= $this->formatNumber($user->population) ?></td>
                        <td><?= $user->last_login ? $this->timeAgo($user->last_login) : 'Nunca' ?></td>
                        <td><?= $this->formatDate($user->created_at) ?></td>
                        <td>
                            <a href="/users/<?= $user->id ?>" class="btn">Ver</a>
                            <a href="/users/<?= $user->id ?>/edit" class="btn">Editar</a>
                            <button onclick="deleteUser(<?= $user->id ?>)" class="btn btn-danger">Excluir</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="/users?page=<?= $currentPage - 1 ?>&search=<?= $this->escape($search) ?>&tribe=<?= $tribe ?>">«</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="/users?page=<?= $i ?>&search=<?= $this->escape($search) ?>&tribe=<?= $tribe ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="/users?page=<?= $currentPage + 1 ?>&search=<?= $this->escape($search) ?>&tribe=<?= $tribe ?>">»</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteUser(userId) {
            if (confirm('Tem certeza que deseja excluir este usuário?')) {
                fetch(`/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Usuário excluído com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao excluir usuário: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro ao excluir usuário');
                    console.error(error);
                });
            }
        }
    </script>
</body>
</html>
