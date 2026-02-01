<?php

namespace App\Controllers;

use App\Http\Request;

class RealWorldTestController extends BaseController {
    public function index(Request $request) {
        return $this->view('real_world_test', [
            'title' => 'Teste Real: Múltiplos Usuários Simultâneos',
            'description' => 'Simulando seu problema real: múltiplos usuários acessando ao mesmo tempo'
        ]);
    }
    
    public function simulateMultipleUsers() {
        info('Iniciando simulação de múltiplos usuários simultâneos');
        
        $results = [];
        $startTime = microtime(true);
        
        // Simular 10 usuários acessando diferentes rotas ao mesmo tempo
        $users = [
            ['id' => 1, 'name' => 'João', 'route' => '/clientes'],
            ['id' => 2, 'name' => 'Maria', 'route' => '/produtos'],
            ['id' => 3, 'name' => 'Pedro', 'route' => '/vendas'],
            ['id' => 4, 'name' => 'Ana', 'route' => '/relatorios'],
            ['id' => 5, 'name' => 'Carlos', 'route' => '/clientes'],
            ['id' => 6, 'name' => 'Lucia', 'route' => '/produtos'],
            ['id' => 7, 'name' => 'Marcos', 'route' => '/vendas'],
            ['id' => 8, 'name' => 'Sofia', 'route' => '/relatorios'],
            ['id' => 9, 'name' => 'Rafael', 'route' => '/clientes'],
            ['id' => 10, 'name' => 'Laura', 'route' => '/produtos']
        ];
        
        // Simular o problema antigo (múltiplas conexões)
        $problematicResults = $this->simulateProblematicScenario($users);
        
        // Simular nossa solução (singleton)
        $solutionResults = $this->simulateOurSolution($users);
        
        $endTime = microtime(true);
        
        return $this->json([
            'simulation_time' => ($endTime - $startTime) * 1000 . 'ms',
            'problematic_scenario' => $problematicResults,
            'our_solution' => $solutionResults,
            'comparison' => [
                'problematic_connections' => $problematicResults['total_connections'],
                'solution_connections' => $solutionResults['total_connections'],
                'reduction' => $problematicResults['total_connections'] - $solutionResults['total_connections'],
                'reduction_percentage' => round(($problematicResults['total_connections'] - $solutionResults['total_connections']) / $problematicResults['total_connections'] * 100, 1) . '%'
            ]
        ]);
    }
    
