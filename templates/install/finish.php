<?php

/**
 *  Puro Installer - Step 4: Finish
 */

// Check if all configs are created
$required_configs = [
    config_path('database.php'),
    config_path('app.php'),
    config_path('cache.php'),
    config_path('security.php')
];

$missing_configs = [];
foreach ($required_configs as $config) {
    if (!file_exists($config)) {
        $missing_configs[] = basename($config);
    }
}

if (!empty($missing_configs)) {
    header('Location: settings.php');
    exit;
}

// Load configurations
$db_config = config('database');
$app_config = config('app');

// Process admin creation
$errors = [];
$success = false;

if ($_POST) {
    $admin_username = $_POST['admin_username'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';

    // Validation
    if (empty($admin_username)) $errors[] = 'Nome de usuário é obrigatório';
    if (empty($admin_email)) $errors[] = 'Email é obrigatório';
    if (empty($admin_password)) $errors[] = 'Senha é obrigatória';
    if ($admin_password !== $admin_password_confirm) $errors[] = 'Senhas não conferem';
    if (strlen($admin_password) < 8) $errors[] = 'Senha deve ter pelo menos 8 caracteres';

    if (empty($errors)) {
        try {
            // Connect to database
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if admin already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$admin_username, $admin_email]);

            if ($stmt->rowCount() > 0) {
                $errors[] = 'Nome de usuário ou email já existe';
            } else {
                // Create admin user
                $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        username, email, password, tribe, role, population,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, 'romans', 'admin', 0, NOW(), NOW())
                ");

                $stmt->execute([$admin_username, $admin_email, $password_hash]);
                $admin_id = $pdo->lastInsertId();

                // Create starting village for admin
                $stmt = $pdo->prepare("
                    INSERT INTO villages (
                        user_id, name, x, y, tribe, population, wood, clay, iron, crop,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, ?, 'romans', 2, 500, 500, 500, 500, NOW(), NOW())
                ");

                // Find empty position for village
                $map_size = $app_config['game']['map_size'] ?? 400;
                $village_x = rand(-$map_size / 2, $map_size / 2);
                $village_y = rand(-$map_size / 2, $map_size / 2);
                $village_name = $admin_username . "'s Village";

                $stmt->execute([$admin_id, $village_name, $village_x, $village_y]);

                // Create installation lock file
                $lock_content = "<?php\n";
                $lock_content .= "// Installation completed on: " . date('Y-m-d H:i:s') . "\n";
                $lock_content .= "// Admin user ID: " . $admin_id . "\n";
                $lock_content .= "// Do not delete this file!\n";

                file_put_contents(storage_path('.installed'), $lock_content);

                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'Erro ao criar administrador: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Puro - Finalização</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #1a1a1a;
            color: #fff;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .header p {
            color: #ccc;
            font-size: 1.1em;
        }

        .card {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #444;
        }

        .card h2 {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            background: #444;
            border: 1px solid #666;
            border-radius: 5px;
            color: #fff;
            font-size: 1em;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #45a049;
        }

        .btn-secondary {
            background: #666;
        }

        .btn-secondary:hover {
            background: #555;
        }

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #229954;
        }

        .error {
            color: #f44336;
            margin: 10px 0;
            padding: 10px;
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            border-radius: 5px;
        }

        .success {
            color: #4CAF50;
            margin: 10px 0;
            padding: 10px;
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
            border-radius: 5px;
        }

        .progress {
            background: #444;
            height: 5px;
            border-radius: 3px;
            margin: 20px 0;
        }

        .progress-bar {
            background: #4CAF50;
            height: 100%;
            width: 100%;
            border-radius: 3px;
        }

        .step-indicator {
            text-align: center;
            margin-bottom: 30px;
        }

        .step {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #444;
            border-radius: 50%;
            line-height: 30px;
            margin: 0 5px;
        }

        .step.active {
            background: #4CAF50;
        }

        .step.completed {
            background: #666;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .help-text {
            font-size: 0.9em;
            color: #999;
            margin-top: 5px;
        }

        .summary {
            background: #444;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .summary h3 {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .summary ul {
            list-style: none;
        }

        .summary li {
            padding: 5px 0;
        }

        .summary li strong {
            color: #ccc;
        }

        .celebration {
            text-align: center;
            font-size: 3em;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏰 Puro</h1>
            <p>Finalização da Instalação</p>
        </div>

        <div class="step-indicator">
            <span class="step completed">1</span>
            <span class="step completed">2</span>
            <span class="step completed">3</span>
            <span class="step active">4</span>
        </div>

        <div class="progress">
            <div class="progress-bar"></div>
        </div>

        <?php if ($success): ?>
            <div class="card" style="border-color: #4CAF50;">
                <div class="celebration">🎉</div>
                <h2 style="color: #4CAF50; text-align: center;">✅ Instalação Concluída com Sucesso!</h2>
                <p style="text-align: center; font-size: 1.2em; margin: 20px 0;">
                    Seu servidor Puro está pronto para usar!
                </p>

                <div class="summary">
                    <h3>📋 Resumo da Instalação</h3>
                    <ul>
                        <li><strong>Servidor:</strong> <?= $app_config['name'] ?></li>
                        <li><strong>URL:</strong> <?= $app_config['url'] ?></li>
                        <li><strong>Banco de Dados:</strong> <?= $db_config['database'] ?></li>
                        <li><strong>Administrador:</strong> <?= htmlspecialchars($admin_username) ?></li>
                        <li><strong>Velocidade do Jogo:</strong> <?= $app_config['game']['speed'] ?>x</li>
                        <li><strong>Máximo de Jogadores:</strong> <?= $app_config['game']['max_players'] ?></li>
                    </ul>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h3>🚀 Próximos Passos</h3>
                    <ol style="margin: 20px 0; padding-left: 20px;">
                        <li><strong>Acesse seu servidor:</strong> <a href="<?= $app_config['url'] ?>" style="color: #4CAF50;"><?= $app_config['url'] ?></a></li>
                        <li><strong>Faça login</strong> com o administrador criado</li>
                        <li><strong>Configure as regras do servidor</strong> no painel admin</li>
                        <li><strong>Abra o registro</strong> para novos jogadores</li>
                        <li><strong>Divulgue seu servidor</strong> e comece a jogar!</li>
                    </ol>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="<?= $app_config['url'] ?>" class="btn btn-success" style="font-size: 1.2em; padding: 15px 40px;">
                        🎮 Acessar Servidor Agora
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="card">
                <h2>👤 Criar Administrador</h2>
                <p>Crie a conta de administrador para gerenciar seu servidor.</p>

                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p>• <?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_username">Nome de Usuário</label>
                            <input type="text" id="admin_username" name="admin_username" value="<?= $_POST['admin_username'] ?? '' ?>" required>
                            <div class="help-text">Nome de usuário do administrador</div>
                        </div>

                        <div class="form-group">
                            <label for="admin_email">Email</label>
                            <input type="email" id="admin_email" name="admin_email" value="<?= $_POST['admin_email'] ?? '' ?>" required>
                            <div class="help-text">Email do administrador</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_password">Senha</label>
                            <input type="password" id="admin_password" name="admin_password" required>
                            <div class="help-text">Mínimo 8 caracteres</div>
                        </div>

                        <div class="form-group">
                            <label for="admin_password_confirm">Confirmar Senha</label>
                            <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
                            <div class="help-text">Digite a senha novamente</div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <a href="settings.php" class="btn btn-secondary">← Voltar</a>
                        <button type="submit" class="btn">Criar Administrador e Finalizar</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>📋 Resumo da Configuração</h2>
                <div class="summary">
                    <h3>Configurações Salvas</h3>
                    <ul>
                        <li><strong>✅ Banco de Dados:</strong> Configurado</li>
                        <li><strong>✅ Aplicação:</strong> Configurada</li>
                        <li><strong>✅ Cache:</strong> Configurado</li>
                        <li><strong>✅ Segurança:</strong> Configurada</li>
                        <li><strong>⏳ Administrador:</strong> Aguardando criação</li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>
