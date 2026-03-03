<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    private static ?string $driver = null;
    private static array $config = [
        'file' => [
            'path' => __DIR__ . '/../../storage/cache',
            'extension' => '.cache'
        ],
        'session' => [
            'prefix' => 'cache_'
        ],
        'memory' => []
    ];
    
    private static array $memoryCache = [];

    public static function configure(array $config): void
    {
        self::$config = array_merge_recursive(self::$config, $config);
    }

    public static function driver(string $driver): void
    {
        self::$driver = $driver;
    }

    private static function getDriver(): string
    {
        return self::$driver ?? 'file';
    }

    public static function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        $driver = self::getDriver();
        
        return match ($driver) {
            'file' => self::putFile($key, $value, $ttl),
            'session' => self::putSession($key, $value, $ttl),
            'memory' => self::putMemory($key, $value, $ttl),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$driver}")
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $driver = self::getDriver();
        
        return match ($driver) {
            'file' => self::getFile($key, $default),
            'session' => self::getSession($key, $default),
            'memory' => self::getMemory($key, $default),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$driver}")
        };
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::put($key, $value, $ttl);
        
        return $value;
    }

    public static function forget(string $key): bool
    {
        $driver = self::getDriver();
        
        return match ($driver) {
            'file' => self::forgetFile($key),
            'session' => self::forgetSession($key),
            'memory' => self::forgetMemory($key),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$driver}")
        };
    }

    public static function flush(): bool
    {
        $driver = self::getDriver();
        
        return match ($driver) {
            'file' => self::flushFile(),
            'session' => self::flushSession(),
            'memory' => self::flushMemory(),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$driver}")
        };
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function increment(string $key, int $value = 1): int
    {
        $current = self::get($key, 0);
        $current = is_numeric($current) ? (int) $current : 0;
        $current += $value;
        
        self::put($key, $current);
        
        return $current;
    }

    public static function decrement(string $key, int $value = 1): int
    {
        return self::increment($key, -$value);
    }

    public static function add(string $key, mixed $value, int $ttl = 3600): bool
    {
        if (self::has($key)) {
            return false;
        }
        
        return self::put($key, $value, $ttl);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);
        
        return $value;
    }

    public static function many(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = self::get($key);
        }
        
        return $results;
    }

    public static function putMany(array $values, int $ttl = 3600): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            $success = self::put($key, $value, $ttl) && $success;
        }
        
        return $success;
    }

    public static function forgetMany(array $keys): bool
    {
        $success = true;
        
        foreach ($keys as $key) {
            $success = self::forget($key) && $success;
        }
        
        return $success;
    }

    // File Driver Methods
    private static function putFile(string $key, mixed $value, int $ttl): bool
    {
        $path = self::$config['file']['path'];
        $extension = self::$config['file']['extension'];
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $filename = $path . '/' . md5($key) . $extension;
        $data = [
            'value' => serialize($value),
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filename, json_encode($data)) !== false;
    }

    private static function getFile(string $key, mixed $default): mixed
    {
        $path = self::$config['file']['path'];
        $extension = self::$config['file']['extension'];
        $filename = $path . '/' . md5($key) . $extension;
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return $default;
        }
        
        $data = json_decode($content, true);
        if (!$data || !isset($data['value'], $data['expires'])) {
            return $default;
        }
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return $default;
        }
        
        return unserialize($data['value']);
    }

    private static function forgetFile(string $key): bool
    {
        $path = self::$config['file']['path'];
        $extension = self::$config['file']['extension'];
        $filename = $path . '/' . md5($key) . $extension;
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    private static function flushFile(): bool
    {
        $path = self::$config['file']['path'];
        $extension = self::$config['file']['extension'];
        
        if (!is_dir($path)) {
            return true;
        }
        
        $files = glob($path . '/*' . $extension);
        $success = true;
        
        foreach ($files as $file) {
            $success = unlink($file) && $success;
        }
        
        return $success;
    }

    // Session Driver Methods
    private static function putSession(string $key, mixed $value, int $ttl): bool
    {
        Session::start();
        
        $prefix = self::$config['session']['prefix'];
        $data = [
            'value' => serialize($value),
            'expires' => time() + $ttl
        ];
        
        Session::put($prefix . $key, $data);
        
        return true;
    }

    private static function getSession(string $key, mixed $default): mixed
    {
        Session::start();
        
        $prefix = self::$config['session']['prefix'];
        $data = Session::get($prefix . $key);
        
        if (!$data || !isset($data['value'], $data['expires'])) {
            return $default;
        }
        
        if ($data['expires'] < time()) {
            Session::forget($prefix . $key);
            return $default;
        }
        
        return unserialize($data['value']);
    }

    private static function forgetSession(string $key): bool
    {
        Session::start();
        
        $prefix = self::$config['session']['prefix'];
        Session::forget($prefix . $key);
        
        return true;
    }

    private static function flushSession(): bool
    {
        Session::start();
        
        $prefix = self::$config['session']['prefix'];
        $session = Session::all();
        
        foreach ($session as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                Session::forget($key);
            }
        }
        
        return true;
    }

    // Memory Driver Methods
    private static function putMemory(string $key, mixed $value, int $ttl): bool
    {
        self::$memoryCache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return true;
    }

    private static function getMemory(string $key, mixed $default): mixed
    {
        if (!isset(self::$memoryCache[$key])) {
            return $default;
        }
        
        $data = self::$memoryCache[$key];
        
        if ($data['expires'] < time()) {
            unset(self::$memoryCache[$key]);
            return $default;
        }
        
        return $data['value'];
    }

    private static function forgetMemory(string $key): bool
    {
        unset(self::$memoryCache[$key]);
        return true;
    }

    private static function flushMemory(): bool
    {
        self::$memoryCache = [];
        return true;
    }

    public static function tags(array $tags): self
    {
        return new class($tags) {
            private array $tags;
            
            public function __construct(array $tags)
            {
                $this->tags = $tags;
            }
            
            public function put(string $key, mixed $value, int $ttl = 3600): bool
            {
                $taggedKey = implode(':', $this->tags) . ':' . $key;
                return Cache::put($taggedKey, $value, $ttl);
            }
            
            public function get(string $key, mixed $default = null): mixed
            {
                $taggedKey = implode(':', $this->tags) . ':' . $key;
                return Cache::get($taggedKey, $default);
            }
            
            public function flush(): bool
            {
                $prefix = implode(':', $this->tags) . ':';
                $driver = Cache::getDriver();
                
                if ($driver === 'file') {
                    $path = Cache::$config['file']['path'];
                    $extension = Cache::$config['file']['extension'];
                    $files = glob($path . '/*' . $extension);
                    
                    foreach ($files as $file) {
                        $content = file_get_contents($file);
                        if ($content && str_contains($content, $prefix)) {
                            unlink($file);
                        }
                    }
                }
                
                return true;
            }
        };
    }

    public static function lock(string $key, int $ttl = 10): bool
    {
        $lockKey = 'lock:' . $key;
        
        if (self::add($lockKey, true, $ttl)) {
            return true;
        }
        
        return false;
    }

    public static function unlock(string $key): bool
    {
        $lockKey = 'lock:' . $key;
        return self::forget($lockKey);
    }
}
