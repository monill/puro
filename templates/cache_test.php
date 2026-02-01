<!DOCTYPE html>
<html lang="<?= locale() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Test - <?= config('name', ' Puro') ?></title>
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
        }

        .success {
            color: #27ae60;
        }

        .warning {
            color: #f39c12;
        }

        .error {
            color: #e74c3c;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>🚀 Cache Test Dashboard</h1>
            <p>Testando o sistema de cache do framework</p>
        </div>

        <!-- Performance -->
        <div class="card">
            <h2>⚡ Performance Test</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= number_format($timeWithoutCache, 2) ?>ms</div>
                    <div class="stat-label">Sem Cache (primeira vez)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= number_format($timeWithCache, 2) ?>ms</div>
                    <div class="stat-label">Com Cache (segunda vez)</div>
                </div>
                <div class="stat">
                    <div class="stat-number success"><?= number_format(($timeWithoutCache - $timeWithCache) / $timeWithoutCache * 100, 1) ?>%</div>
                    <div class="stat-label">Economia de tempo</div>
                </div>
            </div>
        </div>

        <!-- Cache Data -->
        <div class="card">
            <h2>📊 Cache Data</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= $users1 ?></div>
                    <div class="stat-label">Usuários (cache 1)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $users2 ?></div>
                    <div class="stat-label">Usuários (cache 2)</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= count($complexData['users']) ?></div>
                    <div class="stat-label">Usuários no complexo</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= count($complexData['villages']) ?></div>
                    <div class="stat-label">Aldeias no complexo</div>
                </div>
            </div>

            <h3>Dados Complexos Cacheados:</h3>
            <div class="code">
                <pre><?= json_encode($complexData, JSON_PRETTY_PRINT) ?></pre>
            </div>
        </div>

        <!-- Cache Stats -->
        <div class="card">
            <h2>📈 Cache Statistics</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= $cacheStats['total_files'] ?></div>
                    <div class="stat-label">Arquivos em cache</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $cacheStats['total_size'] ?></div>
                    <div class="stat-label">Tamanho total</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $cacheDir ?></div>
                    <div class="stat-label">Diretório do cache</div>
                </div>
            </div>

            <?php if (!empty($cacheStats['files'])): ?>
                <h3>Arquivos de Cache:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th>Tamanho</th>
                            <th>Modificado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cacheStats['files'] as $file): ?>
                            <tr>
                                <td><?= $file['name'] ?></td>
                                <td><?= $file['size'] ?></td>
                                <td><?= $file['modified'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="card">
            <h2>🔧 Cache Actions</h2>
            <a href="/cache-test" class="btn">🔄 Atualizar Teste</a>
            <a href="/cache-test/clear" class="btn btn-danger" onclick="return confirm('Tem certeza?')">🗑️ Limpar Cache</a>
            <button onclick="testRemember()" class="btn">🧪 Testar Remember</button>

            <div id="remember-result" style="margin-top: 15px;"></div>
        </div>

        <!-- How it Works -->
        <div class="card">
            <h2>🎯 Como o Cache Funciona</h2>
            <div class="code">
                <strong>1. Primeira visita (sem cache):</strong><br>
                cache('user_list', function() {<br>
                &nbsp;&nbsp;&nbsp;&nbsp;return User::all(); // Executa query lenta<br>
                }, 300); // Salva por 5 minutos<br><br>

                <strong>2. Segunda visita (com cache):</strong><br>
                $users = cache('user_list'); // Lê do arquivo, instantâneo<br><br>

                <strong>3. Onde fica salvo:</strong><br>
                storage/cache/a1b2c3d4e5f6.cache (md5 da chave)<br><br>

                <strong>4. Estrutura do arquivo:</strong><br>
                [<br>
                &nbsp;&nbsp;'value' => dados_cached,<br>
                &nbsp;&nbsp;'expires' => 1706844000,<br>
                &nbsp;&nbsp;'created' => 1706840400<br>
                ]
            </div>
        </div>
    </div>

    <script>
        function testRemember() {
            fetch('/cache-test/remember')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('remember-result').innerHTML =
                        '<div class="code"><strong>Remember Test Result:</strong><br>' +
                        JSON.stringify(data, null, 2) + '</div>';
                })
                .catch(error => {
                    document.getElementById('remember-result').innerHTML =
                        '<div class="error">Erro: ' + error.message + '</div>';
                });
        }

        // Auto refresh a cada 10 segundos para mostrar cache working
        setTimeout(() => {
            window.location.reload();
        }, 10000);
    </script>
</body>

</html>
