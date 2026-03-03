<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class CorsMiddleware extends Middleware
{
    private static array $config = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 86400,
        'supports_credentials' => false,
        'allow_credentials' => false,
        'preflight_continue' => true,
        'preflight_cache' => true,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public function handle(Request $request, callable $next): mixed
    {
        $response = $next($request);

        // Add CORS headers to response
        $this->addCorsHeaders($response, $request);

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            return $this->handlePreflight($request, $response);
        }

        return $response;
    }

    private function addCorsHeaders(Response $response, Request $request): void
    {
        $origin = $request->header('Origin');

        // Add Access-Control-Allow-Origin
        if ($this->isOriginAllowed($origin)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        // Add Access-Control-Allow-Methods
        if (!empty(self::$config['allowed_methods'])) {
            $response->setHeader('Access-Control-Allow-Methods', implode(', ', self::$config['allowed_methods']));
        }

        // Add Access-Control-Allow-Headers
        if (!empty(self::$config['allowed_headers'])) {
            $response->setHeader('Access-Control-Allow-Headers', implode(', ', self::$config['allowed_headers']));
        }

        // Add Access-Control-Expose-Headers
        if (!empty(self::$config['exposed_headers'])) {
            $response->setHeader('Access-Control-Expose-Headers', implode(', ', self::$config['exposed_headers']));
        }

        // Add Access-Control-Max-Age
        if (self::$config['preflight_cache'] && self::$config['max_age'] > 0) {
            $response->setHeader('Access-Control-Max-Age', (string) self::$config['max_age']);
        }

        // Add Access-Control-Allow-Credentials
        if (self::$config['allow_credentials']) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }
    }

    private function handlePreflight(Request $request, Response $response): Response
    {
        // Create a new response for preflight
        $preflightResponse = new Response('', 204);

        // Add CORS headers
        $this->addCorsHeaders($preflightResponse, $request);

        // Add Vary header for proper caching
        $preflightResponse->setHeader('Vary', 'Origin');

        return $preflightResponse;
    }

    private function isOriginAllowed(?string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        // Check if all origins are allowed
        if (in_array('*', self::$config['allowed_origins'])) {
            return true;
        }

        // Check if specific origin is allowed
        if (in_array($origin, self::$config['allowed_origins'])) {
            return true;
        }

        // Check for wildcard patterns
        foreach (self::$config['allowed_origins'] as $allowedOrigin) {
            if (str_contains($allowedOrigin, '*')) {
                $pattern = '/^' . str_replace('*', '.*', preg_quote($allowedOrigin, '/')) . '$/';
                if (preg_match($pattern, $origin)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function middleware(): callable
    {
        return function($request, $next) {
            $middleware = new self();
            return $middleware->handle($request, $next);
        };
    }

    public static function forOrigin(string $origin): self
    {
        $instance = new self();
        $instance->setConfig(['allowed_origins' => [$origin]]);
        return $instance;
    }

    public static function forOrigins(array $origins): self
    {
        $instance = new self();
        $instance->setConfig(['allowed_origins' => $origins]);
        return $instance;
    }

    public static function allowMethods(array $methods): self
    {
        $instance = new self();
        $instance->setConfig(['allowed_methods' => $methods]);
        return $instance;
    }

    public static function allowHeaders(array $headers): self
    {
        $instance = new self();
        $instance->setConfig(['allowed_headers' => $headers]);
        return $instance;
    }

    public static function exposeHeaders(array $headers): self
    {
        $instance = new self();
        $instance->setConfig(['exposed_headers' => $headers]);
        return $instance;
    }

    public static function allowCredentials(): self
    {
        $instance = new self();
        $instance->setConfig(['allow_credentials' => true]);
        return $instance;
    }

    public static function maxAge(int $seconds): self
    {
        $instance = new self();
        $instance->setConfig(['max_age' => $seconds]);
        return $instance;
    }

    public static function disablePreflightCache(): self
    {
        $instance = new self();
        $instance->setConfig(['preflight_cache' => false]);
        return $instance;
    }

    public static function setConfig(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function resetConfig(): void
    {
        self::$config = [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'exposed_headers' => [],
            'max_age' => 86400,
            'supports_credentials' => false,
            'allow_credentials' => false,
            'preflight_continue' => true,
            'preflight_cache' => true,
        ];
    }

    // Utility methods for common CORS configurations
    public static function api(): self
    {
        return (new self())
            ->setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'],
                'exposed_headers' => ['X-Total-Count', 'X-Page-Count'],
                'max_age' => 86400,
            ]);
    }

    public static function web(): self
    {
        return (new self())
            ->setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN'],
                'allow_credentials' => true,
                'max_age' => 3600,
            ]);
    }

    public static function secure(): self
    {
        return (new self())
            ->setConfig([
                'allowed_origins' => [], // Must be explicitly set
                'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
                'allow_credentials' => true,
                'max_age' => 3600,
            ]);
    }

    public static function development(): self
    {
        return (new self())
            ->setConfig([
                'allowed_origins' => ['*'],
                'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers' => ['*'],
                'exposed_headers' => ['*'],
                'max_age' => 0, // No caching in development
            ]);
    }

    public static function production(): self
    {
        return (new self())
            ->setConfig([
                'allowed_origins' => ['https://yourdomain.com'], // Must be explicitly set
                'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
                'max_age' => 86400,
                'allow_credentials' => false,
            ]);
    }

    // Method to validate CORS configuration
    public static function validateConfig(array $config): array
    {
        $errors = [];

        // Check allowed_origins
        if (empty($config['allowed_origins'])) {
            $errors[] = 'allowed_origins cannot be empty';
        }

        // Check allowed_methods
        if (empty($config['allowed_methods'])) {
            $errors[] = 'allowed_methods cannot be empty';
        }

        // Check allowed_headers
        if (empty($config['allowed_headers'])) {
            $errors[] = 'allowed_headers cannot be empty';
        }

        // Check max_age
        if (isset($config['max_age']) && (!is_int($config['max_age']) || $config['max_age'] < 0)) {
            $errors[] = 'max_age must be a non-negative integer';
        }

        // Check allow_credentials
        if (isset($config['allow_credentials']) && !is_bool($config['allow_credentials'])) {
            $errors[] = 'allow_credentials must be a boolean';
        }

        return $errors;
    }

    // Method to get current CORS headers for a request
    public static function getCorsHeaders(Request $request): array
    {
        $headers = [];
        $origin = $request->header('Origin');

        if ($this->isOriginAllowed($origin)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        }

        if (!empty(self::$config['allowed_methods'])) {
            $headers['Access-Control-Allow-Methods'] = implode(', ', self::$config['allowed_methods']);
        }

        if (!empty(self::$config['allowed_headers'])) {
            $headers['Access-Control-Allow-Headers'] = implode(', ', self::$config['allowed_headers']);
        }

        if (!empty(self::$config['exposed_headers'])) {
            $headers['Access-Control-Expose-Headers'] = implode(', ', self::$config['exposed_headers']);
        }

        if (self::$config['preflight_cache'] && self::$config['max_age'] > 0) {
            $headers['Access-Control-Max-Age'] = (string) self::$config['max_age'];
        }

        if (self::$config['allow_credentials']) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return $headers;
    }

    // Method to check if a request is a CORS request
    public static function isCorsRequest(Request $request): bool
    {
        return !empty($request->header('Origin')) && $request->header('Origin') !== $request->header('Host');
    }

    // Method to check if a request is a preflight request
    public static function isPreflightRequest(Request $request): bool
    {
        return $request->method() === 'OPTIONS' && 
               !empty($request->header('Access-Control-Request-Method'));
    }

    // Method to get the current origin from request
    public static function getOrigin(Request $request): ?string
    {
        return $request->header('Origin');
    }

    // Method to get the requested method from preflight request
    public static function getRequestedMethod(Request $request): ?string
    {
        return $request->header('Access-Control-Request-Method');
    }

    // Method to get the requested headers from preflight request
    public static function getRequestedHeaders(Request $request): array
    {
        $headers = $request->header('Access-Control-Request-Headers');
        return $headers ? explode(',', $headers) : [];
    }
}
