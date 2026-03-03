<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $headers;
    private string $method;
    private string $uri;
    private string $path;
    private ?string $queryString;
    private array $routeParams = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->headers = $this->getAllHeaders();
        $this->method = $this->server['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->server['REQUEST_URI'] ?? '/';
        $this->parseUri();
    }

    private function parseUri(): void
    {
        $parsedUrl = parse_url($this->uri);
        $this->path = $parsedUrl['path'] ?? '/';
        $this->queryString = $parsedUrl['query'] ?? null;
    }

    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->routeParams);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->get) || array_key_exists($key, $this->post) || array_key_exists($key, $this->routeParams);
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->header('Content-Type', ''), 'application/json');
    }

    public function getIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            $ip = $this->server($key);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
        
        return $this->server('REMOTE_ADDR', '127.0.0.1');
    }

    public function getUserAgent(): string
    {
        return $this->server('HTTP_USER_AGENT', '');
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    public function isSecure(): bool
    {
        return ($this->server('HTTPS') && $this->server('HTTPS') !== 'off') 
            || $this->server('SERVER_PORT') == 443 
            || $this->server('HTTP_X_FORWARDED_PROTO') === 'https';
    }

    public function getBaseUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server('HTTP_HOST', 'localhost');
        return $scheme . '://' . $host;
    }

    public function getUrl(): string
    {
        return $this->getBaseUrl() . $this->uri;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }
}
