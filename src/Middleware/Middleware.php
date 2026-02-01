<?php

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Helpers\AuthHelper;

abstract class Middleware {
    /**
     * Handle the incoming request
     */
    abstract public function handle(Request $request, $next);
}

/**
 * Middleware de autenticação
 */
class AuthMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        if (!AuthHelper::check()) {
            return Response::redirect('/login');
        }
        
        return $next($request);
    }
}

/**
 * Middleware de guest (usuário não logado)
 */
class GuestMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        if (AuthHelper::check()) {
            return Response::redirect('/dashboard');
        }
        
        return $next($request);
    }
}

/**
 * Middleware de admin
 */
class AdminMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        if (!AuthHelper::check() || !AuthHelper::isAdmin()) {
            return Response::redirect('/');
        }
        
        return $next($request);
    }
}

/**
 * Middleware de CORS
 */
class CorsMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($request->method() === 'OPTIONS') {
            return Response::make('', 200);
        }
        
        return $next($request);
    }
}

/**
 * Middleware de rate limiting
 */
class RateLimitMiddleware extends Middleware {
    private $maxRequests;
    private $timeWindow;
    
    public function __construct($maxRequests = 60, $timeWindow = 60) {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }
    
    public function handle(Request $request, $next) {
        $ip = $request->getClientIp();
        $key = "rate_limit_{$ip}";
        
        $requests = CacheHelper::get($key, 0);
        
        if ($requests >= $this->maxRequests) {
            return Response::json([
                'error' => 'Too many requests'
            ], 429);
        }
        
        CacheHelper::put($key, $requests + 1, $this->timeWindow);
        
        return $next($request);
    }
}

/**
 * Middleware de logging
 */
class LoggingMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        LogHelper::request(
            $request->method(),
            $request->uri(),
            $response->getStatusCode(),
            $duration
        );
        
        return $response;
    }
}

/**
 * Middleware de manutenção
 */
class MaintenanceMiddleware extends Middleware {
    public function handle(Request $request, $next) {
        $maintenanceFile = FileHelper::storage('maintenance');
        
        if (FileHelper::exists($maintenanceFile)) {
            $maintenanceData = json_decode(FileHelper::get($maintenanceFile), true);
            
            if ($maintenanceData['enabled'] ?? false) {
                return Response::make($maintenanceData['message'] ?? 'Sistema em manutenção', 503);
            }
        }
        
        return $next($request);
    }
}

/**
 * Gerenciador de middleware
 */
class MiddlewareManager {
    private static $middleware = [];
    
    /**
     * Adicionar middleware
     */
    public static function add($name, $middleware) {
        self::$middleware[$name] = $middleware;
    }
    
    /**
     * Executar middleware
     */
    public static function run($middleware, Request $request, $callback) {
        if (is_string($middleware)) {
            $middleware = self::$middleware[$middleware] ?? null;
        }
        
        if ($middleware instanceof Middleware) {
            return $middleware->handle($request, $callback);
        }
        
        if (is_callable($middleware)) {
            return $middleware($request, $callback);
        }
        
        return $callback($request);
    }
    
    /**
     * Executar múltiplos middleware
     */
    public static function runStack($middlewareStack, Request $request, $callback) {
        $next = $callback;
        
        foreach (array_reverse($middlewareStack) as $middleware) {
            $next = function($request) use ($middleware, $next) {
                return self::run($middleware, $request, $next);
            };
        }
        
        return $next($request);
    }
}
