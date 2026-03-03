<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if ($this->shouldVerifyCsrf($request)) {
            $token = $request->post('_token') ?: $request->header('X-CSRF-TOKEN');
            
            if (!Session::validateCsrf($token)) {
                if ($request->isAjax()) {
                    return (new Response())->json([
                        'error' => 'CSRF token mismatch',
                        'message' => 'Invalid CSRF token'
                    ], 419);
                }
                
                return (new Response())->json([
                    'error' => 'CSRF token mismatch',
                    'message' => 'Invalid CSRF token'
                ], 419);
            }
        }

        return $next($request);
    }

    private function shouldVerifyCsrf(Request $request): bool
    {
        $excludedRoutes = $this->getParam('exclude', []);
        
        foreach ($excludedRoutes as $route) {
            if ($request->getPath() === $route) {
                return false;
            }
        }

        return in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }
}
