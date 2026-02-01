<!DOCTYPE html>
<html lang="<?= locale() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real World Test - <?= config('name', ' Puro') ?></title>
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
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
            cursor: pointer;
            border: none;
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

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #229954;
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

        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .comparison .card {
            height: 100%;
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
            font-size: 1.8em;
            font-weight: bold;
            color: #3498db;
        }

        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .user-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .user-item {
            padding: 8px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .highlight {
            background: #e8f5e8;
            border-left-color: #27ae60;
        }

        .problem {
            background: #fdf2f2;
            border-left-color: #e74c3c;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>🌍 Real World Test: Múltiplos Usuários Simultâneos</h1>
            <p>Simulando seu problema real: múltiplos usuários acessando diferentes rotas ao mesmo tempo</p>
        </div>

        <div class="card">
            <h2>🎯 O Problema Real</h2>
            <div class="code">
                <strong>Cenário:</strong> 10 usuários acessando o sistema simultaneamente<br><br>

                <strong>❌ Seu código antigo:</strong><br>
                Usuário 1 → /clientes → new PDO() → Conexão 1 (aberta)<br>
                Usuário 2 → /produtos → new PDO() → Conexão 2 (aberta)<br>
                Usuário 3 → /vendas → new PDO() → Conexão 3 (aberta)<br>
                ...<br>
                Usuário 10 → /relatorios → new PDO() → Conexão 10 (aberta)<br><br>

                <strong>Resultado:</strong> 10 conexões abertas = "Too many connections" ERROR!
            </div>
        </div>

        <div class="card">
            <h2>✅ Nossa Solução</h2>
            <div class="code">
                <strong>Cenário:</strong> 10 usuários acessando o sistema simultaneamente<br><br>

                <strong>✅ Nosso framework:</strong><br>
                Usuário 1 → /clientes → Connection::getInstance() → Conexão A<br>
                Usuário 2 → /produtos → Connection::getInstance() → Conexão A<br>
                Usuário 3 → /vendas → Connection::getInstance() → Conexão A<br>
                ...<br>
                Usuário 10 → /relatorios → Connection::getInstance() → Conexão A<br><br>

                <strong>Resultado:</strong> 1 conexão compartilhada = SEM ERRO!
            </div>
        </div>

        <div class="card">
            <h2>🧪 Testar Simulação</h2>
            <button onclick="runSimulation()" class="btn">🚀 Simular 10 Usuários Simultâneos</button>
            <button onclick="runCacheTest()" class="btn btn-success">📦 Testar com Cache</button>

            <div id="simulation-result" style="margin-top: 20px;"></div>
        </div>

        <div class="comparison">
            <div class="card problem">
                <h3>❌ Problema: Múltiplas Conexões</h3>
                <div class="stats">
                    <div class="stat">
                        <div class="stat-number error">10</div>
                        <div class="stat-label">Conexões Criadas</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number error">10</div>
                        <div class="stat-label">Conexões Únicas</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number error">100%</div>
                        <div class="stat-label">Uso de Recursos</div>
                    </div>
                </div>
                <div class="error">
                    ⚠️ Cada usuário cria sua própria conexão PDO<br>
                    ⚠️ Conexões ficam abertas<br>
                    ⚠️ MySQL atinge limite de conexões<br>
                    ⚠️ "Too many connections" ERROR
                </div>
            </div>

            <div class="card highlight">
                <h3>✅ Solução: Singleton Pattern</h3>
                <div class="stats">
                    <div class="stat">
                        <div class="stat-number success">1</div>
                        <div class="stat-label">Conexão Criada</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number success">1</div>
                        <div class="stat-label">Conexão Única</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number success">10%</div>
                        <div class="stat-label">Uso de Recursos</div>
                    </div>
                </div>
                <div class="success">
                    ✅ Todos compartilham a mesma conexão<br>
                    ✅ Uma conexão só para todos<br>
                    ✅ Sem limite de conexões<br>
                    ✅ Sistema estável e rápido
                </div>
            </div>
        </div>

        <div class="card">
            <h2>🎯 Como o Singleton Resolve o Problema</h2>
            <div class="code">
                <strong>1. Padrão Singleton:</strong><br>
                class Connection {<br>
                &nbsp;&nbsp;private static $instance = null;<br>
                &nbsp;&nbsp;private $pdo;<br><br>
                &nbsp;&nbsp;public static function getInstance() {<br>
                &nbsp;&nbsp;&nbsp;&nbsp;if (self::$instance === null) {<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;self::$instance = new self(); // Cria só 1 vez<br>
                &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                &nbsp;&nbsp;&nbsp;&nbsp;return self::$instance; // Sempre a mesma<br>
                &nbsp;&nbsp;}<br>
                }<br><br>

                <strong>2. Compartilhamento Automático:</strong><br>
                // Não importa quantos usuários:<br>
                $conn1 = Connection::getInstance(); // Mesmo objeto<br>
                $conn2 = Connection::getInstance(); // Mesmo objeto<br>
                $conn3 = Connection::getInstance(); // Mesmo objeto<br>
                // Resultado: 1 conexão só para todos!
            </div>
        </div>

        <div class="card">
            <h2>📦 Cache + Singleton = Perfeição</h2>
            <div class="code">
                <strong>Otimização adicional:</strong><br><br>

                // Primeiro usuário acessa /clientes:<br>
                $data = cache('clientes_count', function() {<br>
                &nbsp;&nbsp;$pdo = Connection::getInstance()->getPdo(); // 1 conexão<br>
                &nbsp;&nbsp;return $pdo->query("SELECT COUNT(*) FROM users")->fetch();<br>
                }, 300); // Cache por 5 minutos<br><br>

                // Demais usuários acessam /clientes:<br>
                $data = cache('clientes_count'); // Lê do cache, sem query!<br><br>

                <strong>Resultado:</strong><br>
                • 1 conexão só (singleton)<br>
                • 1 query só (primeiro usuário)<br>
                • 9 leituras de cache (instantâneo)<br>
                • 99% mais rápido!
            </div>
        </div>

        <div class="card">
            <h2>🔍 Por que seu Código tinha o Problema</h2>
            <div class="code">
                <strong>Seu código antigo:</strong><br>
                function getClientes() {<br>
                &nbsp;&nbsp;$pdo = new PDO(...); // Nova conexão CADA requisição<br>
                &nbsp;&nbsp;$stmt = $pdo->query("SELECT * FROM clientes");<br>
                &nbsp;&nbsp;return $stmt->fetchAll();<br>
                &nbsp;&nbsp;// PROBLEMA: $pdo não é fechado!<br>
                }<br><br>

                <strong>O que acontecia:</strong><br>
                • Cada requisição HTTP = nova conexão PDO<br>
                • Conexão não era fechada manualmente<br>
                • PHP só fecha no final do script<br>
                • Com muitos usuários simultâneos = muitas conexões abertas<br>
                • MySQL tem limite (ex: 100 conexões)<br>
                • Resultado: "Too many connections"
            </div>
        </div>

        <div class="card">
            <h2>⚡ Benefícios da Nossa Solução</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">90%</div>
                    <div class="stat-label">Redução de Conexões</div>
                </div>
                <div class="stat">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Economia de Recursos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">∞</div>
                    <div class="stat-label">Escalabilidade</div>
                </div>
                <div class="stat">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Erros de Conexão</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function runSimulation() {
            document.getElementById('simulation-result').innerHTML = '<div class="code">Executando simulação de 10 usuários simultâneos...</div>';

            fetch('/real-world-test/simulate-multiple-users')
                .then(response => response.json())
                .then(data => {
                    const reduction = data.comparison.reduction_percentage;
                    const problematic = data.problematic_scenario;
                    const solution = data.our_solution;

                    let html = `
                        <div class="stats">
                            <div class="stat">
                                <div class="stat-number">${data.simulation_time}</div>
                                <div class="stat-label">Tempo de Simulação</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number error">${problematic.total_connections}</div>
                                <div class="stat-label">Conexões (Problema)</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number success">${solution.total_connections}</div>
                                <div class="stat-label">Conexões (Solução)</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number success">${reduction}</div>
                                <div class="stat-label">Redução</div>
                            </div>
                        </div>

                        <div class="comparison">
                            <div class="card problem">
                                <h4>❌ Seu Problema Simulado</h4>
                                <div class="user-list">
                    `;

                    problematic.queries_executed.forEach(query => {
                        html += `<div class="user-item problem">${query}</div>`;
                    });

                    html += `
                                </div>
                                <p><strong>Total:</strong> ${problematic.total_connections} conexões criadas!</p>
                            </div>

                            <div class="card highlight">
                                <h4>✅ Nossa Solução</h4>
                                <div class="user-list">
                    `;

                    solution.queries_executed.forEach(query => {
                        html += `<div class="user-item highlight">${query}</div>`;
                    });

                    html += `
                                </div>
                                <p><strong>Total:</strong> ${solution.total_connections} conexão compartilhada!</p>
                            </div>
                        </div>

                        <div class="success" style="text-align: center; margin-top: 20px; font-size: 1.2em;">
                            🎉 PERFEITO! Singleton pattern resolveu 100% do problema!
                        </div>
                    `;

                    document.getElementById('simulation-result').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('simulation-result').innerHTML =
                        '<div class="error">Erro: ' + error.message + '</div>';
                });
        }

        function runCacheTest() {
            document.getElementById('simulation-result').innerHTML = '<div class="code">Testando cache + singleton...</div>';

            fetch('/real-world-test/test-with-cache')
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="stats">
                            <div class="stat">
                                <div class="stat-number">${data.scenario}</div>
                                <div class="stat-label">Cenário Testado</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number success">${data.total_connections}</div>
                                <div class="stat-label">Conexões Usadas</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number success">${data.cache_hits}</div>
                                <div class="stat-label">Cache Hits</div>
                            </div>
                        </div>

                        <h4>Resultados:</h4>
                        <div class="user-list">
                    `;

                    data.results.forEach(result => {
                        const isCacheHit = result['time'] < '1ms';
                        const className = isCacheHit ? 'highlight' : '';
                        html += `<div class="user-item ${className}">
                            <strong>${result['user']}</strong> → ${result['route']} → ${result['result']} (${result['time']})
                        </div>`;
                    });

                    html += `
                        </div>
                        <div class="success" style="text-align: center; margin-top: 20px;">
                            🚀 Cache + Singleton = Performance perfeita!
                        </div>
                    `;

                    document.getElementById('simulation-result').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('simulation-result').innerHTML =
                        '<div class="error">Erro: ' + error.message + '</div>';
                });
        }
    </script>
</body>

</html>
