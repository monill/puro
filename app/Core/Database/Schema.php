<?php

declare(strict_types=1);

namespace App\Core\Database;

class Schema
{
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private string $primaryKey = 'id';
    private bool $autoIncrement = true;
    private string $engine = 'InnoDB';
    private string $charset = 'utf8mb4';
    private string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): self
    {
        $this->addColumn($name, 'BIGINT', ['unsigned' => true, 'auto_increment' => true]);
        $this->primaryKey = $name;
        $this->autoIncrement = true;
        return $this;
    }

    public function bigInteger(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'BIGINT', $options);
    }

    public function integer(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'INT', $options);
    }

    public function mediumInteger(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'MEDIUMINT', $options);
    }

    public function smallInteger(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'SMALLINT', $options);
    }

    public function tinyInteger(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'TINYINT', $options);
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2, array $options = []): self
    {
        return $this->addColumn($name, "DECIMAL({$precision}, {$scale})", $options);
    }

    public function double(string $name, int $precision = 8, int $scale = 2, array $options = []): self
    {
        return $this->addColumn($name, "DOUBLE({$precision}, {$scale})", $options);
    }

    public function float(string $name, int $precision = 8, int $scale = 2, array $options = []): self
    {
        return $this->addColumn($name, "FLOAT({$precision}, {$scale})", $options);
    }

    public function string(string $name, int $length = 255, array $options = []): self
    {
        return $this->addColumn($name, "VARCHAR({$length})", $options);
    }

    public function char(string $name, int $length = 255, array $options = []): self
    {
        return $this->addColumn($name, "CHAR({$length})", $options);
    }

    public function text(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'TEXT', $options);
    }

    public function mediumText(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'MEDIUMTEXT', $options);
    }

    public function longText(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'LONGTEXT', $options);
    }

    public function json(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'JSON', $options);
    }

    public function boolean(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'BOOLEAN', $options);
    }

    public function date(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'DATE', $options);
    }

    public function dateTime(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'DATETIME', $options);
    }

    public function timestamp(string $name, array $options = []): self
    {
        return $this->addColumn($name, 'TIMESTAMP', $options);
    }

    public function timestamps(): self
    {
        $this->addColumn('created_at', 'TIMESTAMP', ['default' => 'CURRENT_TIMESTAMP']);
        $this->addColumn('updated_at', 'TIMESTAMP', ['default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP']);
        return $this;
    }

    public function softDeletes(): self
    {
        return $this->addColumn('deleted_at', 'TIMESTAMP', ['nullable' => true]);
    }

    public function rememberToken(): self
    {
        return $this->string('remember_token', 100)->nullable();
    }

    private function addColumn(string $name, string $type, array $options = []): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => $type,
            'nullable' => $options['nullable'] ?? false,
            'default' => $options['default'] ?? null,
            'auto_increment' => $options['auto_increment'] ?? false,
            'unsigned' => $options['unsigned'] ?? false,
            'on_update' => $options['on_update'] ?? null,
        ];

        return $this;
    }

    public function nullable(): self
    {
        $lastColumn = &$this->columns[array_key_last($this->columns)];
        $lastColumn['nullable'] = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $lastColumn = &$this->columns[array_key_last($this->columns)];
        $lastColumn['default'] = $value;
        return $this;
    }

    public function unsigned(): self
    {
        $lastColumn = &$this->columns[array_key_last($this->columns)];
        $lastColumn['unsigned'] = true;
        return $this;
    }

    public function primary(?string $column = null): self
    {
        $this->primaryKey = $column ?? $this->columns[array_key_last($this->columns)]['name'];
        return $this;
    }

    public function unique(?string $name = null): self
    {
        $lastColumn = $this->columns[array_key_last($this->columns)];
        $indexName = $name ?? 'unique_' . $this->table . '_' . $lastColumn['name'];
        
        $this->indexes[] = [
            'type' => 'unique',
            'name' => $indexName,
            'columns' => [$lastColumn['name']]
        ];
        
        return $this;
    }

    public function index(?string $name = null): self
    {
        $lastColumn = $this->columns[array_key_last($this->columns)];
        $indexName = $name ?? 'idx_' . $this->table . '_' . $lastColumn['name'];
        
        $this->indexes[] = [
            'type' => 'index',
            'name' => $indexName,
            'columns' => [$lastColumn['name']]
        ];
        
        return $this;
    }

    public function foreign(string $referencesTable, string $referencesColumn, ?string $name = null): self
    {
        $lastColumn = $this->columns[array_key_last($this->columns)];
        $constraintName = $name ?? 'fk_' . $this->table . '_' . $lastColumn['name'];
        
        $this->foreignKeys[] = [
            'name' => $constraintName,
            'column' => $lastColumn['name'],
            'references_table' => $referencesTable,
            'references_column' => $referencesColumn
        ];
        
        return $this;
    }

    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    public function toSql(): string
    {
        $sql = "CREATE TABLE {$this->table} (\n";
        
        // Columns
        $columnDefinitions = [];
        foreach ($this->columns as $column) {
            $columnDefinitions[] = $this->compileColumn($column);
        }
        
        // Primary Key
        if ($this->primaryKey) {
            $columnDefinitions[] = "PRIMARY KEY ({$this->primaryKey})";
        }
        
        $sql .= "    " . implode(",\n    ", $columnDefinitions);
        
        // Indexes
        foreach ($this->indexes as $index) {
            $columns = implode(', ', $index['columns']);
            $indexType = $index['type'] === 'unique' ? 'UNIQUE INDEX' : 'INDEX';
            $sql .= ",\n    {$indexType} {$index['name']} ({$columns})";
        }
        
        // Foreign Keys
        foreach ($this->foreignKeys as $foreignKey) {
            $sql .= ",\n    CONSTRAINT {$foreignKey['name']} ";
            $sql .= "FOREIGN KEY ({$foreignKey['column']}) ";
            $sql .= "REFERENCES {$foreignKey['references_table']}({$foreignKey['references_column']})";
        }
        
        $sql .= "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation}";
        
        return $sql;
    }

    private function compileColumn(array $column): string
    {
        $definition = "    {$column['name']} {$column['type']}";
        
        if ($column['unsigned']) {
            $definition .= ' UNSIGNED';
        }
        
        if (!$column['nullable']) {
            $definition .= ' NOT NULL';
        }
        
        if ($column['default'] !== null) {
            $default = is_string($column['default']) ? "'{$column['default']}'" : $column['default'];
            $definition .= " DEFAULT {$default}";
        }
        
        if ($column['auto_increment']) {
            $definition .= ' AUTO_INCREMENT';
        }
        
        if ($column['on_update']) {
            $definition .= " ON UPDATE {$column['on_update']}";
        }
        
        return $definition;
    }
}
