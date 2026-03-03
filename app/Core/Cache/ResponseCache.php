<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Response;
use App\Core\Request;
use App\Core\Log;

class ResponseCache
{
    private static array $config = [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'key_prefix' => 'response_cache:',
        'vary_headers' => ['Accept', 'Accept-Language', 'Cookie'],
        'skip_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
        'skip_patterns' => [],
        'max_size' => 1000,
        'compress' => true,
        'etag' => true,
        'last_modified' => true,
    ];

    private static array $cache = [];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function enable(): void
    {
        self::$config['enabled'] = true;
    }

    public static function disable(): void
    {
        self::$config['enabled'] = false;
    }

    public static function isEnabled(): bool
    {
        return self::$config['enabled'];
    }

    public static function remember(Request $request, callable $callback, int $ttl = null): Response
    {
        if (!self::shouldCache($request)) {
            return $callback();
        }

        $key = self::generateKey($request);
        
        if (self::has($key)) {
            return self::get($key, $request);
        }

        $response = $callback();
        
        if (self::shouldStoreResponse($response)) {
            self::put($key, $response, $ttl ?? self::$config['ttl']);
        }
        
        return $response;
    }

    public static function get(string $key, ?Request $request = null): ?Response
    {
        if (!self::has($key)) {
            return null;
        }

        $item = self::$cache[$key];
        
        if ($item['expires'] < time()) {
            self::forget($key);
            return null;
        }

        // Check if response is still valid
        if ($request && self::isResponseStale($item['response'], $request)) {
            self::forget($key);
            return null;
        }

        $response = $item['response'];
        
        // Add cache headers
        if ($request) {
            self::addCacheHeaders($response, $item);
        }
        
        return $response;
    }

    public static function put(string $key, Response $response, ?int $ttl = null): void
    {
        if (!self::shouldStoreResponse($response)) {
            return;
        }

        $expires = time() + ($ttl ?? self::$config['ttl']);
        
        self::$cache[$key] = [
            'response' => $response,
            'expires' => $expires,
            'created' => time(),
            'etag' => self::generateEtag($response),
            'last_modified' => self::generateLastModified($response),
        ];

        self::manageSize();
    }

    public static function has(string $key): bool
    {
        return isset(self::$cache[$key]) && self::$cache[$key]['expires'] > time();
    }

    public static function forget(string $key): void
    {
        unset(self::$cache[$key]);
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    public static function clear(): void
    {
        self::flush();
    }

    public static function prune(): void
    {
        $now = time();
        
        foreach (self::$cache as $key => $item) {
            if ($item['expires'] < $now) {
                unset(self::$cache[$key]);
            }
        }
    }

    public static function invalidateByPattern(string $pattern): void
    {
        foreach (self::$cache as $key => $item) {
            if (fnmatch($pattern, $key)) {
                unset(self::$cache[$key]);
            }
        }
    }

    public static function invalidateByTag(string $tag): void
    {
        foreach (self::$cache as $key => $item) {
            if (str_contains($key, 'tag:' . $tag)) {
                unset(self::$cache[$key]);
            }
        }
    }

    public static function getStats(): array
    {
        $stats = [
            'total_items' => count(self::$cache),
            'enabled' => self::$config['enabled'],
            'ttl' => self::$config['ttl'],
            'max_size' => self::$config['max_size'],
            'hit_rate' => 0,
            'memory_usage' => memory_get_usage(),
        ];

        return $stats;
    }

    private static function shouldCache(Request $request): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        // Skip certain HTTP methods
        if (in_array($request->method(), self::$config['skip_methods'])) {
            return false;
        }

        // Skip certain patterns
        foreach (self::$config['skip_patterns'] as $pattern) {
            if (fnmatch($pattern, $request->path())) {
                return false;
            }
        }

        // Skip if has query parameters (except cache-specific ones)
        if (!empty($request->query()) && !self::hasCacheParams($request)) {
            return false;
        }

        return true;
    }

    private static function shouldStoreResponse(Response $response): bool
    {
        // Don't cache error responses
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        // Don't cache responses without cache headers
        if (!$response->hasHeader('Cache-Control') || 
            str_contains($response->getHeader('Cache-Control'), 'no-cache') ||
            str_contains($response->getHeader('Cache-Control'), 'no-store')) {
            return false;
        }

        return true;
    }

