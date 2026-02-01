<?php

/**
 *  Puro Installer - Step 1: Welcome
 */

// Check if already installed
if (file_exists(config_path('database.php'))) {
    header('Location: ../');
    exit;
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4.0 or higher is required.');
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'gd'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Required PHP extensions missing: ' . implode(', ', $missing_extensions));
}

// Check writable directories
$writable_dirs = [
    config_path(),
    storage_path(),
    storage_path('logs'),
    storage_path('cache'),
];

$writable_issues = [];
foreach ($writable_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!is_writable($dir)) {
        $writable_issues[] = $dir;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Puro - Instalação</title>
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

        .requirements {
            list-style: none;
        }

        .requirements li {
            padding: 10px 0;
            border-bottom: 1px solid #444;
        }

        .requirements li:last-child {
            border-bottom: none;
        }

        .ok {
            color: #4CAF50;
        }

        .error {
            color: #f44336;
        }

        .warning {
            color: #ff9800;
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

        .btn:disabled {
            background: #666;
            cursor: not-allowed;
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
            width: 25%;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏰 Puro</h1>
            <p>Instalador do Sistema</p>
        </div>

        <div class="step-indicator">
            <span class="step active">1</span>
            <span class="step">2</span>
            <span class="step">3</span>
            <span class="step">4</span>
        </div>

        <div class="progress">
            <div class="progress-bar"></div>
        </div>

        <div class="card">
            <h2>👋 Bem-vindo ao Puro</h2>
            <p>Este instalador irá configurar seu servidor do Puro em 4 passos simples:</p>
            <ol style="margin: 20px 0; padding-left: 20px;">
                <li>Verificação dos requisitos do sistema</li>
                <li>Configuração do banco de dados</li>
                <li>Configurações avançadas do sistema</li>
                <li>Finalização e criação do administrador</li>
            </ol>
        </div>

        <div class="card">
            <h2>🔍 Verificação de Requisitos</h2>
            <ul class="requirements">
                <li>
                    <strong>Versão PHP:</strong>
                    <span class="<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error' ?>">
                        <?= PHP_VERSION ?> <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? '✅' : '❌ (requer 7.4.0+)' ?>
                    </span>
                </li>

                <?php foreach ($required_extensions as $ext): ?>
                    <li>
                        <strong>Extensão <?= $ext ?>:</strong>
                        <span class="<?= extension_loaded($ext) ? 'ok' : 'error' ?>">
                            <?= extension_loaded($ext) ? 'Instalada ✅' : 'Não encontrada ❌' ?>
                        </span>
                    </li>
                <?php endforeach; ?>

                <?php foreach ($writable_dirs as $dir): ?>
                    <li>
                        <strong>Permissão de escrita:</strong>
                        <span class="<?= is_writable($dir) ? 'ok' : 'error' ?>">
                            <?= str_replace(base_path() . '/', '', $dir) ?>
                            <?= is_writable($dir) ? '✅' : '❌ (requer permissão de escrita)' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if (!empty($missing_extensions) || !empty($writable_issues)): ?>
            <div class="card" style="border-color: #f44336;">
                <h2 style="color: #f44336;">❌ Problemas Encontrados</h2>
                <p>Por favor, corrija os problemas acima antes de continuar.</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>📋 Informações do Sistema</h2>
            <ul style="list-style: none;">
                <li><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
                <li><strong>PHP Version:</strong> <?= PHP_VERSION ?></li>
                <li><strong>Sistema Operacional:</strong> <?= PHP_OS ?></li>
                <li><strong>Memory Limit:</strong> <?= ini_get('memory_limit') ?></li>
                <li><strong>Max Execution Time:</strong> <?= ini_get('max_execution_time') ?>s</li>
                <li><strong>Upload Max Filesize:</strong> <?= ini_get('upload_max_filesize') ?></li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <?php if (empty($missing_extensions) && empty($writable_issues)): ?>
                <a href="database.php" class="btn">Próximo Passo →</a>
            <?php else: ?>
                <button class="btn" disabled>Corrija os problemas antes de continuar</button>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
