<?php

namespace App\Database;

use PDO;

class QueryBuilder {
    private $pdo;
    private $table;
    private $wheres = [];
    private $bindings = [];
    private $selects = ['*'];
    private $limit;
    private $offset;

    public function __construct($table) {
        $this->pdo = Connection::getInstance()->getPdo();
        $this->table = $table;
    }

    public function select($columns = ['*']) {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function limit($limit, $offset = 0) {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function get() {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset) {
                $sql .= " OFFSET {$this->offset}";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first() {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function find($id) {
        return $this->where('id', $id)->first();
    }

    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    public function update($data) {
        if (empty($this->wheres)) {
            throw new \Exception("Update sem WHERE não permitido");
        }

        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);
        $sql .= " WHERE " . implode(' AND ', $this->wheres);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($values, $this->bindings));
        
        return $stmt->rowCount();
    }

    public function delete() {
        if (empty($this->wheres)) {
            throw new \Exception("Delete sem WHERE não permitido");
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $this->wheres);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        
        return $stmt->rowCount();
    }

    public function count() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
}
