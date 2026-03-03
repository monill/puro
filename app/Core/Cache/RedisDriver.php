<?php

declare(strict_types=1);

namespace App\Core\Cache;

class RedisDriver implements CacheDriverInterface
{
    private static ?\Redis $connection = null;
    private static array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'database' => 0,
        'prefix' => 'cache:',
        'timeout' => 2.0,
        'read_timeout' => 2.0,
        'retry_interval' => 100,
        'max_retries' => 3,
        'persistent_id' => null,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function connect(): \Redis
    {
        if (self::$connection) {
            return self::$connection;
        }

        try {
            $redis = new \Redis();
            
            $redis->connect(
                self::$config['host'],
                self::$config['port'],
                self::$config['timeout'],
                self::$config['persistent_id'],
                self::$config['retry_interval'],
                self::$config['read_timeout']
            );

            if (self::$config['password']) {
                $redis->auth(self::$config['password']);
            }

            if (self::$config['database'] > 0) {
                $redis->select(self::$config['database']);
            }

            self::$connection = $redis;
            
            return $redis;
        } catch (\Exception $e) {
            throw new \RuntimeException("Redis connection failed: " . $e->getMessage());
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $redis = self::connect();
            $value = $redis->get(self::$config['prefix'] . $key);
            
            if ($value === false) {
                return $default;
            }

            return self::unserialize($value);
        } catch (\Exception $e) {
            Log::error('Redis get error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    public static function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            $redis = self::connect();
            $serialized = self::serialize($value);
            
            if ($ttl > 0) {
                return $redis->setex(self::$config['prefix'] . $key, $ttl, $serialized);
            } else {
                return $redis->set(self::$config['prefix'] . $key, $serialized);
            }
        } catch (\Exception $e) {
            Log::error('Redis put error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function forget(string $key): bool
    {
        try {
            $redis = self::connect();
            return $redis->del(self::$config['prefix'] . $key) > 0;
        } catch (\Exception $e) {
            Log::error('Redis forget error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function flush(): bool
    {
        try {
            $redis = self::connect();
            $pattern = self::$config['prefix'] . '*';
            
            $keys = $redis->keys($pattern);
            
            if (empty($keys)) {
                return true;
            }
            
            return $redis->del($keys) > 0;
        } catch (\Exception $e) {
            Log::error('Redis flush error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function add(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            $redis = self::connect();
            $serialized = self::serialize($value);
            
            return $redis->zadd(self::$config['prefix'] . $key, time(), $serialized) > 0;
        } catch (\Exception $e) {
            Log::error('Redis add error', [
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
            $redis = self::connect();
            return $redis->incrby(self::$config['prefix'] . $key, $value);
        } catch (\Exception $e) {
            Log::error('Redis increment error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function decrement(string $key, int $value = 1): int
    {
        try {
            $redis = self::connect();
            return $redis->decrby(self::$config['prefix'] . $key, $value);
        } catch (\Exception $e) {
            Log::error('Redis decrement error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function exists(string $key): bool
    {
        try {
            $redis = self::connect();
            return $redis->exists(self::$config['prefix'] . $key);
        } catch (\Exception $e) {
            Log::error('Redis exists error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function expire(string $key, int $ttl): bool
    {
        try {
            $redis = self::connect();
            return $redis->expire(self::$config['prefix'] . $key, $ttl);
        } catch (\Exception $e) {
            Log::error('Redis expire error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function ttl(string $key): int
    {
        try {
            $redis = self::connect();
            return $redis->ttl(self::$config['prefix'] . $key);
        } catch (\Exception $e) {
            Log::error('Redis ttl error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return -1;
        }
    }

    public static function hset(string $key, string $field, mixed $value): bool
    {
        try {
            $redis = self::connect();
            return $redis->hset(self::$config['prefix'] . $key, $field, self::serialize($value));
        } catch (\Exception $e) {
            Log::error('Redis hset error', [
                'key' => $key,
                'field' => $field,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function hget(string $key, string $field, mixed $default = null): mixed
    {
        try {
            $redis = self::connect();
            $value = $redis->hget(self::$config['prefix'] . $key, $field);
            
            if ($value === false) {
                return $default;
            }

            return self::unserialize($value);
        } catch (\Exception $e) {
            Log::error('Redis hget error', [
                'key' => $key,
                'field' => $field,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    public static function hgetall(string $key): array
    {
        try {
            $redis = self::connect();
            $data = $redis->hgetall(self::$config['prefix'] . $key);
            
            $result = [];
            foreach ($data as $field => $value) {
                $result[$field] = self::unserialize($value);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Redis hgetall error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function hdel(string $key, string $field): bool
    {
        try {
            $redis = self::connect();
            return $redis->hdel(self::$config['prefix'] . $key, $field) > 0;
        } catch (\Exception $e) {
            Log::error('Redis hdel error', [
                'key' => $key,
                'field' => $field,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function lpush(string $key, mixed ...$values): int
    {
        try {
            $redis = self::connect();
            $serialized = array_map([self::class, 'serialize'], $values);
            return $redis->lpush(self::$config['prefix'] . $key, ...$serialized);
        } catch (\Exception $e) {
            Log::error('Redis lpush error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function rpush(string $key, mixed ...$values): int
    {
        try {
            $redis = self::connect();
            $serialized = array_map([self::class, 'serialize'], $values);
            return $redis->rpush(self::$config['prefix'] . $key, ...$serialized);
        } catch (\Exception $e) {
            Log::error('Redis rpush error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function lpop(string $key): mixed
    {
        try {
            $redis = self::connect();
            $value = $redis->lpop(self::$config['prefix'] . $key);
            
            if ($value === false) {
                return null;
            }

            return self::unserialize($value);
        } catch (\Exception $e) {
            Log::error('Redis lpop error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public static function rpop(string $key): mixed
    {
        try {
            $redis = self::connect();
            $value = $redis->rpop(self::$config['prefix'] . $key);
            
            if ($value === false) {
                return null;
            }

            return self::unserialize($value);
        } catch (\Exception $e) {
            Log::error('Redis rpop error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public static function lrange(string $key, int $start, int $end): array
    {
        try {
            $redis = self::connect();
            $values = $redis->lrange(self::$config['prefix'] . $key, $start, $end);
            
            return array_map([self::class, 'unserialize'], $values);
        } catch (\Exception $e) {
            Log::error('Redis lrange error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function rrange(string $key, int $start, int $end): array
    {
        try {
            $redis = self::connect();
            $values = $redis->rrange(self::$config['prefix'] . $key, $start, $end);
            
            return array_map([self::class, 'unserialize'], $values);
        } catch (\Exception $e) {
            Log::error('Redis rrange error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function llen(string $key): int
    {
        try {
            $redis = self::connect();
            return $redis->llen(self::$config['prefix'] . $key);
        } catch (\Exception $e) {
            Log::error('Redis llen error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function sadd(string $key, mixed $member): bool
    {
        try {
            $redis = self::connect();
            return $redis->sadd(self::$config['prefix'] . $key, self::serialize($member));
        } catch (\Exception $e) {
            Log::error('Redis sadd error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function srem(string $key, mixed $member): bool
    {
        try {
            $redis = self::connect();
            return $redis->srem(self::$config['prefix'] . $key, self::serialize($member));
        } catch (\Exception $e) {
            Log::error('Redis srem error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function sismember(string $key, mixed $member): bool
    {
        try {
            $redis = self::connect();
            return $redis->sismember(self::$config['prefix'] . $key, self::serialize($member));
        } catch (\Exception $e) {
            Log::error('Redis sismember error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function smembers(string $key): array
    {
        try {
            $redis = self::connect();
            $members = $redis->smembers(self::$config['prefix'] . $key);
            
            return array_map([self::class, 'unserialize'], $members);
        } catch (\Exception $e) {
            Log::error('Redis smembers error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function sinter(string $key, array $members): array
    {
        try {
            $redis = self::connect();
            $serialized = array_map([self::class, 'serialize'], $members);
            $intersection = $redis->sinter(self::$config['prefix'] . $key, ...$serialized);
            
            return array_map([self::class, 'unserialize'], $intersection);
        } catch (\Exception $e) {
            Log::error('Redis sinter error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function sunion(string $key, array $members): array
    {
        try {
            $redis = self::connect();
            $serialized = array_map([self::class, 'serialize'], $members);
            $union = $redis->sunion(self::$config['prefix'] . $key, ...$serialized);
            
            return array_map([self::class, 'unserialize'], $union);
        } catch (\Exception $e) {
            Log::error('Redis sunion error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function sdiff(string $key, array $members): array
    {
        try {
            $redis = self::connect();
            $serialized = array_map([self::class, 'serialize'], $members);
            $diff = $redis->sdiff(self::$config['prefix'] . $key, ...$serialized);
            
            return array_map([self::class, 'unserialize'], $diff);
        } catch (\Exception $e) {
            Log::error('Redis sdiff error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function mget(array $keys): array
    {
        try {
            $redis = self::connect();
            $prefixedKeys = array_map(fn($key) => self::$config['prefix'] . $key, $keys);
            $values = $redis->mget($prefixedKeys);
            
            $result = [];
            foreach ($keys as $i => $key) {
                $result[$key] = $values[$i] !== false ? self::unserialize($values[$i]) : null;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Redis mget error', [
                'keys' => $keys,
                'error' => $e->getMessage()
            ]);
            return array_fill_keys($keys, null);
        }
    }

    public static function mset(array $values, int $ttl = 3600): bool
    {
        try {
            $redis = self::connect();
            $prefixedValues = [];
            
            foreach ($values as $key => $value) {
                $prefixedValues[self::$config['prefix'] . $key] = self::serialize($value);
            }
            
            if ($ttl > 0) {
                return $redis->msetex($prefixedValues, $ttl);
            } else {
                return $redis->mset($prefixedValues);
            }
        } catch (\Exception $e) {
            Lock::error('Redis mset error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function mdel(array $keys): int
    {
        try {
            $redis = self::connect();
            $prefixedKeys = array_map(fn($key) => self::$config['prefix'] . $key, $keys);
            return $redis->del($prefixedKeys);
        } catch (\Exception $e) {
            Log::error('Redis mdel error', [
                'keys' => $keys,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function pipeline(callable $callback): array
    {
        try {
            $redis = self::connect();
            $redis->multi();
            
            $result = $callback($redis);
            
            $redis->exec();
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Redis pipeline error', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public static function transaction(callable $callback): mixed
    {
        try {
            $redis = self::connect();
            $redis->multi();
            
            $redis->exec();
            
            $result = $callback($redis);
            
            $redis->exec();
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Redis transaction error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public static function publish(string $channel, mixed $message): int
    {
        try {
            $redis = self::connect();
            return $redis->publish($channel, self::serialize($message));
        } catch (\Exception $e) {
            Log::error('Redis publish error', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public static function subscribe(string $channel, callable $callback): void
    {
        try {
            $redis = self::connect();
            $redis->subscribe($channel);
            
            while ($message = $redis->subscribe()) {
                $callback($channel, self::unserialize($message));
            }
        } catch (\Exception $e) {
            Log::error('Redis subscribe error', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function psubscribe(array $channels, callable $callback): void
    {
        try {
            $redis = self::connect();
            $redis->psubscribe($channels);
            
            while ($message = $redis->psubscribe()) {
                $channel = $message[0];
                $data = self::unserialize($message[1]);
                $callback($channel, $data);
            }
        } catch (\Exception $e) {
            Log::error('Redis psubscribe error', [
                'channels' => $channels,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function info(): array
    {
        try {
            $redis = self::connect();
            return [
                'server' => $redis->info(),
                'config' => self::$config,
                'connected' => true,
            ];
        } catch (\Exception $e) {
            return [
                'server' => [],
                'config' => self::$config,
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function disconnect(): void
    {
        if (self::$connection) {
            self::$connection->close();
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
