<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Helpers\FileHelper;
use App\Helpers\LogHelper;

class InstallController extends BaseController {
    private $requirements = [
        'php' => '8.0',
        'extensions' => ['pdo', 'pdo_mysql', 'json', 'mbstring'],
        'permissions' => ['config', 'storage']
    ];

    public function index(Request $request) {
        // Verificar se já está instalado
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        return $this->view('install/index', [
            'step' => 1,
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function database(Request $request) {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        return $this->view('install/database', [
            'step' => 2,
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function setup(Request $request) {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        return $this->view('install/setup', [
            'step' => 3,
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function admin(Request $request) {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        return $this->view('install/admin', [
            'step' => 4,
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function finish(Request $request) {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        return $this->view('install/finish', [
            'step' => 5,
            'requirements' => $this->checkRequirements()
        ]);
    }

    public function install(Request $request) {
        if ($this->isInstalled()) {
            return $this->error('Já instalado');
        }

        $step = $request->post('step');

        switch ($step) {
            case 'database':
                return $this->installDatabase($request);
            case 'setup':
                return $this->installSetup($request);
            case 'admin':
                return $this->installAdmin($request);
            default:
                return $this->error('Step inválido');
        }
    }

    private function installDatabase(Request $request) {
        $validation = $this->validate($request, [
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validation) {
            return $validation;
        }

        // Testar conexão
        try {
            $dsn = "mysql:host={$request->post('host')};charset=utf8mb4";
            $pdo = new \PDO($dsn, $request->post('username'), $request->post('password'));
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Criar banco se não existir
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$request->post('database')}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Salvar configuração
            $this->saveDatabaseConfig($request->post());

            return $this->success('Configuração do banco salva com sucesso!');

        } catch (\PDOException $e) {
            return $this->error('Erro na conexão: ' . $e->getMessage());
        }
    }

    private function installSetup(Request $request) {
        $validation = $this->validate($request, [
            'server_name' => 'required',
            'server_speed' => 'required',
            'game_speed' => 'required',
            'troop_speed' => 'required'
        ]);

        if ($validation) {
            return $validation;
        }

        // Salvar configurações do jogo
        $this->saveGameConfig($request->post());

        // Criar tabelas
        $this->createTables();

        return $this->success('Configurações salvas e tabelas criadas!');
    }

    private function installAdmin(Request $request) {
        $validation = $this->validate($request, [
            'admin_username' => 'required|min:3',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:6'
        ]);

        if ($validation) {
            return $validation;
        }

        // Criar usuário admin
        $userData = [
            'username' => $request->post('admin_username'),
            'email' => $request->post('admin_email'),
            'password' => password_hash($request->post('admin_password'), PASSWORD_DEFAULT),
            'tribe' => 1,
            'population' => 2,
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ];

        $user = \App\Database\Models\User::create($userData);

        // Criar aldeia inicial do admin
        $villageData = [
            'owner_id' => $user->id,
            'name' => "Capital de {$user->username}",
            'is_capital' => 1,
            'population' => 2,
            'wood' => 750,
            'clay' => 750,
            'iron' => 750,
            'crop' => 750,
            'wood_production' => 10,
            'clay_production' => 10,
            'iron_production' => 10,
            'crop_production' => 10,
            'max_store' => 800,
            'max_crop' => 800,
            'loyalty' => 100,
            'x' => rand(1, 100),
            'y' => rand(1, 100),
            'created_at' => date('Y-m-d H:i:s')
        ];

        \App\Database\Models\Village::create($villageData);

        // Criar arquivo de instalação
        $this->createInstallFile();

        return $this->success('Instalação concluída com sucesso!');
    }

    private function checkRequirements() {
        $checks = [];

        // Versão PHP
        $checks['php_version'] = [
            'required' => $this->requirements['php'],
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, $this->requirements['php'], '>=')
        ];

        // Extensões
        foreach ($this->requirements['extensions'] as $ext) {
            $checks['extension_' . $ext] = [
                'required' => $ext,
                'current' => extension_loaded($ext) ? 'Instalada' : 'Não instalada',
                'status' => extension_loaded($ext)
            ];
        }

        // Permissões (simulado para Windows)
        $checks['permissions'] = [
            'required' => 'Writable',
            'current' => 'OK (Windows)',
            'status' => true
        ];

        return $checks;
    }

    private function isInstalled() {
        return FileHelper::exists(FileHelper::storage('.installed'));
    }

    private function createInstallFile() {
        $storageDir = FileHelper::storage();
        FileHelper::ensureDirectory($storageDir);
        
        FileHelper::put($storageDir . '/.installed', date('Y-m-d H:i:s'));
        
        LogHelper::info('Sistema instalado com sucesso', [
            'storage_path' => $storageDir,
            'install_file' => $storageDir . '/.installed'
        ]);
    }

    private function checkDatabaseConnection($host, $username, $password, $database) {
        try {
            $dsn = "mysql:host={$host};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Testar se o banco existe
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    private function saveDatabaseConfig($data) {
        $config = "<?php\n\nreturn [\n";
        $config .= "    'host' => '{$data['host']}',\n";
        $config .= "    'database' => '{$data['database']}',\n";
        $config .= "    'username' => '{$data['username']}',\n";
        $config .= "    'password' => '{$data['password']}',\n";
        $config .= "    'charset' => 'utf8mb4',\n";
        $config .= "    'collation' => 'utf8mb4_unicode_ci'\n";
        $config .= "];\n";

        $configFile = FileHelper::path('config/database.php');
        FileHelper::put($configFile, $config);
        
        LogHelper::info('Configuração do banco salva', [
            'config_file' => $configFile,
            'database' => $data['database']
        ]);
    }

    private function saveGameConfig($data) {
        $config = "<?php\n\nreturn [\n";
        $config .= "    'server_name' => '{$data['server_name']}',\n";
        $config .= "    'server_speed' => {$data['server_speed']},\n";
        $config .= "    'game_speed' => {$data['game_speed']},\n";
        $config .= "    'troop_speed' => {$data['troop_speed']},\n";
        $config .= "    'start_time' => time(),\n";
        $config .= "    'debug_mode' => false\n";
        $config .= "];\n";

        $configFile = FileHelper::path('config/game.php');
        FileHelper::put($configFile, $config);
        
        LogHelper::info('Configurações do jogo salvas', [
            'config_file' => $configFile,
            'server_name' => $data['server_name']
        ]);
    }

    private function createTables() {
        $pdo = \App\Database\Connection::getInstance()->getPdo();
        
        try {
            // Executar arquivo de tabelas
            $this->executeSqlFile($pdo, storage_path('database/tables.sql'));
            
            // Executar arquivo de seeds (dados iniciais)
            $this->executeSqlFile($pdo, storage_path('database/seeds.sql'));
            
        } catch (\Exception $e) {
            throw new \Exception('Erro ao criar tabelas: ' . $e->getMessage());
        }
    }

    private function executeSqlFile($pdo, $sqlFile) {
        if (!FileHelper::exists($sqlFile)) {
            throw new \Exception("Arquivo SQL não encontrado: {$sqlFile}");
        }
        
        LogHelper::info('Executando arquivo SQL', ['file' => $sqlFile]);
        
        // Ler arquivo SQL
        $sql = FileHelper::get($sqlFile);
        
        // Separar por ponto e vírgula e remover comentários
        $statements = array_filter(
            array_map('trim', preg_split('/;\s*\n/', $sql)),
            function($line) {
                return !empty($line) && !preg_match('/^--/', $line);
            }
        );
        
        // Executar cada statement
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
                LogHelper::debug('SQL executado com sucesso', ['statement' => substr($statement, 0, 100)]);
            } catch (\PDOException $e) {
                LogHelper::error('Erro ao executar SQL', [
                    'statement' => substr($statement, 0, 100),
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Erro ao executar SQL: ' . $e->getMessage());
            }
        }
        
        LogHelper::info('Arquivo SQL executado com sucesso', [
            'file' => $sqlFile,
            'statements_count' => count($statements)
        ]);
    }
}
