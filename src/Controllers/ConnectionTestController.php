<?php

namespace App\Controllers;

use App\Http\Request;

class ConnectionTestController extends BaseController {
    public function index(Request $request) {
        $results = [];
        
        // Teste 1: Verificar se é a mesma conexão
        info('Testando conexões PDO');
        
        $conn1 = \App\Database\Connection::getInstance();
        $conn2 = \App\Database\Connection::getInstance();
        $conn3 = \App\Database\Connection::getInstance();
        
        $results['connection_test'] = [
            'conn1_id' => spl_object_hash($conn1),
            'conn2_id' => spl_object_hash($conn2),
            'conn3_id' => spl_object_hash($conn3),
            'same_connection' => ($conn1 === $conn2 && $conn2 === $conn3)
        ];
        
        // Teste 2: Cache com múltiplas queries
        $startTime = microtime(true);
        
        // Primeira vez: executa query e cache
        $users1 = cache('connection_test_users', function() {
            info('Executando query de usuários (primeira vez)');
            $pdo = \App\Database\Connection::getInstance()->getPdo();
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            return $stmt->fetch()['count'];
        }, 10);
        
        // Segunda vez: lê do cache (sem query)
        $users2 = cache('connection_test_users', function() {
            info('Executando query de usuários (segunda vez - NÃO DEVE EXECUTAR)');
            $pdo = \App\Database\Connection::getInstance()->getPdo();
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            return $stmt->fetch()['count'];
        });
        
        // Terceira vez: lê do cache (sem query)
        $users3 = cache('connection_test_users', function() {
            info('Executando query de usuários (terceira vez - NÃO DEVE EXECUTAR)');
            $pdo = \App\Database\Connection::getInstance()->getPdo();
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            return $stmt->fetch()['count'];
        });
        
        $endTime = microtime(true);
        
        $results['cache_test'] = [
            'users1' => $users1,
            'users2' => $users2,
            'users3' => $users3,
            'all_same' => ($users1 === $users2 && $users2 === $users3),
            'total_time' => ($endTime - $startTime) * 1000 . 'ms'
        ];
        
        // Teste 3: Múltiplas queries sem cache (para comparação)
        $startTimeNoCache = microtime(true);
        
        $queries = [];
        for ($i = 0; $i < 5; $i++) {
            $pdo = \App\Database\Connection::getInstance()->getPdo();
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $queries[] = $stmt->fetch()['count'];
        }
        
        $endTimeNoCache = microtime(true);
        
        $results['no_cache_test'] = [
            'queries' => $queries,
            'all_same' => count(array_unique($queries)) === 1,
            'total_time' => ($endTimeNoCache - $startTimeNoCache) * 1000 . 'ms',
            'time_per_query' => (($endTimeNoCache - $startTimeNoCache) * 1000) / 5 . 'ms'
        ];
        
        // Teste 4: Verificar status da conexão
        $pdo = \App\Database\Connection::getInstance()->getPdo();
        $results['connection_status'] = [
            'connected' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
            'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'driver_name' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'error_mode' => $pdo->getAttribute(PDO::ATTR_ERRMODE)
        ];
        
        // Teste 5: Simular problema de conexões múltiplas (como você tinha)
        $results['simulation'] = $this->simulateConnectionProblem();
        
        return $this->view('connection_test', $results);
    }
    
    private function simulateConnectionProblem() {
        $results = [];
        
        // Simular seu problema antigo
        info('Simulando problema de conexões múltiplas');
        
        $connections = [];
        $connectionIds = [];
        
        // Criar múltiplas conexões (seu problema)
        for ($i = 0; $i < 3; $i++) {
            try {
                $config = config('database'); // ✅ Usa função global config()
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $pdo = new PDO($dsn, $config['username'], $config['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $connections[] = $pdo;
                $connectionIds[] = spl_object_hash($pdo);
                
                info("Conexão criada manualmente: " . spl_object_hash($pdo));
                
            } catch (Exception $e) {
                $results['error'] = $e->getMessage();
                break;
            }
        }
        
        $results['manual_connections'] = [
            'count' => count($connections),
            'connection_ids' => $connectionIds,
            'unique_connections' => count(array_unique($connectionIds))
        ];
        
        // Fechar conexões manuais
        foreach ($connections as $pdo) {
            $pdo = null; // Fecha a conexão
        }
        
        // Comparar com singleton
        $singletonConnections = [];
        $singletonIds = [];
        
        for ($i = 0; $i < 3; $i++) {
            $conn = \App\Database\Connection::getInstance();
            $singletonConnections[] = $conn;
            $singletonIds[] = spl_object_hash($conn);
        }
        
        $results['singleton_connections'] = [
            'count' => count($singletonConnections),
            'connection_ids' => $singletonIds,
            'unique_connections' => count(array_unique($singletonIds))
        ];
        
        return $results;
    }
    
    public function stressTest() {
        info('Iniciando stress test de conexões');
        
        $results = [];
        $startTime = microtime(true);
        
        // 50 chamadas ao cache (deve usar 1 conexão só)
        for ($i = 0; $i < 50; $i++) {
            $data = cache('stress_test_' . $i, function() use ($i) {
                $pdo = \App\Database\Connection::getInstance()->getPdo();
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                return [
                    'iteration' => $i,
                    'count' => $stmt->fetch()['count'],
                    'connection_id' => spl_object_hash(\App\Database\Connection::getInstance())
                ];
            }, 5);
            
            $results[] = $data;
        }
        
        $endTime = microtime(true);
        
        $uniqueConnections = array_unique(array_column($results, 'connection_id'));
        
        return $this->json([
            'total_iterations' => 50,
            'unique_connections' => count($uniqueConnections),
            'total_time' => ($endTime - $startTime) * 1000 . 'ms',
            'avg_time_per_call' => (($endTime - $startTime) * 1000) / 50 . 'ms',
            'connections_used' => $uniqueConnections,
            'success' => count($uniqueConnections) === 1 // Deve ser 1 só!
        ]);
    }
}
