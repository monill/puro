<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database\Migration;
use App\Core\Database\Seeder;
use App\Core\File;

class Cli
{
    private static array $commands = [];
    private static array $config = [
        'app_name' => 'Minimal PHP Framework',
        'version' => '1.0.0',
    ];

    public static function register(string $name, callable $handler, string $description = ''): void
    {
        self::$commands[$name] = [
            'handler' => $handler,
            'description' => $description,
        ];
    }

    public static function run(array $argv): void
    {
        if (count($argv) < 2) {
            self::showHelp();
            return;
        }

        $command = $argv[1];
        $args = array_slice($argv, 2);

        if ($command === 'list') {
            self::listCommands();
            return;
        }

        if ($command === 'help') {
            self::showHelp(isset($args[0]) ? $args[0] : null);
            return;
        }

        if (!isset(self::$commands[$command])) {
            echo "Unknown command: {$command}\n";
            echo "Run 'php cli help' for available commands.\n";
            return;
        }

        $startTime = microtime(true);
        
        try {
            self::$commands[$command]['handler']($args);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo "\n✅ Command completed in {$duration}ms\n";
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            exit(1);
        }
    }

    private static function showHelp(?string $command = null): void
    {
        if ($command) {
            if (!isset(self::$commands[$command])) {
                echo "Unknown command: {$command}\n";
                return;
            }

            echo "Command: {$command}\n";
            echo "Description: " . self::$commands[$command]['description'] . "\n";
            echo "Usage: php cli {$command} [options]\n";
            return;
        }

        echo self::getHeader();
        echo "\nUsage:\n";
        echo "  php cli <command> [options]\n\n";
        echo "Available commands:\n";

        foreach (self::$commands as $name => $cmd) {
            $description = $cmd['description'] ?: 'No description available';
            echo "  {$name}\t{$description}\n";
        }

        echo "\nType 'php cli help <command>' for more information about a specific command.\n";
    }

    private static function listCommands(): void
    {
        echo "Available commands:\n";
        
        foreach (self::$commands as $name => $cmd) {
            $description = $cmd['description'] ?: 'No description available';
            echo "  {$name}\t{$description}\n";
        }
    }

    private static function getHeader(): string
    {
        return <<<HEADER
{$config['app_name']} v{$config['version']}
Minimal PHP Framework CLI Tool

HEADER;
    }

    public static function init(): void
    {
        echo "Initializing {$config['app_name']}...\n";

        // Create directories
        $directories = [
            'app/Core',
            'app/Controllers',
            'app/Models',
            'app/Middlewares',
            'public',
            'templates',
            'templates/errors',
            'templates/partials',
            'routes',
            'storage/cache',
            'storage/logs',
            'storage/uploads',
            'lang/en-us',
            'lang/pt-br',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "  Created directory: {$dir}\n";
            }
        }

        // Create config file
        $configFile = 'config.php';
        if (!file_exists($configFile)) {
            $configContent = self::generateConfigFile();
            File::put($configFile, $configContent);
            echo "  Created config file: {$configFile}\n";
        }

        // Create .htaccess
        $htaccessFile = 'public/.htaccess';
        if (!file_exists($htaccessFile)) {
            $htaccessContent = self::generateHtaccessFile();
            File::put($htaccessFile, $htaccessContent);
            echo "  Created .htaccess file: {$htaccessFile}\n";
        }

        // Create database schema
        $schemaFile = 'storage/schema.sql';
        if (!file_exists($schemaFile)) {
            $schemaContent = self::generateSchemaFile();
            File::put($schemaFile, $schemaContent);
            echo "  Created database schema: {$schemaFile}\n";
        }