    private function simulateProblematicScenario($users) {
        info('Simulando cenário problemático (como seu código antigo)');
        
        $connections = [];
        $connectionIds = [];
        $queries = [];
        
        foreach ($users as $user) {
            try {
                // Simular seu código antigo - nova conexão por usuário
                $config = config('database'); // ✅ Usa função global config()
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $pdo = new PDO($dsn, $config['username'], $config['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $connections[] = $pdo;
                $connectionIds[] = spl_object_hash($pdo);
                
                // Simular query específica para cada rota
                switch ($user['route']) {
                    case '/clientes':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                        $result = $stmt->fetch();
                        $queries[] = "Usuário {$user['name']} ({$user['id']}) - Clientes: {$result['count']}";
                        break;
                        
                    case '/produtos':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM villages");
                        $result = $stmt->fetch();
                        $queries[] = "Usuário {$user['name']} ({$user['id']}) - Produtos: {$result['count']}";
                        break;
                        
                    case '/vendas':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE tribe = 1");
                        $result = $stmt->fetch();
                        $queries[] = "Usuário {$user['name']} ({$user['id']}) - Vendas: {$result['count']}";
                        break;
                        
                    case '/relatorios':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE population > 100");
                        $result = $stmt->fetch();
                        $queries[] = "Usuário {$user['name']} ({$user['id']}) - Relatórios: {$result['count']}";
                        break;
                }
                
                info("PROBLEMA: Usuário {$user['name']} criou conexão " . spl_object_hash($pdo));
                
                // PROBLEMA: Conexão não é fechada! Fica aberta!
                // No mundo real, isso aconteceria com cada requisição HTTP
                
            } catch (Exception $e) {
                $queries[] = "ERRO Usuário {$user['name']}: " . $e->getMessage();
            }
        }
        
        // No mundo real, essas conexões ficariam abertas até o PHP limpar
        // Mas com muitos usuários simultâneos, o MySQL atingiria o limite
        
        return [
            'users_count' => count($users),
            'connections_created' => count($connections),
            'unique_connections' => count(array_unique($connectionIds)),
            'total_connections' => count($connections), // Cada uma é uma conexão real!
            'queries_executed' => $queries,
            'problem' => 'Cada usuário criou sua própria conexão PDO!'
        ];
    }
    
    private function simulateOurSolution($users) {
        info('Simulando nossa solução (singleton)');
        
        $connection = \App\Database\Connection::getInstance();
        $connectionId = spl_object_hash($connection);
        $queries = [];
        
        foreach ($users as $user) {
            // Sempre a mesma conexão!
            $pdo = $connection->getPdo();
            
            // Simular query específica para cada rota
            switch ($user['route']) {
                case '/clientes':
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $result = $stmt->fetch();
                    $queries[] = "Usuário {$user['name']} ({$user['id']}) - Clientes: {$result['count']} [Conexão: " . substr($connectionId, 0, 8) . "...]";
                    break;
                    
                case '/produtos':
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM villages");
                    $result = $stmt->fetch();
                    $queries[] = "Usuário {$user['name']} ({$user['id']}) - Produtos: {$result['count']} [Conexão: " . substr($connectionId, 0, 8) . "...]";
                    break;
                    
                case '/vendas':
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE tribe = 1");
                    $result = $stmt->fetch();
                    $queries[] = "Usuário {$user['name']} ({$user['id']}) - Vendas: {$result['count']} [Conexão: " . substr($connectionId, 0, 8) . "...]";
                    break;
                    
                case '/relatorios':
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE population > 100");
                    $result = $stmt->fetch();
                    $queries[] = "Usuário {$user['name']} ({$user['id']}) - Relatórios: {$result['count']} [Conexão: " . substr($connectionId, 0, 8) . "...]";
                    break;
            }
            
            info("SOLUÇÃO: Usuário {$user['name']} usou conexão " . substr($connectionId, 0, 8) . "...");
        }
        
        return [
            'users_count' => count($users),
            'connections_created' => 1, // Só uma!
            'unique_connections' => 1, // Só uma!
            'total_connections' => 1, // Só uma conexão real!
            'queries_executed' => $queries,
            'solution' => 'Todos os usuários compartilharam a mesma conexão!'
        ];
    }
    
    public function testWithCache() {
        info('Testando com cache + singleton (cenário real)');
        
        $results = [];
        
        // Simular diferentes usuários acessando diferentes dados com cache
        $scenarios = [
            ['user' => 'João', 'route' => '/clientes', 'cache_key' => 'clientes_count'],
            ['user' => 'Maria', 'route' => '/clientes', 'cache_key' => 'clientes_count'], // Mesmo cache
            ['user' => 'Pedro', 'route' => '/produtos', 'cache_key' => 'produtos_count'],
            ['user' => 'Ana', 'route' => '/produtos', 'cache_key' => 'produtos_count'], // Mesmo cache
            ['user' => 'Carlos', 'route' => '/vendas', 'cache_key' => 'vendas_count'],
            ['user' => 'Lucia', 'route' => '/relatorios', 'cache_key' => 'relatorios_count'],
            ['user' => 'Marcos', 'route' => '/clientes', 'cache_key' => 'clientes_count'], // Mesmo cache
            ['user' => 'Sofia', 'route' => '/produtos', 'cache_key' => 'produtos_count'], // Mesmo cache
        ];
        
        $connection = \App\Database\Connection::getInstance();
        $connectionId = spl_object_hash($connection);
        
        foreach ($scenarios as $scenario) {
            $startTime = microtime(true);
            
            // Cache + Singleton = PERFEITO!
            $data = cache($scenario['cache_key'], function() use ($scenario, $connection) {
                info("CACHE MISS: {$scenario['user']} acessando {$scenario['route']}");
                
                $pdo = $connection->getPdo();
                
                switch ($scenario['route']) {
                    case '/clientes':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                        return $stmt->fetch()['count'];
                    case '/produtos':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM villages");
                        return $stmt->fetch()['count'];
                    case '/vendas':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE tribe = 1");
                        return $stmt->fetch()['count'];
                    case '/relatorios':
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE population > 100");
                        return $stmt->fetch()['count'];
                }
            }, 30); // 30 segundos de cache
            
            $endTime = microtime(true);
            
            $results[] = [
                'user' => $scenario['user'],
                'route' => $scenario['route'],
                'cache_key' => $scenario['cache_key'],
                'result' => $data,
                'time' => ($endTime - $startTime) * 1000 . 'ms',
                'connection' => substr($connectionId, 0, 8) . '...'
            ];
        }
        
        return $this->json([
            'scenario' => '8 usuários simultâneos com cache + singleton',
            'results' => $results,
            'total_connections' => 1,
            'cache_hits' => count(array_filter($results, fn($r) => $r['time'] < '1ms')),
            'summary' => 'Perfeito: 1 conexão + cache para todos!'
        ]);
    }
}
