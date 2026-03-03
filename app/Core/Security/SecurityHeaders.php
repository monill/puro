<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class SecurityHeaders extends Middleware
{
    private static array $config = [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'content_security_policy' => null,
        'permissions_policy' => null,
        'cross_origin_embedder_policy' => null,
        'cross_origin_opener_policy' => null,
        'cross_origin_resource_policy' => null,
        'expect_ct' => null,
        'feature_policy' => null,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public function handle(Request $request, callable $next): mixed
    {
        $response = $next($request);

        // Add security headers to response
        $this->addSecurityHeaders($response, $request);

        return $response;
    }

    private function addSecurityHeaders(Response $response, Request $request): void
    {
        // X-Frame-Options
        if (self::$config['x_frame_options']) {
            $response->setHeader('X-Frame-Options', self::$config['x_frame_options']);
        }

        // X-Content-Type-Options
        if (self::$config['x_content_type_options']) {
            $response->setHeader('X-Content-Type-Options', self::$config['x_content_type_options']);
        }

        // X-XSS-Protection
        if (self::$config['x_xss_protection']) {
            $response->setHeader('X-XSS-Protection', self::$config['x_xss_protection']);
        }

        // Strict-Transport-Security (HTTPS only)
        if ($this->isSecure($request) && self::$config['strict_transport_security']) {
            $response->setHeader('Strict-Transport-Security', self::$config['strict_transport_security']);
        }

        // Referrer-Policy
        if (self::$config['referrer_policy']) {
            $response->setHeader('Referrer-Policy', self::$config['referrer_policy']);
        }

        // Content-Security-Policy
        if (self::$config['content_security_policy']) {
            $response->setHeader('Content-Security-Policy', self::$config['content_security_policy']);
        }

        // Permissions-Policy
        if (self::$config['permissions_policy']) {
            $response->setHeader('Permissions-Policy', self::$config['permissions_policy']);
        }

        // Cross-Origin-Embedder-Policy
        if (self::$config['cross_origin_embedder_policy']) {
            $response->setHeader('Cross-Origin-Embedder-Policy', self::$config['cross_origin_embedder_policy']);
        }

        // Cross-Origin-Opener-Policy
        if (self::$config['cross_origin_opener_policy']) {
            $response->setHeader('Cross-Origin-Opener-Policy', self::$config['cross_origin_opener_policy']);
        }

        // Cross-Origin-Resource-Policy
        if (self::$config['cross_origin_resource_policy']) {
            $response->setHeader('Cross-Origin-Resource-Policy', self::$config['cross_origin_resource_policy']);
        }

        // Expect-CT
        if (self::$config['expect_ct']) {
            $response->setHeader('Expect-CT', self::$config['expect_ct']);
        }

        // Feature-Policy (legacy)
        if (self::$config['feature_policy']) {
            $response->setHeader('Feature-Policy', self::$config['feature_policy']);
        }
    }

    private function isSecure(Request $request): bool
    {
        return $request->isSecure() || 
               $request->header('X-Forwarded-Proto') === 'https' ||
               $request->header('X-Forwarded-Ssl') === 'on';
    }

    public static function middleware(): callable
    {
        return function($request, $next) {
            $middleware = new self();
            return $middleware->handle($request, $next);
        };
    }

    public static function basic(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'referrer_policy' => 'strict-origin-when-cross-origin',
        ]);
        return $instance;
    }

    public static function strict(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains; preload',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => self::generateStrictCSP(),
            'permissions_policy' => self::generateStrictPermissionsPolicy(),
            'cross_origin_embedder_policy' => 'require-corp',
            'cross_origin_opener_policy' => 'same-origin',
            'cross_origin_resource_policy' => 'same-origin',
        ]);
        return $instance;
    }

    public static function api(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => self::generateApiCSP(),
            'permissions_policy' => self::generateApiPermissionsPolicy(),
            'cross_origin_embedder_policy' => 'require-corp',
            'cross_origin_opener_policy' => 'same-origin',
        ]);
        return $instance;
    }

    public static function web(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'SAMEORIGIN',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => self::generateWebCSP(),
            'permissions_policy' => self::generateWebPermissionsPolicy(),
            'cross_origin_embedder_policy' => 'require-corp',
            'cross_origin_opener_policy' => 'same-origin',
        ]);
        return $instance;
    }

    public static function development(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => self::generateDevelopmentCSP(),
        ]);
        return $instance;
    }

    public static function production(): self
    {
        $instance = new self();
        $instance->setConfig([
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains; preload',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => self::generateProductionCSP(),
            'permissions_policy' => self::generateProductionPermissionsPolicy(),
            'cross_origin_embedder_policy' => 'require-corp',
            'cross_origin_opener_policy' => 'same-origin',
            'cross_origin_resource_policy' => 'same-origin',
            'expect_ct' => 'max-age=86400, enforce, report-uri="https://yourdomain.com/ct-report"',
        ]);
        return $instance;
    }

    private static function generateStrictCSP(): string
    {
        return "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: https:; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "media-src 'self'; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'; " .
               "upgrade-insecure-requests";
    }

    private static function generateApiCSP(): string
    {
        return "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data:; " .
               "font-src 'self'; " .
               "connect-src 'self' https:; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'";
    }

    private static function generateWebCSP(): string
    {
        return "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self' https:; " .
               "media-src 'self' https:; " .
               "object-src 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'self'";
    }

    private static function generateDevelopmentCSP(): string
    {
        return "default-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https:; " .
               "connect-src 'self' https: ws: wss:; " .
               "media-src 'self' https:; " .
               "object-src 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'self'";
    }

    private static function generateProductionCSP(): string
    {
        return "default-src 'self'; " .
               "script-src 'self'; " .
               "style-src 'self' https://fonts.googleapis.com; " .
               "img-src 'self' data: https://cdn.yourdomain.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self' https:; " .
               "media-src 'self' https:; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'; " .
               "upgrade-insecure-requests; " .
               "block-all-mixed-content";
    }

    private static function generateStrictPermissionsPolicy(): string
    {
        return "geolocation=(), " .
               "microphone=(), " .
               "camera=(), " .
               "payment=(), " .
               "usb=(), " .
               "magnetometer=(), " .
               "gyroscope=(), " .
               "accelerometer=(), " .
               "ambient-light-sensor=(), " .
               "autoplay=(), " .
               "encrypted-media=(), " .
               "fullscreen=(), " .
               "picture-in-picture=()";
    }

    private static function generateApiPermissionsPolicy(): string
    {
        return "geolocation=(), " .
               "microphone=(), " .
               "camera=(), " .
               "payment=(), " .
               "usb=(), " .
               "magnetometer=(), " .
               "gyroscope=(), " .
               "accelerometer=(), " .
               "ambient-light-sensor=()";
    }

    private static function generateWebPermissionsPolicy(): string
    {
        return "geolocation=self, " .
               "microphone=self, " .
               "camera=self, " .
               "payment=self, " .
               "usb=(), " .
               "magnetometer=(), " .
               "gyroscope=(), " .
               "accelerometer=(), " .
               "ambient-light-sensor=(), " .
               "autoplay=self, " .
               "encrypted-media=self, " .
               "fullscreen=self, " .
               "picture-in-picture=self";
    }

    private static function generateProductionPermissionsPolicy(): string
    {
        return "geolocation=self, " .
               "microphone=self, " .
               "camera=self, " .
               "payment=self, " .
               "usb=(), " .
               "magnetometer=(), " .
               "gyroscope=(), " .
               "accelerometer=(), " .
               "ambient-light-sensor=(), " .
               "autoplay=self, " .
               "encrypted-media=self, " .
               "fullscreen=self, " .
               "picture-in-picture=self";
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
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => null,
            'permissions_policy' => null,
            'cross_origin_embedder_policy' => null,
            'cross_origin_opener_policy' => null,
            'cross_origin_resource_policy' => null,
            'expect_ct' => null,
            'feature_policy' => null,
        ];
    }

    // Method to validate CSP policy
    public static function validateCsp(string $csp): array
    {
        $errors = [];
        
        // Check for common CSP issues
        if (str_contains($csp, 'unsafe-inline') && str_contains($csp, 'unsafe-eval')) {
            $errors[] = 'CSP contains both unsafe-inline and unsafe-eval, which is not recommended';
        }
        
        if (str_contains($csp, '*') && !str_contains($csp, 'upgrade-insecure-requests')) {
            $errors[] = 'CSP uses wildcard (*) without upgrade-insecure-requests';
        }
        
        if (!str_contains($csp, 'default-src')) {
            $errors[] = 'CSP should include default-src directive';
        }
        
        return $errors;
    }

    // Method to get current security headers
    public static function getSecurityHeaders(): array
    {
        $headers = [];
        
        foreach (self::$config as $key => $value) {
            if ($value) {
                $headerName = self::convertToHeaderName($key);
                $headers[$headerName] = $value;
            }
        }
        
        return $headers;
    }

    private static function convertToHeaderName(string $key): string
    {
        $mapping = [
            'x_frame_options' => 'X-Frame-Options',
            'x_content_type_options' => 'X-Content-Type-Options',
            'x_xss_protection' => 'X-XSS-Protection',
            'strict_transport_security' => 'Strict-Transport-Security',
            'referrer_policy' => 'Referrer-Policy',
            'content_security_policy' => 'Content-Security-Policy',
            'permissions_policy' => 'Permissions-Policy',
            'cross_origin_embedder_policy' => 'Cross-Origin-Embedder-Policy',
            'cross_origin_opener_policy' => 'Cross-Origin-Opener-Policy',
            'cross_origin_resource_policy' => 'Cross-Origin-Resource-Policy',
            'expect_ct' => 'Expect-CT',
            'feature_policy' => 'Feature-Policy',
        ];
        
        return $mapping[$key] ?? $key;
    }

    // Method to add custom security header
    public static function addHeader(string $name, string $value): void
    {
        $key = self::convertToConfigKey($name);
        self::$config[$key] = $value;
    }

    private static function convertToConfigKey(string $header): string
    {
        $mapping = [
            'X-Frame-Options' => 'x_frame_options',
            'X-Content-Type-Options' => 'x_content_type_options',
            'X-XSS-Protection' => 'x_xss_protection',
            'Strict-Transport-Security' => 'strict_transport_security',
            'Referrer-Policy' => 'referrer_policy',
            'Content-Security-Policy' => 'content_security_policy',
            'Permissions-Policy' => 'permissions_policy',
            'Cross-Origin-Embedder-Policy' => 'cross_origin_embedder_policy',
            'Cross-Origin-Opener-Policy' => 'cross_origin_opener_policy',
            'Cross-Origin-Resource-Policy' => 'cross_origin_resource_policy',
            'Expect-CT' => 'expect_ct',
            'Feature-Policy' => 'feature_policy',
        ];
        
        return $mapping[$header] ?? strtolower(str_replace('-', '_', $header));
    }

    // Method to remove security header
    public static function removeHeader(string $name): void
    {
        $key = self::convertToConfigKey($name);
        unset(self::$config[$key]);
    }
}
