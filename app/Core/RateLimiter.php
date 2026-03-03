<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Cache;

class RateLimiter
{
    private static array $config = [
        'driver' => 'cache',
        'key_prefix' => 'rate_limit:',
        'default_limit' => 60,
        'default_window' => 60, // seconds
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function attempt(string $key, int $maxAttempts = 5, int $seconds = 60): bool
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        Cache::put($cacheKey, $attempts + 1, $seconds);
        
        return true;
    }

    public static function hits(string $key): int
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        return Cache::get($cacheKey, 0);
    }

    public static function remaining(string $key, int $maxAttempts): int
    {
        $hits = self::hits($key);
        return max(0, $maxAttempts - $hits);
    }

    public static function reset(string $key): void
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        Cache::forget($cacheKey);
    }

    public static function clear(): void
    {
        // This would need to be implemented based on the cache driver
        // For now, we'll use a tag-based approach if available
        if (method_exists(Cache::class, 'tags')) {
            Cache::tags(['rate_limit'])->flush();
        }
    }

    public static function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return self::hits($key) >= $maxAttempts;
    }

    public static function availableIn(string $key, int $maxAttempts): int
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        $ttl = Cache::get($cacheKey . '_ttl', 0);
        
        return max(0, $ttl - time());
    }

    public static function block(string $key, int $seconds): void
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        Cache::put($cacheKey . '_blocked', true, $seconds);
    }

    public static function isBlocked(string $key): bool
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        return Cache::get($cacheKey . '_blocked', false);
    }

    public static function blockFor(string $key, int $maxAttempts, int $decaySeconds): void
    {
        if (self::tooManyAttempts($key, $maxAttempts)) {
            self::block($key, $decaySeconds);
        }
    }

    // Advanced rate limiting methods
    public static function slidingWindow(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $cacheKey = self::$config['key_prefix'] . $key;
        $now = time();
        
        // Get existing attempts
        $attempts = Cache::get($cacheKey, []);
        
        // Remove old attempts outside the window
        $attempts = array_filter($attempts, function($timestamp) use ($now, $windowSeconds) {
            return $timestamp > ($now - $windowSeconds);
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        // Add current attempt
        $attempts[] = $now;
        Cache::put($cacheKey, $attempts, $windowSeconds);
        
        return true;
    }

    public static function tokenBucket(string $key, int $capacity, float $refillRate): bool
    {
        $cacheKey = self::$config['key_prefix'] . 'bucket:' . $key;
        $now = microtime(true);
        
        $bucket = Cache::get($cacheKey, [
            'tokens' => $capacity,
            'last_refill' => $now,
        ]);
        
        // Calculate tokens to add
        $timePassed = $now - $bucket['last_refill'];
        $tokensToAdd = $timePassed * $refillRate;
        
        $bucket['tokens'] = min($capacity, $bucket['tokens'] + $tokensToAdd);
        $bucket['last_refill'] = $now;
        
        // Check if we have tokens
        if ($bucket['tokens'] >= 1) {
            $bucket['tokens'] -= 1;
            Cache::put($cacheKey, $bucket, 3600); // Store for 1 hour
            return true;
        }
        
        Cache::put($cacheKey, $bucket, 3600);
        return false;
    }

    public static function fixedWindow(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $windowStart = floor(time() / $windowSeconds) * $windowSeconds;
        $cacheKey = self::$config['key_prefix'] . 'window:' . $key . ':' . $windowStart;
        
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        Cache::put($cacheKey, $attempts + 1, $windowSeconds);
        return true;
    }

    // API rate limiting helpers
    public static function api(string $endpoint, int $maxRequests = 100, int $windowSeconds = 3600): bool
    {
        $key = 'api:' . $endpoint;
        return self::slidingWindow($key, $maxRequests, $windowSeconds);
    }

    public static function auth(string $identifier, int $maxAttempts = 5, int $decaySeconds = 300): bool
    {
        $key = 'auth:' . $identifier;
        
        if (!self::attempt($key, $maxAttempts, $decaySeconds)) {
            self::block($key, $decaySeconds);
            return false;
        }
        
        return true;
    }

    public static function login(string $ip, int $maxAttempts = 5, int $decaySeconds = 900): bool
    {
        $key = 'login:' . $ip;
        return self::auth($key, $maxAttempts, $decaySeconds);
    }

    public static function passwordReset(string $email, int $maxAttempts = 3, int $decaySeconds = 3600): bool
    {
        $key = 'password_reset:' . $email;
        return self::auth($key, $maxAttempts, $decaySeconds);
    }

    public static function registration(string $ip, int $maxAttempts = 3, int $decaySeconds = 3600): bool
    {
        $key = 'registration:' . $ip;
        return self::auth($key, $maxAttempts, $decaySeconds);
    }

    public static function contact(string $ip, int $maxAttempts = 5, int $decaySeconds = 1800): bool
    {
        $key = 'contact:' . $ip;
        return self::auth($key, $maxAttempts, $decaySeconds);
    }

    // Middleware helpers
    public static function middleware(string $key, int $maxAttempts, int $seconds): callable
    {
        return function($request, $next) use ($key, $maxAttempts, $seconds) {
            $identifier = self::getIdentifier($request, $key);
            
            if (!self::attempt($identifier, $maxAttempts, $seconds)) {
                $response = new Response();
                return $response->json([
                    'error' => 'Too Many Attempts',
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'retry_after' => self::availableIn($identifier, $maxAttempts)
                ], 429);
            }
            
            return $next($request);
        };
    }

    public static function apiMiddleware(string $endpoint, int $maxRequests, int $seconds): callable
    {
        return function($request, $next) use ($endpoint, $maxRequests, $seconds) {
            if (!self::api($endpoint, $maxRequests, $seconds)) {
                $response = new Response();
                return $response->json([
                    'error' => 'Rate Limit Exceeded',
                    'message' => 'API rate limit exceeded',
                    'limit' => $maxRequests,
                    'window' => $seconds
                ], 429);
            }
            
            return $next($request);
        };
    }

    private static function getIdentifier($request, string $key): string
    {
        return match ($key) {
            'ip' => $request->getIp(),
            'user' => $request->getRouteParam('id') ?? $request->get('user_id'),
            'email' => $request->post('email'),
            'endpoint' => $request->getPath(),
            default => $key,
        };
    }

    // Statistics and monitoring
    public static function getStats(string $key): array
    {
        $cacheKey = self::$config['key_prefix'] . 'stats:' . $key;
        return Cache::get($cacheKey, [
            'total_requests' => 0,
            'blocked_requests' => 0,
            'last_request' => null,
        ]);
    }

    public static function recordRequest(string $key): void
    {
        $stats = self::getStats($key);
        $stats['total_requests']++;
        $stats['last_request'] = time();
        
        $cacheKey = self::$config['key_prefix'] . 'stats:' . $key;
        Cache::put($cacheKey, $stats, 86400); // Store for 24 hours
    }

    public static function recordBlock(string $key): void
    {
        $stats = self::getStats($key);
        $stats['blocked_requests']++;
        
        $cacheKey = self::$config['key_prefix'] . 'stats:' . $key;
        Cache::put($cacheKey, $stats, 86400);
    }

    public static function getConfig(): array
    {
        return self::$config;
    }
}
