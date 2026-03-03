<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\AuthManager;

class CustomerAuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!AuthManager::customer()->check()) {
            if ($request->isAjax()) {
                return (new Response())->json([
                    'error' => 'Unauthorized',
                    'message' => 'Customer authentication required'
                ], 401);
            }
            
            return (new Response())->redirect('/login');
        }

        return $next($request);
    }
}
