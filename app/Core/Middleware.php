<?php

declare(strict_types=1);

namespace App\Core;

abstract class Middleware
{
    protected array $params = [];

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    abstract public function handle(Request $request, callable $next): mixed;

    protected function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    protected function hasParam(string $key): bool
    {
        return isset($this->params[$key]);
    }
}