    private static function generateKey(Request $request): string
    {
        $key = $request->method() . ':' . $request->path();
        
        // Include vary headers
        foreach (self::$config['vary_headers'] as $header) {
            $value = $request->header($header);
            if ($value) {
                $key .= ':' . $header . ':' . $value;
            }
        }

        // Include cache-specific query parameters
        $cacheParams = self::getCacheParams($request);
        if (!empty($cacheParams)) {
            $key .= '?' . http_build_query($cacheParams);
        }

        return self::$config['key_prefix'] . md5($key);
    }

    private static function hasCacheParams(Request $request): bool
    {
        $cacheParams = self::getCacheParams($request);
        return !empty($cacheParams);
    }

    private static function getCacheParams(Request $request): array
    {
        $cacheParams = [];
        $allowedParams = ['cache', 'version', 'v'];
        
        foreach ($allowedParams as $param) {
            if ($request->has($param)) {
                $cacheParams[$param] = $request->get($param);
            }
        }
        
        return $cacheParams;
    }

    private static function isResponseStale(Response $cachedResponse, Request $request): bool
    {
        // Check If-Modified-Since
        if ($request->hasHeader('If-Modified-Since')) {
            $ifModifiedSince = strtotime($request->getHeader('If-Modified-Since'));
            $lastModified = $cachedResponse->getHeader('Last-Modified');
            
            if ($lastModified && $ifModifiedSince >= strtotime($lastModified)) {
                return true;
            }
        }

        // Check If-None-Match
        if ($request->hasHeader('If-None-Match')) {
            $ifNoneMatch = $request->getHeader('If-None-Match');
            $etag = $cachedResponse->getHeader('ETag');
            
            if ($etag && $ifNoneMatch === $etag) {
                return true;
            }
        }

        return false;
    }

    private static function addCacheHeaders(Response $response, array $cacheItem): void
    {
        // Add ETag header
        if (self::$config['etag'] && $cacheItem['etag']) {
            $response->setHeader('ETag', $cacheItem['etag']);
        }

        // Add Last-Modified header
        if (self::$config['last_modified'] && $cacheItem['last_modified']) {
            $response->setHeader('Last-Modified', $cacheItem['last_modified']);
        }

        // Add Cache-Control header
        $response->setHeader('Cache-Control', 'public, max-age=' . self::$config['ttl']);
    }

    private static function generateEtag(Response $response): string
    {
        $content = $response->getContent();
        $headers = $response->getHeaders();
        
        return '"' . md5($content . serialize($headers)) . '"';
    }

    private static function generateLastModified(Response $response): string
    {
        return gmdate('D, d M Y H:i:s T', time());
    }

    private static function manageSize(): void
    {
        if (count(self::$cache) <= self::$config['max_size']) {
            return;
        }

        // Remove oldest items
        $oldestTime = null;
        $oldestKey = null;
        
        foreach (self::$cache as $key => $item) {
            if ($oldestTime === null || $item['created'] < $oldestTime) {
                $oldestTime = $item['created'];
                $oldestKey = $key;
            }
        }

        if ($oldestKey !== null) {
            unset(self::$cache[$oldestKey]);
        }
    }

    public static function middleware(): callable
    {
        return function($request, $next) {
            if (!self::shouldCache($request)) {
                return $next($request);
            }

            $key = self::generateKey($request);
            
            // Check if we have a cached response
            if (self::has($key)) {
                $cachedResponse = self::get($key, $request);
                
                if ($cachedResponse) {
                    // Return 304 Not Modified if appropriate
                    if (self::isResponseStale($cachedResponse, $request)) {
                        return new Response('', 304);
                    }
                    
                    return $cachedResponse;
                }
            }

            // Generate response
            $response = $next($request);
            
            // Cache the response if appropriate
            if (self::shouldStoreResponse($response)) {
                self::put($key, $response);
            }
            
            return $response;
        };
    }

    public static function tag(string $tag): string
    {
        return 'tag:' . $tag;
    }

    public static function withTags(array $tags): string
    {
        return 'tags:' . implode(',', $tags);
    }

    public static function warmup(array $urls): void
    {
        foreach ($urls as $url) {
            $request = new Request([], [], [], [], [], 'GET', $url);
            $key = self::generateKey($request);
            
            if (!self::has($key)) {
                // This would need to be implemented based on your routing system
                // For now, we'll just simulate the warmup
                Log::info('Warming up cache for URL', ['url' => $url]);
            }
        }
    }

    public static function getCacheInfo(string $key): ?array
    {
        if (!self::has($key)) {
            return null;
        }

        return self::$cache[$key] ?? null;
    }

    public static function getMemoryUsage(): int
    {
        return memory_get_usage();
    }

    public static function getMemoryUsageFormatted(): string
    {
        $bytes = self::getMemoryUsage();
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
