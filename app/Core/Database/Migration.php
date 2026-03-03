<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use App\Core\File;
use App\Core\Log;

abstract class Migration
{
    protected PDO $connection;
    protected string $table = 'migrations';

    public function __construct()
    {
        $this->connection = Connection::getDefault();
    }

    abstract public function up(): void;

    abstract public function down(): void;

    public function migrate(): bool
    {
        try {
            $this->up();
            $this->logMigration($this->getMigrationName());
            Log::info('Migration executed successfully', ['migration' => $this->getMigrationName()]);
            return true;
        } catch (\Exception $e) {
            Log::error('Migration failed', [
                'migration' => $this->getMigrationName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function rollback(): bool
    {
        try {
            $this->down();
            $this->removeMigrationLog($this->getMigrationName());
            Log::info('Migration rolled back successfully', ['migration' => $this->getMigrationName()]);
            return true;
        } catch (\Exception $e) {
            Log::error('Migration rollback failed', [
                'migration' => $this->getMigrationName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function createTable(string $table, callable $callback): void
    {
        $schema = new Schema($table);
        $callback($schema);
        
        $sql = $schema->toSql();
        $this->connection->exec($sql);
    }

    protected function dropTable(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $this->connection->exec($sql);
    }

    protected function dropTableIfExists(string $table): void
    {
        $this->dropTable($table);
    }

    protected function renameTable(string $from, string $to): void
    {
        $sql = "RENAME TABLE {$from} TO {$to}";
        $this->connection->exec($sql);
    }

    protected function addColumn(string $table, string $column, string $type): void
    {
        $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$type}";
        $this->connection->exec($sql);
    }

    protected function dropColumn(string $table, string $column): void
    {
        $sql = "ALTER TABLE {$table} DROP COLUMN {$column}";
        $this->connection->exec($sql);
    }

    protected function renameColumn(string $table, string $from, string $to): void
    {
        $sql = "ALTER TABLE {$table} RENAME COLUMN {$from} TO {$to}";
        $this->connection->exec($sql);
    }

    protected function modifyColumn(string $table, string $column, string $type): void
    {
        $sql = "ALTER TABLE {$table} MODIFY COLUMN {$column} {$type}";
        $this->connection->exec($sql);
    }

    protected function addIndex(string $table, array $columns, ?string $name = null): void
    {
        $indexName = $name ?? 'idx_' . $table . '_' . implode('_', $columns);
        $columnsStr = implode(', ', $columns);
        
        $sql = "CREATE INDEX {$indexName} ON {$table} ({$columnsStr})";
        $this->connection->exec($sql);
    }

    protected function dropIndex(string $table, string $name): void
    {
        $sql = "DROP INDEX {$name} ON {$table}";
        $this->connection->exec($sql);
    }

    protected function addForeignKey(string $table, string $column, string $referencesTable, string $referencesColumn, ?string $name = null): void
    {
        $constraintName = $name ?? 'fk_' . $table . '_' . $column;
        
        $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} 
                FOREIGN KEY ({$column}) REFERENCES {$referencesTable}({$referencesColumn})";
        
        $this->connection->exec($sql);
    }

    protected function dropForeignKey(string $table, string $name): void
    {
        $sql = "ALTER TABLE {$table} DROP FOREIGN KEY {$name}";
        $this->connection->exec($sql);
    }

    private function getMigrationName(): string
    {
        $className = static::class;
        return substr($className, strrpos($className, '\\') + 1);
    }

    private function logMigration(string $migration): void
    {
        $this->ensureMigrationsTable();
        
        $sql = "INSERT INTO {$this->table} (migration, executed_at) VALUES (?, NOW())";
        $statement = $this->connection->prepare($sql);
        $statement->execute([$migration]);
    }

    private function removeMigrationLog(string $migration): void
    {
        $sql = "DELETE FROM {$this->table} WHERE migration = ?";
        $statement = $this->connection->prepare($sql);
        $statement->execute([$migration]);
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        )";
        
        $this->connection->exec($sql);
    }

    public static function getExecutedMigrations(): array
    {
        $instance = new static();
        $instance->ensureMigrationsTable();
        
        $sql = "SELECT migration FROM {$instance->table} ORDER BY executed_at DESC";
        $statement = $instance->connection->query($sql);
        
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getPendingMigrations(string $migrationsPath): array
    {
        $executed = static::getExecutedMigrations();
        $allMigrations = static::getAllMigrations($migrationsPath);
        
        return array_diff($allMigrations, $executed);
    }

    private static function getAllMigrations(string $migrationsPath): array
    {
        if (!File::exists($migrationsPath)) {
            return [];
        }

        $files = File::files($migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $migrations[] = $className;
            }
        }

        return $migrations;
    }

    public static function runMigrations(string $migrationsPath): array
    {
        $pending = static::getPendingMigrations($migrationsPath);
        $executed = [];
        $failed = [];

        foreach ($pending as $migration) {
            try {
                require_once $migrationsPath . '/' . $migration . '.php';
                
                if (class_exists($migration)) {
                    $instance = new $migration();
                    $instance->migrate();
                    $executed[] = $migration;
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'migration' => $migration,
                    'error' => $e->getMessage()
                ];
                Log::error('Migration failed', [
                    'migration' => $migration,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'executed' => $executed,
            'failed' => $failed
        ];
    }

    public static function rollbackLastMigration(string $migrationsPath): bool
    {
        $executed = static::getExecutedMigrations();
        
        if (empty($executed)) {
            return false;
        }

        $lastMigration = $executed[0];
        
        try {
            require_once $migrationsPath . '/' . $lastMigration . '.php';
            
            if (class_exists($lastMigration)) {
                $instance = new $lastMigration();
                $instance->rollback();
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Migration rollback failed', [
                'migration' => $lastMigration,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }
}