        echo "\n✅ Initialization complete!\n";
        echo "Next steps:\n";
        echo "  1. Configure your database in config.php\n";
        echo "  2. Run 'php cli migrate' to create tables\n";
        echo "  3. Run 'php cli seed' to populate data\n";
        echo "  4. Start your development server\n";
    }

    private static function generateConfigFile(): string
    {
        return '<?php' . PHP_EOL . <<<CONFIG
return [
    // Database Configuration
    'db' => [
        'host' => 'localhost',
        'name' => 'minimal_framework',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
        'timezone' => '-03:00',
    ],

    // Application Configuration
    'app' => [
        'name' => 'Minimal PHP Framework',
        'url' => 'http://localhost',
        'lang' => 'pt-br',
        'timezone' => 'America/Sao_Paulo',
        'debug' => true,
    ],

    // Mail Configuration
    'mail' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'user' => '',
        'pass' => '',
        'from_email' => 'noreply@example.com',
        'from_name' => 'Minimal Framework',
    ],

    // Authentication Configuration
    'auth' => [
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
                'model' => 'App\\Models\\User',
            ],
            'customer' => [
                'driver' => 'session',
                'provider' => 'customers',
                'model' => 'App\\Models\\Customer',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'database',
                'table' => 'users',
            ],
            'customers' => [
                'driver' => 'database',
                'table' => 'customers',
            ],
        ],
    ],

    // Cache Configuration
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/storage/cache',
            ],
            'session' => [
                'driver' => 'session',
            ],
            'memory' => [
                'driver' => 'memory',
            ],
        ],
    ],

    // Session Configuration
    'session' => [
        'driver' => 'file',
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    // Upload Configuration
    'upload' => [
        'path' => __DIR__ . '/storage/uploads',
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        'max_file_size' => 10485760, // 10MB
        'image_quality' => 85,
    ],
];
CONFIG;
    }

    private static function generateHtaccessFile(): string
    {
        return <<<HTACCESS
RewriteEngine On

# Handle preflight requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ index.php [QSA,L]

# Redirect to index.php if file/directory doesn't exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Hide .htaccess file
<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>

# Disable directory listing
Options -Indexes

# Set default charset
AddDefaultCharset UTF-8

# PHP settings
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log ../storage/logs/php_errors.log
    php_value max_execution_time 30
    php_value memory_limit 128M
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>
HTACCESS;
    }

    private static function generateSchemaFile(): string
    {
        return <<<SQL
-- Database Schema for Minimal PHP Framework
-- Generated on: date('Y-m-d H:i:s')

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    order_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrations table
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_migration (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    }

    public static function migrate(): void
    {
        echo "Running database migrations...\n";

        $migrationsPath = __DIR__ . '/../../database/migrations';
        if (!is_dir($migrationsPath)) {
            mkdir($migrationsPath, 0755, true);
        }

        $results = Migration::runMigrations($migrationsPath);

        if (!empty($results['executed'])) {
            echo "✅ Executed migrations:\n";
            foreach ($results['executed'] as $migration) {
                echo "  - {$migration}\n";
            }
        }

        if (!empty($results['failed'])) {
            echo "❌ Failed migrations:\n";
            foreach ($results['failed'] as $migration) {
                echo "  - {$migration['seeder']}: {$migration['error']}\n";
            }
        }

        echo "\nMigration completed!\n";
    }

    public static function rollback(): void
    {
        echo "Rolling back last migration...\n";

        $migrationsPath = __DIR__ . '/../../database/migrations';
        
        if (Migration::rollbackLastMigration($migrationsPath)) {
            echo "✅ Rollback successful\n";
        } else {
            echo "❌ No migrations to rollback\n";
        }
    }

    public static function seed(): void
    {
        echo "Running database seeders...\n";

        $seedersPath = __DIR__ . '/../../database/seeders';
        if (!is_dir($seedersPath)) {
            mkdir($seedersPath, 0755, true);
        }

        $results = Seeder::runAll($seedersPath);

        if (!empty($results['executed'])) {
            echo "✅ Executed seeders:\n";
            foreach ($results['executed'] as $seeder) {
                echo "  - {$seeder}\n";
            }
        }

        if (!empty($results['failed'])) {
            echo "❌ Failed seeders:\n";
            foreach ($results['failed'] as $seeder) {
                echo "  - {$seeder['seeder']}: {$seeder['error']}\n";
            }
        }

        echo "\nSeeding completed!\n";
    }

    public static function serve(string $host = 'localhost', int $port = 8000): void
    {
        echo "Starting development server...\n";
        echo "Server running at http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop\n\n";

        $command = "php -S {$host}:{$port} -t public";
        passthru($command);
    }

    public static function cacheClear(): void
    {
        echo "Clearing cache...\n";

        $cachePath = __DIR__ . '/../../storage/cache';
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    echo "  Deleted: " . basename($file) . "\n";
                }
            }
        }

        echo "✅ Cache cleared!\n";
    }

    public static function logsClear(): void
    {
        echo "Clearing logs...\n";

        $logsPath = __DIR__ . '/../../storage/logs';
        if (is_dir($logsPath)) {
            $files = glob($logsPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    echo "  Deleted: " . basename($file) . "\n";
                }
            }
        }

        echo "✅ Logs cleared!\n";
    }

    public static function optimize(): void
    {
        echo "Optimizing application...\n";

        // Clear cache
        self::cacheClear();

        // Clear logs
        self::logsClear();

        // Optimize autoloader
        echo "  Optimizing autoloader...\n";

        echo "✅ Optimization completed!\n";
    }

    public static function version(): void
    {
        echo self::getHeader();
        echo "\nPHP Version: " . PHP_VERSION . "\n";
        echo "Framework Version: " . self::$config['version'] . "\n";
        echo "Environment: " . (getenv('APP_ENV') ?: 'development') . "\n";
        echo "Debug Mode: " . (getenv('APP_DEBUG') ? 'true' : 'false') . "\n";
    }

    public static function make(string $type, string $name): void
    {
        echo "Creating {$type}: {$name}\n";

        switch ($type) {
            case 'controller':
                self::makeController($name);
                break;
            case 'model':
                self::makeModel($name);
                break;
            case 'middleware':
                self::makeMiddleware($name);
                break;
            case 'migration':
                self::makeMigration($name);
                break;
            case 'seeder':
                self::makeSeeder($name);
                break;
            default:
                echo "Unknown type: {$type}\n";
                return;
        }

        echo "✅ {$type} created successfully!\n";
    }

    private static function makeController(string $name): void
    {
        $className = ucfirst($name) . 'Controller';
        $filePath = __DIR__ . '/../../app/Controllers/' . $className . '.php';

        $content = '<?php' . PHP_EOL . <<<CONTROLLER
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class {$className}
{
    public function index(Request $request): Response
    {
        return new Response('{$className} index method');
    }

    public function show(Request $request, int \$id): Response
    {
        return new Response("{$className} show method - ID: {\$id}");
    }

    public function create(Request $request): Response
    {
        return new Response('{$className} create method');
    }

    public function store(Request $request): Response
    {
        return new Response('{$className} store method');
    }

    public function edit(Request $request, int \$id): Response
    {
        return new Response("{$className} edit method - ID: {\$id}");
    }

    public function update(Request $request, int \$id): Response
    {
        return new Response("{$className} update method - ID: {\$id}");
    }

    public function destroy(Request $request, int \$id): Response
    {
        return new Response("{$className} destroy method - ID: {\$id}");
    }
}
CONTROLLER;

        File::put($filePath, $content);
    }

    private static function makeModel(string $name): void
    {
        $className = ucfirst($name);
        $filePath = __DIR__ . '/../../app/Models/' . $className . '.php';

        $content = '<?php' . PHP_EOL . <<<MODEL
declare(strict_types=1);

namespace App\Models;

use App\Core\Database\QueryBuilder;

class {$className}
{
    protected static \$table = '{$name}s';
    
    public static function find(int \$id): ?self
    {
        \$query = new QueryBuilder();
        \$result = \$query->table(static::\$table)->where('id', \$id)->first();
        
        return \$result ? new self(\$result) : null;
    }
    
    public static function all(): array
    {
        \$query = new QueryBuilder();
        \$results = \$query->table(static::\$table)->get();
        
        return array_map(fn(\$item) => new self(\$item), \$results);
    }
    
    public static function create(array \$data): self
    {
        \$query = new QueryBuilder();
        \$id = \$query->table(static::\$table)->insertGetId(\$data);
        
        return self::find(\$id);
    }
    
    public function update(array \$data): bool
    {
        \$query = new QueryBuilder();
        return \$query->table(static::\$table)->where('id', \$this->id)->update(\$data) > 0;
    }
    
    public function delete(): bool
    {
        \$query = new QueryBuilder();
        return \$query->table(static::\$table)->where('id', \$this->id)->delete() > 0;
    }
    
    public function toArray(): array
    {
        return get_object_vars(\$this);
    }
}
MODEL;

        File::put($filePath, $content);
    }

    private static function makeMiddleware(string $name): void
    {
        $className = ucfirst($name) . 'Middleware';
        $filePath = __DIR__ . '/../../app/Middlewares/' . $className . '.php';

        $content = '<?php' . PHP_EOL . <<<MIDDLEWARE
declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class {$className} extends Middleware
{
    public function handle(Request \$request, callable \$next): mixed
    {
        // Add your middleware logic here
        
        return \$next(\$request);
    }
}
MIDDLEWARE;

        File::put($filePath, $content);
    }

    private static function makeMigration(string $name): void
    {
        $className = ucfirst($name);
        $filePath = __DIR__ . '/../../database/migrations/' . date('Y_m_d_His') . '_' . $className . '.php';

        $content = '<?php' . PHP_EOL . <<<MIGRATION
declare(strict_types=1);

use App\Core\Database\Migration;
use App\Core\Database\Schema;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->createTable('{$name}s', function (Schema \$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        \$this->dropTable('{$name}s');
    }
}
MIGRATION;

        File::put($filePath, $content);
    }

    private static function makeSeeder(string $name): void
    {
        $className = ucfirst($name) . 'Seeder';
        $filePath = __DIR__ . '/../../database/seeders/' . $className . '.php';

        $content = '<?php' . PHP_EOL . <<<SEEDER
declare(strict_types=1);

use App\Core\Database\Seeder;

class {$className} extends Seeder
{
    public function run(): void
    {
        // Add your seeding logic here
    }
}
SEEDER;

        File::put($filePath, $content);
    }

    public static function registerDefaultCommands(): void
    {
        self::register('init', [self::class, 'init'], 'Initialize the framework');
        self::register('migrate', [self::class, 'migrate'], 'Run database migrations');
        self::register('rollback', [self::class, 'rollback'], 'Rollback last migration');
        self::register('seed', [self::class, 'seed'], 'Run database seeders');
        self::register('serve', [self::class, 'serve'], 'Start development server');
        self::register('cache:clear', [self::class, 'cacheClear'], 'Clear application cache');
        self::register('logs:clear', [self::class, 'logsClear'], 'Clear application logs');
        self::register('optimize', [self::class, 'optimize'], 'Optimize application');
        self::register('version', [self::class, 'version'], 'Show version information');
        self::register('make', [self::class, 'make'], 'Create new file (controller|model|middleware|migration|seeder)');
    }
}
