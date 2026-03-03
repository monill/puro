<?php

namespace App\Http;

class Router
{
    private static $routes = [];
    private static $middleware = [];
    private static $globalMiddlewares = [];
    private static $middlewareGroups = [];
    private static $lastRoute = null; // Para saber qual a última rota adicionada

    public static function get($path, $handler, $middleware = [])
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post($path, $handler, $middleware = [])
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put($path, $handler, $middleware = [])
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function delete($path, $handler, $middleware = [])
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    public static function addRoute($method, $path, $handler, $middleware = [])
    {
        $path = self::normalizePath($path);

        // Inicializa a rota no array estático
        self::$routes[$method][$path] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => (array)$middleware,
            'where'      => [],
            'name'       => null
        ];

        // Retornamos um objeto Proxy para permitir o encadeamento: ->where(), ->name()
        // Passamos o método e o path para que o objeto saiba qual rota alterar
        return new class($method, $path) {
            private $method;
            private $path;

            public function __construct($method, $path)
            {
                $this->method = $method;
                $this->path = $path;
            }

            public function where($param, $regex)
            {
                \App\Http\Router::updateLastRoute($this->method, $this->path, 'where', [$param => $regex]);
                return $this;
            }

            public function name($name)
            {
                \App\Http\Router::updateLastRoute($this->method, $this->path, 'name', $name);
                return $this;
            }

            public function middleware($m)
            {
                \App\Http\Router::updateLastRoute($this->method, $this->path, 'middleware', (array)$m);
                return $this;
            }
        };
    }

    public static function updateLastRoute($method, $path, $key, $value)
    {
        if (isset(self::$routes[$method][$path])) {
            if (is_array(self::$routes[$method][$path][$key])) {
                self::$routes[$method][$path][$key] = array_merge(self::$routes[$method][$path][$key], (array)$value);
            } else {
                self::$routes[$method][$path][$key] = $value;
            }
        }
    }

    public static function setLastRouteAttribute($key, $value)
    {
        $path = self::$lastRoute['path'];
        $method = self::$lastRoute['method'];

        if ($key === 'where' || $key === 'middleware') {
            self::$routes[$method][$path][$key] = array_merge(self::$routes[$method][$path][$key], (array)$value);
        } else {
            self::$routes[$method][$path][$key] = $value;
        }
    }

    public static function middleware($name, $callback)
    {
        self::$middleware[$name] = $callback;
    }

    public static function dispatch(Request $request)
    {
        $method = $request->method();
        $uri = $request->uri();

        // 1. Executar Middlewares Globais antes de tudo
        foreach (self::$globalMiddlewares as $middleware) {
            // Lógica para executar middleware global aqui...
        }

        if (!isset(self::$routes[$method])) {
            return self::notFound();
        }

        // Procurar rota exata
        if (isset(self::$routes[$method][$uri])) {
            return self::handleRoute(self::$routes[$method][$uri], $request);
        }

        // Procurar rota com parâmetros
        foreach (self::$routes[$method] as $route => $routeData) {
            if (self::matchesRoute($route, $uri, $params)) {
                return self::handleRoute($routeData, $request, $params);
            }
        }

        return self::notFound();
    }

    private static function handleRoute($routeData, Request $request, $params = [])
    {
        // Executar middleware
        foreach ($routeData['middleware'] as $middlewareName) {
            if (isset(self::$middleware[$middlewareName])) {
                $result = call_user_func(self::$middleware[$middlewareName], $request);
                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        $handler = $routeData['handler'];

        // Se for string "Controller@method"
        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";

            if (!class_exists($controllerClass)) {
                return self::notFound("Controller {$controller} não encontrado");
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                return self::notFound("Método {$method} não encontrado em {$controller}");
            }

            return call_user_func_array([$controllerInstance, $method], [$request] + $params);
        }

        // Se for Closure
        if (is_callable($handler)) {
            return call_user_func_array($handler, [$request] + $params);
        }

        return self::notFound();
    }

    private static function matchesRoute($route, $uri, &$params)
    {
        $routeParts = explode('/', trim($route, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (strpos($part, ':') === 0) {
                $params[substr($part, 1)] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return false;
            }
        }

        return true;
    }

    private static function normalizePath($path)
    {
        return '/' . trim($path, '/');
    }

    private static function notFound($message = 'Página não encontrada')
    {
        return Response::make($message, 404);
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    public static function getMiddleware()
    {
        return self::$middleware;
    }

    public static function globalMiddleware($middleware)
    {
        if (is_array($middleware)) {
            self::$globalMiddlewares = array_merge(self::$globalMiddlewares, $middleware);
        } else {
            self::$globalMiddlewares[] = $middleware;
        }
    }

    public static function middlewareGroup($name, array $middlewares)
    {
        self::$middlewareGroups[$name] = $middlewares;
    }

    public static function group($attributes, $callback)
    {
        // Por enquanto, apenas executa a função que está dentro do grupo
        // No futuro, você usará $attributes para adicionar prefixos ou middlewares
        if (is_callable($callback)) {
            $callback();
        }
    }

    public static function hasRoute($method, $uri)
    {
        $method = strtoupper($method);
        $uri = '/' . trim($uri, '/');

        // Verifica rota exata
        if (isset(self::$routes[$method][$uri])) {
            return true;
        }

        // Verifica rotas com parâmetros (:id, etc)
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $route => $data) {
                if (self::matchesRoute($route, $uri, $params)) {
                    return true;
                }
            }
        }

        return false;
    }
}
