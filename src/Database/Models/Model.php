<?php

namespace App\Database\Models;

use App\Database\QueryBuilder;

abstract class Model {
    protected static $table;
    protected $attributes = [];

    public function __construct($attributes = []) {
        $this->fill($attributes);
    }

    public function fill($attributes) {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($key) {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function toArray() {
        return $this->attributes;
    }

    public static function query() {
        return new QueryBuilder(static::$table);
    }

    public static function select($columns = ['*']) {
        return static::query()->select($columns);
    }

    public static function where($column, $operator, $value = null) {
        return static::query()->where($column, $operator, $value);
    }

    public static function find($id) {
        $result = static::where('id', $id)->first();
        return $result ? new static($result) : null;
    }

    public static function first() {
        $result = static::query()->first();
        return $result ? new static($result) : null;
    }

    public static function all() {
        $results = static::query()->get();
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }

    public static function create($data) {
        $id = static::query()->insert($data);
        return static::find($id);
    }

    public function save() {
        if (isset($this->attributes['id'])) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    public function update() {
        if (!isset($this->attributes['id'])) {
            throw new \Exception("Cannot update model without ID");
        }

        $data = $this->attributes;
        unset($data['id']);
        
        static::where('id', $this->attributes['id'])->update($data);
        return $this;
    }

    public function delete() {
        if (!isset($this->attributes['id'])) {
            throw new \Exception("Cannot delete model without ID");
        }

        return static::where('id', $this->attributes['id'])->delete();
    }

    public static function count() {
        return static::query()->count();
    }
}
