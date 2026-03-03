<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Log;

class QueryCache
{
    private static array $cache = [];
    private static array $config = [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'max_size' => 1000,
        'key_prefix' => 'query_cache:',
        'hash_key' => true,
    ];

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

    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!self::isEnabled()) {
            return $callback();
        }

        $cacheKey = self::generateKey($key);
        
        if (self::has($cacheKey)) {
            return self::get($cacheKey);
        }

        $result = $callback();
        self::put($cacheKey, $result, $ttl ?? self::$config['ttl']);
        
        return $result;
    }

    public static function get(string $key): mixed
    {
        if (!self::isEnabled()) {
            return null;
        }

        $cacheKey = self::generateKey($key);
        
        if (!self::has($cacheKey)) {
            return null;
        }

        $item = self::$cache[$cacheKey];
        
        if ($item['expires'] < time()) {
            self::forget($cacheKey);
            return null;
        }

        return $item['data'];
    }

    public static function put(string $key, mixed $value, ?int $ttl = null): void
    {
        if (!self::self::isEnabled()) {
            return;
        }

        $cacheKey = self::generateKey($key);
        $expires = time() + ($ttl ?? self::$config['ttl']);
        
        self::$cache[$cacheKey] = [
            'data' => $value,
            'expires' => $expires,
            'hits' => 0,
            'created' => time(),
        ];

        self::manageSize();
    }

    public static function has(string $key): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $cacheKey = self::generateKey($key);
        
        return isset(self::$cache[$cacheKey]) && self::$cache[$cacheKey]['expires'] > time();
    }

    public static function forget(string $key): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $cacheKey = self::generateKey($key);
        unset(self::$cache[$cacheKey]);
    }

    public static function flush(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$cache = [];
    }

    public static function clear(): void
    {
        self::flush();
    }

    public static function tags(array $tags): self
    {
        return new self($tags);
    }

    public static function increment(string $key, int $value = 1): int
    {
        if (!self::has($key)) {
            self::put($key, 0);
        }

        $cacheKey = self::generateKey($key);
        self::$cache[$cacheKey]['data'] += $value;
        self::$cache[$cacheKey]['hits']++;
        
        return self::$cache[$cacheKey]['data'];
    }

    public static function decrement(string $key, int $value = 1): int
    {
        if (!self::has($key)) {
            self::put($key, 0);
        }

        $cacheKey = self::generateKey($key);
        self::$cache[$cacheKey]['data'] -= $value;
        self::$cache[$cacheKey]['hits']++;
        
        return self::$cache[$cacheKey]['data'];
    }

    public static function getHits(string $key): int
    {
        if (!self::has($key)) {
            return 0;
        }

        $cacheKey = self::generateKey($key);
        return self::$cache[$cacheKey]['hits'] ?? 0;
    }

    public static function getCreated(string $key): ?int
    {
        if (!self::has($key)) {
            return null;
        }

        $cacheKey = self::getCacheKey($key);
        return self::$cache[$cacheKey]['created'] ?? null;
    }

    public static function getExpires(string $key): ?int
    {
        if (!self::has($key)) {
            return null;
        }

        $cacheKey = self::generateKey($key);
        return self::$cache[$cacheKey]['expires'] ?? null;
    }

    public static function getStats(): array
    {
        $stats = [
            'total_items' => count(self::$cache),
            'enabled' => self::$config['enabled'],
            'ttl' => self::$config['ttl'],
            'max_size' => self::$config['max_size'],
            'hits' => 0,
            'misses' => 0,
            'hit_rate' => 0,
        ];

        foreach (self::$cache as $item) {
            $stats['hits'] += $item['hits'];
            $stats['misses'] += ($item['hits'] === 0 ? 1 : 0);
        }

        if ($stats['hits'] + $stats['misses'] > 0) {
            $stats['hit_rate'] = ($stats['hits'] / ($stats['hits'] + $stats['misses'])) * 100;
        }

        return $stats;
    }

    public static function getCacheInfo(string $key): ?array
    {
        if (!self::has($key)) {
            return null;
        }

        $cacheKey = self::generateKey($key);
        return self::$cache[$cacheKey] ?? null;
    }

    public static function warmup(array $keys): void
    {
        foreach ($keys as $key) {
            self::get($key);
        }
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

    private static function generateKey(string $key): string
    {
        $key = self::$config['key_prefix'] . $key;
        
        if (self::$config['hash_key']) {
            $key = md5($key);
        }
        
        return $key;
    }

    private static function getCacheKey(string $key): string
    {
        $key = self::$config['key_prefix'] . $key;
        
        if (self::$config['hash_key']) {
            $key = md5($key);
        }
        
        return $key;
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

    public static function __callStatic(string $method, array $arguments): mixed
    {
        if ($method === 'tags') {
            return new self($arguments[0] ?? []);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}

class QueryCacheTags
{
    private array $tags = [];

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        QueryCache::put($this->getTaggedKey($key), $value, $ttl);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return QueryCache::get($this->getTaggedKey($key), $default);
    }

    public function forget(string $key): void
    {
        QueryCache::forget($this->getTaggedKey($key));
    }

    public function flush(): void
    {
        foreach ($this->tags as $tag) {
            QueryCache::flush();
        }
    }

    private function getTaggedKey(string $key): string
    {
        if (empty($this->tags)) {
            return $key;
        }

        return implode(':', $this->tags) . ':' . $key;
    }
}
