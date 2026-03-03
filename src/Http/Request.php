<?php

namespace App\Http;

class Request
{
    private $get;
    private $post;
    private $server;
    private $files;
    private $uri;
    private $method;

    public function __construct($get, $post, $server)
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->files = $_FILES;

        // Limpa a URI (ex: transforma /rifa/fusca?id=1 em /rifa/fusca)
        $this->uri = parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->method = $server['REQUEST_METHOD'] ?? 'GET';
    }

    public function get($key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function input($key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all()
    {
        return array_merge($this->post, $this->get);
    }

    public function getMethod()
    {
        return strtoupper($this->method);
    }

    public function method()
    {
        return $this->getMethod();
    }

    public function getUri()
    {
        return $this->uri;
    }
    
    public function uri()
    {
        return rtrim($this->uri, '/') ?: '/';
    }

    public function isPost()
    {
        return $this->method() === 'POST';
    }

    public function isGet()
    {
        return $this->method() === 'GET';
    }

    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    public function has($key)
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function hasFile($key)
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function getHeader($key, $default = null)
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$headerKey] ?? $default;
    }

    public function isAjax()
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getClientIp()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($this->server[$key])) {
                $ips = explode(',', $this->server[$key]);
                return trim($ips[0]);
            }
        }

        return '127.0.0.1';
    }

    public function getUserAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function getReferer()
    {
        return $this->server['HTTP_REFERER'] ?? '';
    }

    public function validate($rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $this->input($field);

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "O campo {$field} é obrigatório";
                continue;
            }

            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "O campo {$field} deve ser um email válido";
            }

            if (strpos($rule, 'min:') !== false) {
                $min = (int) substr($rule, strpos($rule, 'min:') + 4);
                if (strlen($value) < $min) {
                    $errors[$field] = "O campo {$field} deve ter no mínimo {$min} caracteres";
                }
            }
        }

        return $errors;
    }

    public static function capture()
    {
        return new self($_GET, $_POST, $_SERVER);
    }
}
