<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\AuthManager;

class AdminAuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!AuthManager::admin()->check()) {
            if ($request->isAjax()) {
                return (new Response())->json([
                    'error' => 'Unauthorized',
                    'message' => 'Admin authentication required'
                ], 401);
            }
            
            return (new Response())->redirect('/admin/login');
        }

        return $next($request);
    }
}
