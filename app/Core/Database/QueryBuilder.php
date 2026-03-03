<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOStatement;
use App\Core\Log;

class QueryBuilder
{
    private PDO $connection;
    private string $table;
    private array $wheres = [];
    private array $orders = [];
    private array $joins = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $bindings = [];
    private string $type = 'select';
    private array $selects = ['*'];
    private array $inserts = [];
    private array $updates = [];

    public function __construct(PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::getDefault();
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array|string $columns): self
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        $this->type = 'select';
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null, string $boolean = 'and'): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'and'): self
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
            'placeholders' => $placeholders
        ];

        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function whereNotIn(string $column, array $values, string $boolean = 'and'): self
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $this->wheres[] = [
            'type' => 'notIn',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
            'placeholders' => $placeholders
        ];

        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function whereNull(string $column, string $boolean = 'and'): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean
        ];

        return $this;
    }

    public function whereNotNull(string $column, string $boolean = 'and'): self
    {
        $this->wheres[] = [
            'type' => 'notNull',
            'column' => $column,
            'boolean' => $boolean
        ];

        return $this;
    }

    public function whereBetween(string $column, array $values, string $boolean = 'and'): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'inner',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'left',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'right',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction)
        ];

        return $this;
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'desc');
    }

    public function orderByRaw(string $expression): self
    {
        $this->orders[] = [
            'type' => 'raw',
            'sql' => $expression
        ];

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function take(int $limit): self
    {
        return $this->limit($limit);
    }

    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }

    public function insert(array $data): bool
    {
        $this->type = 'insert';
        $this->inserts = $data;

        $sql = $this->toSql();
        $this->bindings = array_values($data);

        return $this->execute($sql);
    }

    public function insertGetId(array $data): string
    {
        $this->insert($data);
        return Connection::getLastInsertId();
    }

    public function update(array $data): int
    {
        $this->type = 'update';
        $this->updates = $data;

        $sql = $this->toSql();
        $this->prepareUpdateBindings($data);

        $this->execute($sql);
        return $this->rowCount();
    }

    public function delete(): int
    {
        $this->type = 'delete';
        $sql = $this->toSql();

        $this->execute($sql);
        return $this->rowCount();
    }

    public function get(): array
    {
        $this->type = 'select';
        $sql = $this->toSql();

        return $this->fetchAll($sql);
    }

    public function first(): ?array
    {
        $this->type = 'select';
        $this->limit(1);
        $sql = $this->toSql();

        $result = $this->fetch($sql);
        return $result ?: null;
    }

    public function find($id): ?array
    {
        return $this->where('id', $id)->first();
    }

    public function value(string $column): mixed
    {
        $this->select($column);
        $result = $this->first();
        
        return $result ? $result[$column] : null;
    }

    public function pluck(string $column, ?string $key = null): array
    {
        $results = $this->get();
        $pluck = [];

        foreach ($results as $result) {
            if ($key) {
                $pluck[$result[$key]] = $result[$column];
            } else {
                $pluck[] = $result[$column];
            }
        }

        return $pluck;
    }

    public function count(): int
    {
        $this->select('COUNT(*) as count');
        $result = $this->first();
        
        return (int) ($result['count'] ?? 0);
    }

    public function sum(string $column): float
    {
        $this->select("SUM({$column}) as sum");
        $result = $this->first();
        
        return (float) ($result['sum'] ?? 0);
    }

    public function avg(string $column): float
    {
        $this->select("AVG({$column}) as avg");
        $result = $this->first();
        
        return (float) ($result['avg'] ?? 0);
    }

    public function min(string $column): mixed
    {
        $this->select("MIN({$column}) as min");
        $result = $this->first();
        
        return $result['min'] ?? null;
    }

    public function max(string $column): mixed
    {
        $this->select("MAX({$column}) as max");
        $result = $this->first();
        
        return $result['max'] ?? null;
    }

    public function exists(): bool
    {
        $this->select('1')->limit(1);
        $result = $this->first();
        
        return !empty($result);
    }

    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    public function toSql(): string
    {
        return match ($this->type) {
            'select' => $this->compileSelect(),
            'insert' => $this->compileInsert(),
            'update' => $this->compileUpdate(),
            'delete' => $this->compileDelete(),
            default => throw new \RuntimeException("Unsupported query type: {$this->type}")
        };
    }

    private function compileSelect(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects);
        $sql .= " FROM {$this->table}";
        
        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();
        $sql .= $this->compileOrders();
        $sql .= $this->compileLimit();

        return $sql;
    }

    private function compileInsert(): string
    {
        $columns = implode(', ', array_keys($this->inserts));
        $placeholders = implode(', ', array_fill(0, count($this->inserts), '?'));
        
        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    private function compileUpdate(): string
    {
        $set = [];
        
        foreach ($this->updates as $column => $value) {
            $set[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        $sql .= $this->compileWheres();
        
        return $sql;
    }

    private function compileDelete(): string
    {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->compileWheres();
        
        return $sql;
    }

    private function compileWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = ' WHERE ';
        $clauses = [];

        foreach ($this->wheres as $where) {
            $clause = $this->compileWhere($where);
            if ($clause) {
                $clauses[] = ($where['boolean'] === 'or' ? 'OR ' : '') . $clause;
            }
        }

        return $sql . implode(' ', $clauses);
    }

    private function compileWhere(array $where): string
    {
        return match ($where['type']) {
            'basic' => "{$where['column']} {$where['operator']} ?",
            'in' => "{$where['column']} IN ({$where['placeholders']})",
            'notIn' => "{$where['column']} NOT IN ({$where['placeholders']})",
            'null' => "{$where['column']} IS NULL",
            'notNull' => "{$where['column']} IS NOT NULL",
            'between' => "{$where['column']} BETWEEN ? AND ?",
            default => ''
        };
    }

    private function compileJoins(): string
    {
        if (empty($this->joins)) {
            return '';
        }

        $sql = '';
        
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        return $sql;
    }

    private function compileOrders(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $orders = [];
        
        foreach ($this->orders as $order) {
            if (isset($order['type']) && $order['type'] === 'raw') {
                $orders[] = $order['sql'];
            } else {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
        }

        return " ORDER BY " . implode(', ', $orders);
    }

    private function compileLimit(): string
    {
        $sql = '';
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function execute(string $sql): bool
    {
        $startTime = microtime(true);
        
        try {
            $statement = $this->connection->prepare($sql);
            $result = $statement->execute($this->bindings);
            
            $executionTime = microtime(true) - $startTime;
            Log::sql($sql, $this->bindings, $executionTime);
            
            return $result;
        } catch (\PDOException $e) {
            Log::error('Query failed', [
                'sql' => $sql,
                'bindings' => $this->bindings,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException("Query failed: " . $e->getMessage(), 0, $e);
        }
    }

    private function fetch(string $sql): mixed
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetch();
    }

    private function fetchAll(string $sql): array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetchAll();
    }

    private function rowCount(): int
    {
        return $this->connection->rowCount();
    }

    private function prepareUpdateBindings(array $data): void
    {
        $this->bindings = array_merge(array_values($data), $this->bindings);
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function reset(): self
    {
        $this->wheres = [];
        $this->orders = [];
        $this->joins = [];
        $this->limit = null;
        $this->offset = null;
        $this->bindings = [];
        $this->type = 'select';
        $this->selects = ['*'];
        $this->inserts = [];
        $this->updates = [];

        return $this;
    }

    public function raw(string $sql): PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute();
            
            $executionTime = microtime(true) - $startTime;
            Log::sql($sql, [], $executionTime);
            
            return $statement;
        } catch (\PDOException $e) {
            Log::error('Raw query failed', [
                'sql' => $sql,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException("Raw query failed: " . $e->getMessage(), 0, $e);
        }
    }

    public static function rawSql(string $sql, array $bindings = []): PDOStatement
    {
        $instance = new self();
        $instance->bindings = $bindings;
        
        return $instance->raw($sql);
    }
}
