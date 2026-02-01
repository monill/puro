<!DOCTYPE html>
<html lang="<?= locale() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Test - <?= env('APP_NAME', ' Puro') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            cursor: pointer;
            border: none;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-warning {
            background: #f39c12;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .code {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }

        .success {
            color: #27ae60;
            font-weight: bold;
        }

        .warning {
            color: #f39c12;
            font-weight: bold;
        }

        .error {
            color: #e74c3c;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
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

        .status-ok {
            color: #27ae60;
        }

        .status-error {
            color: #e74c3c;
        }

        .type-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            color: white;
        }

        .type-boolean {
            background: #3498db;
        }

        .type-string {
            background: #2ecc71;
        }

        .type-integer {
            background: #e74c3c;
        }

        .type-null {
            background: #95a5a6;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .input-group {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }

        .input-group input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>🌍 Environment Variables Test</h1>
            <p>Testando o sistema de variáveis de ambiente (.env)</p>
        </div>

        <!-- Status dos Arquivos -->
        <div class="card">
            <h2>📁 Status dos Arquivos</h2>
            <div class="grid">
                <div>
                    <strong>.env:</strong>
                    <span class="<?= $envFileExists ? 'status-ok' : 'status-error' ?>">
                        <?= $envFileExists ? '✅ Existe' : '❌ Não encontrado' ?>
                    </span>
                    <br><small><?= $envFilePath ?></small>
                </div>
                <div>
                    <strong>.env.example:</strong>
                    <span class="<?= $envExampleExists ? 'status-ok' : 'status-error' ?>">
                        <?= $envExampleExists ? '✅ Existe' : '❌ Não encontrado' ?>
                    </span>
                    <br><small><?= $envExamplePath ?></small>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button onclick="reloadEnv()" class="btn">🔄 Recarregar .env</button>
                <button onclick="testDbConnection()" class="btn btn-success">🗄️ Testar Conexão BD</button>
            </div>
        </div>

        <!-- Variáveis do .env -->
        <div class="card">
            <h2>🔧 Variáveis do .env</h2>
            <table>
                <thead>
                    <tr>
                        <th>Chave</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envVars as $key => $value): ?>
                        <tr>
                            <td><code><?= $key ?></code></td>
                            <td>
                                <?php if ($key === 'DB_PASSWORD'): ?>
                                    <span class="warning">••••••••</span>
                                <?php else: ?>
                                    <?= is_bool($value) ? ($value ? 'true' : 'false') : $value ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="type-badge type-<?= gettype($value) ?>">
                                    <?= gettype($value) ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editEnvVar('<?= $key ?>', '<?= is_bool($value) ? ($value ? 'true' : 'false') : $value ?>')" class="btn" style="padding: 5px 10px; font-size: 0.8em;">
                                    ✏️ Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Testes de Tipos -->
        <div class="card">
            <h2>🧪 Testes de Tipos de Dados</h2>
            <div class="grid">
                <?php foreach ($typeTests as $test => $value): ?>
                    <div>
                        <strong><?= $test ?>:</strong>
                        <span class="type-badge type-<?= gettype($value) ?>">
                            <?= gettype($value) ?>
                        </span>
                        <br>
                        <code><?= is_bool($value) ? ($value ? 'true' : 'false') : var_export($value, true) ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formulário de Edição -->
        <div class="card" id="editForm" style="display: none;">
            <h2>✏️ Editar Variável</h2>
            <div class="input-group">
                <input type="text" id="editKey" placeholder="Chave" readonly>
                <input type="text" id="editValue" placeholder="Valor">
                <button onclick="saveEnvVar()" class="btn btn-success">💾 Salvar</button>
                <button onclick="cancelEdit()" class="btn btn-danger">❌ Cancelar</button>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card" id="results" style="display: none;">
            <h2>📊 Resultados</h2>
            <div id="resultContent"></div>
        </div>

        <!-- Como Funciona -->
        <div class="card">
            <h2>🎯 Como Funciona o Sistema .env</h2>
            <div class="code">
                <strong>1. Arquivo .env:</strong><br>
                APP_NAME=" Puro"<br>
                APP_DEBUG=true<br>
                DB_HOST=localhost<br>
                GAME_SPEED=1<br><br>

                <strong>2. Carregamento automático:</strong><br>
                ConfigHelper::env('APP_NAME') // " Puro"<br>
                ConfigHelper::env('APP_DEBUG') // true<br>
                ConfigHelper::env('GAME_SPEED') // 1<br><br>

                <strong>3. Parse automático de tipos:</strong><br>
                "true" → boolean true<br>
                "false" → boolean false<br>
                "123" → integer 123<br>
                "123.45" → float 123.45<br>
                "null" → null<br>
                "texto" → string "texto"<br><br>

                <strong>4. Uso no index.php:</strong><br>
                define('APP_NAME', env('APP_NAME', ' Puro'));<br>
                define('APP_DEBUG', env('APP_DEBUG', false));<br>
                define('APP_URL', env('APP_URL', 'http://localhost/puro'));
            </div>
        </div>

        <!-- Segurança -->
        <div class="card">
            <h2>🔒 Segurança</h2>
            <div class="code">
                <strong>✅ .gitignore configurado:</strong><br>
                .env → Não vai para o repositório<br>
                .env.local → Não vai para o repositório<br>
                .env.*.local → Não vai para o repositório<br><br>

                <strong>✅ .env.example como template:</strong><br>
                Copie .env.example para .env<br>
                Ajuste as configurações locais<br>
                Nunca commit o .env!<br><br>

                <strong>✅ Variáveis sensíveis:</strong><br>
                DB_PASSWORD → Oculta na interface<br>
                APP_KEY → Chave de criptografia<br>
                MAIL_PASSWORD → Senha do email
            </div>
        </div>
    </div>

    <script>
        function reloadEnv() {
            showResult('Recarregando variáveis de ambiente...');

            fetch('/env-test/reload')
                .then(response => response.json())
                .then(data => {
                    showResult(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    showResult('Erro: ' + error.message, 'error');
                });
        }

        function testDbConnection() {
            showResult('Testando conexão com banco de dados...');

            fetch('/env-test/test-database')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showResult(`
                            <strong>✅ Conexão bem-sucedida!</strong><br>
                            MySQL Version: ${data.mysql_version}<br>
                            Host: ${data.config.host}<br>
                            Database: ${data.config.database}
                        `, 'success');
                    } else {
                        showResult(`
                            <strong>❌ Erro na conexão:</strong><br>
                            ${data.message}<br>
                            ${data.error}
                        `, 'error');
                    }
                })
                .catch(error => {
                    showResult('Erro: ' + error.message, 'error');
                });
        }

        function editEnvVar(key, value) {
            document.getElementById('editKey').value = key;
            document.getElementById('editValue').value = value;
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editValue').focus();
        }

        function saveEnvVar() {
            const key = document.getElementById('editKey').value;
            const value = document.getElementById('editValue').value;

            if (!key || !value) {
                alert('Chave e valor são obrigatórios');
                return;
            }

            showResult('Salvando variável...');

            fetch('/env-test/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `key=${encodeURIComponent(key)}&value=${encodeURIComponent(value)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showResult(data.message, 'success');
                        cancelEdit();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showResult(data.message, 'error');
                    }
                })
                .catch(error => {
                    showResult('Erro: ' + error.message, 'error');
                });
        }

        function cancelEdit() {
            document.getElementById('editForm').style.display = 'none';
            document.getElementById('editKey').value = '';
            document.getElementById('editValue').value = '';
        }

        function showResult(message, type = 'info') {
            const resultsDiv = document.getElementById('results');
            const resultContent = document.getElementById('resultContent');

            resultsDiv.style.display = 'block';
            resultContent.innerHTML = message;

            // Adicionar classe de cor
            resultContent.className = type === 'success' ? 'success' : (type === 'error' ? 'error' : '');
        }

        // Auto-hide results after 5 seconds
        setTimeout(() => {
            const resultsDiv = document.getElementById('results');
            if (resultsDiv.style.display === 'block') {
                resultsDiv.style.display = 'none';
            }
        }, 5000);
    </script>
</body>

</html>
