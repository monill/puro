<?php

declare(strict_types=1);

namespace App\Core;

class Route
{
    private static array $routes = [];
    private static array $middlewares = [];
    private static array $patterns = [
        '{id}' => '(\d+)',
        '{slug}' => '([a-zA-Z0-9\-_]+)',
        '{alpha}' => '([a-zA-Z]+)',
        '{alphanum}' => '([a-zA-Z0-9]+)',
        '{any}' => '([^/]+)',
        '{num}' => '(\d+)',
        '{uuid}' => '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
    ];

    public static function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }

    public static function patch(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('PATCH', $path, $handler, $middlewares);
    }

    public static function delete(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }

    public static function options(string $path, callable|array $handler, array $middlewares = []): void
    {
        self::addRoute('OPTIONS', $path, $handler, $middlewares);
    }

    public static function any(string $path, callable|array $handler, array $middlewares = []): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        foreach ($methods as $method) {
            self::addRoute($method, $path, $handler, $middlewares);
        }
    }

    public static function match(array $methods, string $path, callable|array $handler, array $middlewares = []): void
    {
        foreach ($methods as $method) {
            self::addRoute(strtoupper($method), $path, $handler, $middlewares);
        }
    }

    private static function addRoute(string $method, string $path, callable|array $handler, array $middlewares): void
    {
        $route = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
            'regex' => self::compileRegex($path),
            'params' => self::extractParamNames($path)
        ];

        self::$routes[] = $route;
    }

    private static function compileRegex(string $path): string
    {
        $regex = $path;
        
        foreach (self::$patterns as $placeholder => $pattern) {
            $regex = str_replace($placeholder, $pattern, $regex);
        }
        
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $regex);
        $regex = '#^' . $regex . '$#';
        
        return $regex;
    }

    private static function extractParamNames(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    public static function dispatch(Request $request): ?Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                
                array_shift($matches);
                
                foreach ($matches as $i => $value) {
                    $paramName = $route['params'][$i] ?? $i;
                    $params[$paramName] = $value;
                }

                $request->setRouteParams($params);

                $response = self::executeMiddlewares($route['middlewares'], $request);
                
                if ($response instanceof Response) {
                    return $response;
                }

                return self::executeHandler($route['handler'], $request, $response);
            }
        }

        return new Response('Not Found', 404);
    }

    private static function executeMiddlewares(array $middlewares, Request $request): ?Response
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                if (!class_exists($middleware)) {
                    throw new \RuntimeException("Middleware class not found: {$middleware}");
                }
                
                $middlewareInstance = new $middleware();
                
                if (!method_exists($middlewareInstance, 'handle')) {
                    throw new \RuntimeException("Middleware must have handle method: {$middleware}");
                }
                
                $response = $middlewareInstance->handle($request);
                
                if ($response instanceof Response) {
                    return $response;
                }
            } elseif (is_callable($middleware)) {
                $response = $middleware($request);
                
                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        return null;
    }

    private static function executeHandler(callable|array $handler, Request $request, ?Response $response): Response
    {
        if (is_callable($handler)) {
            $result = $handler($request, $response);
            return $result instanceof Response ? $result : new Response($result);
        }

        if (is_array($handler)) {
            [$controller, $method] = $handler;
            
            if (!class_exists($controller)) {
                throw new \RuntimeException("Controller class not found: {$controller}");
            }
            
            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                throw new \RuntimeException("Method not found: {$method} in {$controller}");
            }
            
            $result = $controllerInstance->$method($request, $response);
            return $result instanceof Response ? $result : new Response($result);
        }

        throw new \RuntimeException('Invalid handler type');
    }

    public static function addPattern(string $placeholder, string $regex): void
    {
        self::$patterns[$placeholder] = $regex;
    }

    public static function group(array $attributes, callable $callback): void
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];
        $namespace = $attributes['namespace'] ?? '';

        $previousRoutes = count(self::$routes);

        $callback();

        $newRoutes = array_slice(self::$routes, $previousRoutes);

        foreach ($newRoutes as &$route) {
            if ($prefix) {
                $route['path'] = $prefix . $route['path'];
                $route['regex'] = self::compileRegex($route['path']);
            }

            if ($middleware) {
                $route['middlewares'] = array_merge($middleware, $route['middlewares']);
            }

            if ($namespace && is_array($route['handler'])) {
                $route['handler'][0] = $namespace . '\\' . $route['handler'][0];
            }
        }
    }

    public static function resource(string $name, string $controller, array $options = []): void
    {
        $prefix = $options['prefix'] ?? '';
        $middleware = $options['middleware'] ?? [];
        $only = $options['only'] ?? ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];
        $except = $options['except'] ?? [];

        $methods = [
            'index' => ['GET', "/{$name}"],
            'show' => ['GET', "/{$name}/{id}"],
            'create' => ['GET', "/{$name}/create"],
            'store' => ['POST', "/{$name}"],
            'edit' => ['GET', "/{$name}/{id}/edit"],
            'update' => ['PUT', "/{$name}/{id}"],
            'destroy' => ['DELETE', "/{$name}/{id}"]
        ];

        foreach ($methods as $method => [$httpMethod, $path]) {
            if (in_array($method, $except) || !in_array($method, $only)) {
                continue;
            }

            $handler = [$controller, $method];
            self::addRoute($httpMethod, $prefix . $path, $handler, $middleware);
        }
    }

    public static function apiResource(string $name, string $controller, array $options = []): void
    {
        $only = $options['only'] ?? ['index', 'show', 'store', 'update', 'destroy'];
        $except = $options['except'] ?? ['create', 'edit'];

        self::resource($name, $controller, array_merge($options, [
            'only' => $only,
            'except' => $except
        ]));
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function clear(): void
    {
        self::$routes = [];
        self::$middlewares = [];
    }

    public static function url(string $name, array $params = []): string
    {
        foreach (self::$routes as $route) {
            if (isset($route['name']) && $route['name'] === $name) {
                $url = $route['path'];
                
                foreach ($params as $key => $value) {
                    $url = str_replace('{' . $key . '}', (string) $value, $url);
                }
                
                if (preg_match('/\{[^}]+\}/', $url)) {
                    throw new \RuntimeException("Missing required parameters for route: {$name}");
                }
                
                return $url;
            }
        }

        throw new \RuntimeException("Route not found: {$name}");
    }

    public static function name(string $name): self
    {
        $lastRoute = end(self::$routes);
        if ($lastRoute) {
            $lastRoute['name'] = $name;
            self::$routes[array_key_last(self::$routes)] = $lastRoute;
        }

        return new self();
    }

    public static function middleware(array|string $middlewares): self
    {
        $lastRoute = end(self::$routes);
        if ($lastRoute) {
            $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
            $lastRoute['middlewares'] = array_merge($lastRoute['middlewares'], $middlewares);
            self::$routes[array_key_last(self::$routes)] = $lastRoute;
        }

        return new self();
    }
}
