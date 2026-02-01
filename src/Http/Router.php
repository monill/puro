<?php

namespace App\Http;

class Router {
    private static $routes = [];
    private static $middleware = [];

    public static function get($path, $handler, $middleware = []) {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post($path, $handler, $middleware = []) {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put($path, $handler, $middleware = []) {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function delete($path, $handler, $middleware = []) {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    public static function addRoute($method, $path, $handler, $middleware = []) {
        $path = self::normalizePath($path);
        self::$routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public static function middleware($name, $callback) {
        self::$middleware[$name] = $callback;
    }

    public static function dispatch(Request $request) {
        $method = $request->method();
        $uri = $request->uri();

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

    private static function handleRoute($routeData, Request $request, $params = []) {
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

    private static function matchesRoute($route, $uri, &$params) {
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

    private static function normalizePath($path) {
        return '/' . trim($path, '/');
    }

    private static function notFound($message = 'Página não encontrada') {
        return Response::make($message, 404);
    }

    public static function getRoutes() {
        return self::$routes;
    }

    public static function getMiddleware() {
        return self::$middleware;
    }
}
