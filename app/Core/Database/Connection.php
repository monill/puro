<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOException;
use App\Core\Log;

class Connection
{
    private static array $connections = [];
    private static array $config = [];
    private static ?PDO $defaultConnection = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(string $name = 'default'): PDO
    {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        $config = self::$config[$name] ?? self::$config;
        
        if (!$config) {
            throw new \InvalidArgumentException("Database configuration not found for connection: {$name}");
        }

        $dsn = self::buildDsn($config);
        $username = $config['user'] ?? '';
        $password = $config['pass'] ?? '';
        $options = $config['options'] ?? self::getDefaultOptions();

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            self::$connections[$name] = $pdo;
            
            if ($name === 'default') {
                self::$defaultConnection = $pdo;
            }

            Log::debug('Database connection established', ['connection' => $name]);
            
            return $pdo;
        } catch (PDOException $e) {
            Log::error('Database connection failed', [
                'connection' => $name,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    private static function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['name'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        return match ($driver) {
            'mysql' => "mysql:host={$host};port={$port};dbname={$database};charset={$charset}",
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
            'sqlite' => "sqlite:{$database}",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$database}",
            default => throw new \InvalidArgumentException("Unsupported database driver: {$driver}")
        };
    }

    private static function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];
    }

    public static function getDefault(): PDO
    {
        if (self::$defaultConnection) {
            return self::$defaultConnection;
        }

        return self::connection('default');
    }

    public static function disconnect(?string $name = null): void
    {
        if ($name) {
            unset(self::$connections[$name]);
            if ($name === 'default') {
                self::$defaultConnection = null;
            }
        } else {
            self::$connections = [];
            self::$defaultConnection = null;
        }
    }

    public static function reconnect(string $name = 'default'): PDO
    {
        self::disconnect($name);
        return self::connection($name);
    }

    public static function beginTransaction(string $connection = 'default'): bool
    {
        return self::connection($connection)->beginTransaction();
    }

    public static function commit(string $connection = 'default'): bool
    {
        return self::connection($connection)->commit();
    }

    public static function rollback(string $connection = 'default'): bool
    {
        return self::connection($connection)->rollBack();
    }

    public static function transaction(callable $callback, string $connection = 'default'): mixed
    {
        $pdo = self::connection($connection);
        
        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getLastInsertId(string $connection = 'default'): string
    {
        return self::connection($connection)->lastInsertId();
    }

    public static function quote(string $string, string $connection = 'default'): string
    {
        return self::connection($connection)->quote($string);
    }

    public static function getPdoInfo(string $connection = 'default'): array
    {
        $pdo = self::connection($connection);
        
        return [
            'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        ];
    }

    public static function testConnection(string $name = 'default'): bool
    {
        try {
            $pdo = self::connection($name);
            $pdo->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            Log::error('Database connection test failed', [
                'connection' => $name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function getConnections(): array
    {
        return array_keys(self::$connections);
    }

    public static function isConfigured(): bool
    {
        return !empty(self::$config);
    }

    public static function getConfig(): array
    {
        return self::$config;
    }
}
