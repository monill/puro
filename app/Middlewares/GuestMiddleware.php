<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;

class GuestMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (Auth::check()) {
            return (new Response())->redirect('/dashboard');
        }

        return $next($request);
    }
}
