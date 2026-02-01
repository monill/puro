<?php

namespace App\Controllers;

use App\Http\Request;

class EnvTestController extends BaseController {
    public function index(Request $request) {
        // Testar todas as variáveis do .env
        $envVars = [
            'APP_NAME' => env('APP_NAME'),
            'APP_VERSION' => env('APP_VERSION'),
            'APP_ENV' => env('APP_ENV'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'APP_URL' => env('APP_URL'),
            'DB_HOST' => env('DB_HOST'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'DB_PASSWORD' => env('DB_PASSWORD', '(oculto)'),
            'CACHE_DRIVER' => env('CACHE_DRIVER'),
            'SESSION_DRIVER' => env('SESSION_DRIVER'),
            'LOG_LEVEL' => env('LOG_LEVEL'),
            'APP_LOCALE' => env('APP_LOCALE'),
            'GAME_SPEED' => env('GAME_SPEED'),
            'INITIAL_WOOD' => env('INITIAL_WOOD'),
            'MAINTENANCE_MODE' => env('MAINTENANCE_MODE'),
        ];
        
        // Testar tipos de dados
        $typeTests = [
            'boolean_true' => env('APP_DEBUG'),
            'boolean_false' => env('MAINTENANCE_MODE'),
            'integer' => env('GAME_SPEED'),
            'string' => env('APP_NAME'),
            'null' => env('NON_EXISTENT_VAR', 'default_value'),
        ];
        
        // Verificar se arquivo .env existe
        $envFileExists = file_exists(base_path('.env'));
        $envExampleExists = file_exists(base_path('.env.example'));
        
        return $this->view('env_test', [
            'envVars' => $envVars,
            'typeTests' => $typeTests,
            'envFileExists' => $envFileExists,
            'envExampleExists' => $envExampleExists,
            'envFilePath' => base_path('.env'),
            'envExamplePath' => base_path('.env.example')
        ]);
    }
    
    public function reload() {
        // Forçar reload do .env
        $configHelper = new \App\Helpers\ConfigHelper();
        
        // Limpar cache de ambiente (se existir)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        return $this->json([
            'message' => 'Arquivo .env recarregado com sucesso!',
            'timestamp' => date('Y-m-d H:i:s'),
            'env_vars_count' => count($_ENV)
        ]);
    }
    
    public function testDatabaseConnection() {
        try {
            $config = [
                'host' => env('DB_HOST'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => env('DB_CHARSET', 'utf8mb4')
            ];
            
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new \PDO($dsn, $config['username'], $config['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Testar query
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            
            return $this->json([
                'success' => true,
                'message' => 'Conexão com banco de dados bem-sucedida!',
                'mysql_version' => $result['version'],
                'config' => [
                    'host' => $config['host'],
                    'database' => $config['database'],
                    'username' => $config['username'],
                    'charset' => $config['charset']
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro na conexão com banco de dados',
                'error' => $e->getMessage(),
                'config' => [
                    'host' => env('DB_HOST'),
                    'database' => env('DB_DATABASE'),
                    'username' => env('DB_USERNAME'),
                    'charset' => env('DB_CHARSET', 'utf8mb4')
                ]
            ], 500);
        }
    }
    
    public function updateVar(Request $request) {
        $key = $request->post('key');
        $value = $request->post('value');
        
        if (!$key || !$value) {
            return $this->json(['error' => 'Chave e valor são obrigatórios'], 400);
        }
        
        try {
            $envFile = base_path('.env');
            $content = file_get_contents($envFile);
            
            // Procurar a linha com a chave
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $content)) {
                // Atualizar linha existente
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                // Adicionar nova linha
                $content .= "\n{$key}={$value}\n";
            }
            
            file_put_contents($envFile, $content);
            
            return $this->json([
                'success' => true,
                'message' => "Variável {$key} atualizada com sucesso!",
                'key' => $key,
                'value' => $value
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao atualizar variável',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
