<?php

declare(strict_types=1);

namespace App\Core\Cache;

class MemcacheDriver implements CacheDriverInterface
{
    private static ?\Memcached $connection = null;
    private static array $config = [
        'servers' => [
            ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1],
        ],
        'prefix' => 'cache:',
        'compression' => true,
        'serializer' => 'php',
        'persistent_id' => null,
        'connect_timeout' => 2.0,
        'retry_timeout' => 1.0,
        'send_timeout' => 1.0,
        'receive_timeout' => 1.0,
        'retry_interval' => 100,
        'max_retries' => 3,
        'failure_callback' => null,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function connect(): \Memcached
    {
        if (self::$connection) {
            return self::$connection;
        }

        try {
            $memcached = new \Memcached();
            
            $servers = [];
            foreach (self::$config['servers'] as $server) {
                $servers[] = $server['host'] . ':' . $server['port'];
            }

            $memcached->setServers($servers);
            
            $memcached->setOption(\Memcached::OPT_COMPRESSION, self::$config['compression']);
            $memcached->setOption(\Memcached::OPT_SERIALIZER, self::$config['serializer']);
            $memcached->setOption(\Memcached::OPT_PREFIX_KEY, self::$config['prefix']);
            $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, self::$config['connect_timeout']);
            $memcached->setOption(\Memcached::OPT_RETRY_TIMEOUT, self::$config['retry_timeout']);
            $memcached->setOption(\Memcached::OPT_SEND_TIMEOUT, self::$config['send_timeout']);
            $memcached->setOption(\Memcached::OPT_RECEIVE_TIMEOUT, self::$config['receive_timeout']);
            $memcached->setOption(\Memcached::OPT_RETRY_INTERVAL, self::$config['retry_interval']);
            $memcached->setOption(\Memcached::OPT_MAX_RETRIES, self::$config['max_retries']);
            
            if (self::$config['failure_callback']) {
                $memcached->setOption(\Memcached::OPT_FAILURE_CALLBACK, self::$config['failure_callback']);
            }

            if (self::$config['persistent_id']) {
                $memcached->setOption(\Memcached::OPT_PERSISTENT_ID, self::$config['persistent_id']);
            }

            self::$connection = $memcached;
            
            return $memcached;
        } catch (\Exception $e) {
            throw new \RuntimeException("Memcached connection failed: " . $e->getMessage());
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $memcached = self::connect();
            $value = $memcached->get($key);
            
            if ($value === false) {
                return $default;
            }

            return self::unserialize($value);
        } catch (\Exception $e) {
            Log::error('Memcached get error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    public static function put(string $key, mixed $value, int $ttl = 0): bool
    {
        try {
            $memcached = self::connect();
            $serialized = self::serialize($value);
            
            if ($ttl > 0) {
                return $memcached->set($key, $serialized, $ttl);
            } else {
                return $memcached->set($key, $serialized);
            }
        } catch (\Exception $e) {
            Log::error('Memcached put error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function forget(string $key): bool
    {
        try {
            $memcached = self::connect();
            return $memcached->delete($key);
        } catch (\Exception $e) {
            Log::error('Memcached forget error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function flush(): bool
    {
        try {
            $memcached = self::connect();
            return $memcached->flush();
        } catch (\Exception $e) {
            Log::error('Memcached flush error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function add(string $key, mixed $value, int $ttl = 0): bool
    {
        try {
            $memcached = self::connect();
            $serialized = self::serialize($value);
            
            return $memcached->add($key, $serialized, $ttl);
        } catch (\Exception $e) {
            Log::error('Memcached add error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
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

    public static function increment(string $key, int $value = 1): int
    {
        try {
            $memcached = self::connect();
            return $memcached->increment($key, $value);
        } catch (\Exception $e) {
            Log::error('Memcached increment error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function decrement(string $key, int $value = 1): int
    {
        try {
            $memcached = self::connect();
            return $memcached->decrement($key, $value);
        } catch (\Exception $e) {
            Log::error('Memcached decrement error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function exists(string $key): bool
    {
        try {
            $memcached = self::connect();
            return $memcached->get($key) !== false;
        } catch (\Exception $e) {
            Log::error('Memcached exists error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function touch(string $key, int $ttl = 0): bool
    {
        try {
            $memcached = self::connect();
            return $memcached->touch($key, $ttl);
        } catch (\Exception $e) {
            $error('Memcached touch error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function getStats(): array
    {
        try {
            $memcached = self::connect();
            return $memcached->getStats();
        } catch (\Exception $e) {
            Log::error('Memcached getStats error', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function getVersion(): string
    {
        try {
            $memcached = self::connect();
            return $memcached->getVersion();
        } catch (\Exception $e) {
            Log::error('Memcached getVersion error', [
                'error' => $e->getMessage()
            ]);
            return 'unknown';
        }
    }

    public static function getServerList(): array
    {
        try {
            $memcached = self::connect();
            return $memcached->getServerList();
        } catch (\Exception $e) {
            Log::error('Memcached getServerList error', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function addServer(string $host, int $port, int $weight = 1): bool
    {
        try {
            $memcached = self::connect();
            return $memcached->addServer($host, $port, $weight);
        } catch (\Exception $e) {
            Log::error('Memcached addServer error', [
                'host' => $host,
                'port' => $port,
                'weight' => $weight,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function setServers(array $servers): void
    {
        try {
            $memcached = self::connect();
            $serverList = [];
            
            foreach ($servers as $server) {
                $serverList[] = $server['host'] . ':' . $server['port'];
            }
            
            $memcached->setServers($serverList);
        } catch (\Exception $e) {
            Log::error('Memcached setServers error', [
                'servers' => $servers,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function disconnect(): void
    {
        if (self::$connection) {
            self::$connection->quit();
            self::$connection = null;
        }
    }

    private static function serialize(mixed $value): string
    {
        return serialize($value);
    }

    private static function unserialize(string $value): mixed
    {
        return unserialize($value);
    }

    public static function clear(): void
    {
        self::flush();
        self::disconnect();
    }
}
