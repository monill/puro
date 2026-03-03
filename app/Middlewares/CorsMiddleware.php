<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class CorsMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $response = $next($request);
        
        if ($response instanceof Response) {
            $this->addCorsHeaders($response);
        } else {
            $response = (new Response())->setContent($response);
            $this->addCorsHeaders($response);
        }
        
        return $response;
    }

    private function addCorsHeaders(Response $response): void
    {
        $allowedOrigins = $this->getParam('origins', ['*']);
        $allowedMethods = $this->getParam('methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
        $allowedHeaders = $this->getParam('headers', ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN']);
        $maxAge = $this->getParam('max_age', 86400);
        $credentials = $this->getParam('credentials', false);

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        $response->setHeader('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        $response->setHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        $response->setHeader('Access-Control-Max-Age', (string) $maxAge);

        if ($credentials) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }
    }
}
