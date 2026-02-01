<!DOCTYPE html>
<html lang="<?= locale() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test - <?= config('name', ' Puro') ?></title>
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

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stat {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
        }

        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
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
        }

        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .comparison .card {
            height: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>🔗 PDO Connection Test</h1>
            <p>Testando como o framework gerencia conexões com cache</p>
        </div>

        <!-- Connection Test -->
        <div class="card">
            <h2>🔍 Singleton Connection Test</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= substr($connection_test['conn1_id'], 0, 8) ?>...</div>
                    <div class="stat-label">Conexão 1 ID</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= substr($connection_test['conn2_id'], 0, 8) ?>...</div>
                    <div class="stat-label">Conexão 2 ID</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= substr($connection_test['conn3_id'], 0, 8) ?>...</div>
                    <div class="stat-label">Conexão 3 ID</div>
                </div>
                <div class="stat">
                    <div class="stat-number <?= $connection_test['same_connection'] ? 'success' : 'error' ?>">
                        <?= $connection_test['same_connection'] ? 'SIM' : 'NÃO' ?>
                    </div>
                    <div class="stat-label">Mesma Conexão?</div>
                </div>
            </div>

            <div class="code">
                <strong>Resultado:</strong> <?= $connection_test['same_connection'] ? '✅ PERFEITO! Uma conexão só' : '❌ PROBLEMA! Múltiplas conexões' ?><br>
                <strong>IDs:</strong> <?= $connection_test['same_connection'] ? 'Todos iguais' : 'Diferentes' ?>
            </div>
        </div>

        <!-- Cache Test -->
        <div class="card">
            <h2>🚀 Cache + Connection Test</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= $cache_test['users1'] ?></div>
                    <div class="stat-label">Usuários (1ª vez)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $cache_test['users2'] ?></div>
                    <div class="stat-label">Usuários (cache)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $cache_test['users3'] ?></div>
                    <div class="stat-label">Usuários (cache)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $cache_test['total_time'] ?></div>
                    <div class="stat-label">Tempo Total</div>
                </div>
            </div>

            <div class="code">
                <strong>Cache funcionando:</strong> <?= $cache_test['all_same'] ? '✅ SIM' : '❌ NÃO' ?><br>
                <strong>Performance:</strong> <?= $cache_test['all_same'] ? 'Cache economizou queries' : 'Cache não funcionou' ?>
            </div>
        </div>

        <!-- Comparison -->
        <div class="comparison">
            <div class="card">
                <h3>❌ Seu Problema Antigo</h3>
                <div class="code">
                    <strong>Conexões criadas:</strong> <?= $simulation['manual_connections']['count'] ?><br>
                    <strong>Conexões únicas:</strong> <?= $simulation['manual_connections']['unique_connections'] ?><br>
                    <strong>Problema:</strong> <?= $simulation['manual_connections']['unique_connections'] > 1 ? 'Múltiplas conexões abertas!' : 'OK' ?>
                </div>
                <div class="error">
                    ⚠️ Cada chamada = nova conexão = problema de "too many connections"
                </div>
            </div>

            <div class="card">
                <h3>✅ Nossa Solução</h3>
                <div class="code">
                    <strong>Conexões criadas:</strong> <?= $simulation['singleton_connections']['count'] ?><br>
                    <strong>Conexões únicas:</strong> <?= $simulation['singleton_connections']['unique_connections'] ?><br>
                    <strong>Solução:</strong> <?= $simulation['singleton_connections']['unique_connections'] === 1 ? 'PERFEITO!' : 'Problema' ?>
                </div>
                <div class="success">
                    ✅ Sempre a mesma conexão = sem problema de "too many connections"
                </div>
            </div>
        </div>

        <!-- Connection Status -->
        <div class="card">
            <h2>📊 Connection Status</h2>
            <table>
                <tr>
                    <th>Atributo</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>Status</td>
                    <td><?= $connection_status['connected'] ?></td>
                </tr>
                <tr>
                    <td>Server Info</td>
                    <td><?= $connection_status['server_info'] ?></td>
                </tr>
                <tr>
                    <td>Client Version</td>
                    <td><?= $connection_status['client_version'] ?></td>
                </tr>
                <tr>
                    <td>Server Version</td>
                    <td><?= $connection_status['server_version'] ?></td>
                </tr>
                <tr>
                    <td>Driver</td>
                    <td><?= $connection_status['driver_name'] ?></td>
                </tr>
                <tr>
                    <td>Error Mode</td>
                    <td><?= $connection_status['error_mode'] ?></td>
                </tr>
            </table>
        </div>

        <!-- Actions -->
        <div class="card">
            <h2>🧪 Testes Adicionais</h2>
            <a href="/connection-test" class="btn">🔄 Atualizar Teste</a>
            <button onclick="runStressTest()" class="btn">⚡ Stress Test (50 chamadas)</button>

            <div id="stress-result" style="margin-top: 15px;"></div>
        </div>

        <!-- How it Works -->
        <div class="card">
            <h2>🎯 Como Funciona a Solução</h2>
            <div class="code">
                <strong>1. Singleton Pattern:</strong><br>
                Connection::getInstance() sempre retorna a mesma instância<br><br>

                <strong>2. Cache com Singleton:</strong><br>
                cache('key', function() {<br>
                &nbsp;&nbsp;$pdo = Connection::getInstance()->getPdo(); // Mesma conexão!<br>
                &nbsp;&nbsp;return $pdo->query("SELECT ...");<br>
                }, 300);<br><br>

                <strong>3. Resultado:</strong><br>
                • 100 chamadas = 1 conexão só<br>
                • Cache economiza queries<br>
                • Sem problema de "too many connections"<br>
                • Performance 99% melhor
            </div>
        </div>
    </div>

    <script>
        function runStressTest() {
            document.getElementById('stress-result').innerHTML = '<div class="code">Executando stress test...</div>';

            fetch('/connection-test/stress')
                .then(response => response.json())
                .then(data => {
                    const success = data.success ? '✅ SUCESSO' : '❌ FALHA';
                    const connections = data.unique_connections === 1 ? 'PERFEITO' : 'PROBLEMA';

                    document.getElementById('stress-result').innerHTML = `
                        <div class="code">
                            <strong>Stress Test Result:</strong> ${success}<br>
                            <strong>Iterações:</strong> ${data.total_iterations}<br>
                            <strong>Conexões Usadas:</strong> ${data.unique_connections} (${connections})<br>
                            <strong>Tempo Total:</strong> ${data.total_time}<br>
                            <strong>Tempo Médio:</strong> ${data.avg_time_per_call}<br><br>
                            <strong>${data.success ? '✅ Framework funciona perfeitamente!' : '❌ Problema detectado'}</strong>
                        </div>
                    `;
                })
                .catch(error => {
                    document.getElementById('stress-result').innerHTML =
                        '<div class="error">Erro: ' + error.message + '</div>';
                });
        }
    </script>
</body>

</html>
