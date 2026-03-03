<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $started = false;
    private static array $config = [
        'name' => 'PHPSESSID',
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    public static function start(array $config = []): bool
    {
        if (self::$started) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return true;
        }

        self::$config = array_merge(self::$config, $config);

        session_name(self::$config['name']);
        session_set_cookie_params([
            'lifetime' => self::$config['lifetime'],
            'path' => self::$config['path'],
            'domain' => self::$config['domain'],
            'secure' => self::$config['secure'],
            'httponly' => self::$config['httponly'],
            'samesite' => self::$config['samesite']
        ]);

        if (!headers_sent()) {
            if (session_start()) {
                self::$started = true;
                return true;
            }
        }

        return false;
    }

    public static function isStarted(): bool
    {
        return self::$started || session_status() === PHP_SESSION_ACTIVE;
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION['_flash'][$key]);
    }

    public static function all(): array
    {
        self::ensureStarted();
        return $_SESSION;
    }

    public static function only(array $keys): array
    {
        self::ensureStarted();
        return array_intersect_key($_SESSION, array_flip($keys));
    }

    public static function except(array $keys): array
    {
        self::ensureStarted();
        return array_diff_key($_SESSION, array_flip($keys));
    }

    public static function flush(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    public static function clear(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    public static function destroy(): bool
    {
        if (!self::isStarted()) {
            return false;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        return session_destroy();
    }

    public static function regenerate(bool $deleteOldSession = false): bool
    {
        self::ensureStarted();
        return session_regenerate_id($deleteOldSession);
    }

    public static function getId(): string
    {
        return session_id();
    }

    public static function setId(string $id): void
    {
        session_id($id);
    }

    public static function getName(): string
    {
        return session_name();
    }

    public static function setName(string $name): void
    {
        session_name($name);
    }

    public static function save(): void
    {
        if (self::isStarted()) {
            session_write_close();
            self::$started = false;
        }
    }

    public static function put(string $key, mixed $value): void
    {
        self::set($key, $value);
    }

    public static function push(string $key, mixed $value): void
    {
        self::ensureStarted();
        
        if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        $_SESSION[$key][] = $value;
    }

    public static function increment(string $key, int $amount = 1): int
    {
        self::ensureStarted();
        
        $value = self::get($key, 0);
        $value = is_numeric($value) ? (int) $value : 0;
        $value += $amount;
        
        self::set($key, $value);
        
        return $value;
    }

    public static function decrement(string $key, int $amount = 1): int
    {
        return self::increment($key, -$amount);
    }

    public static function forget(string $key): void
    {
        self::remove($key);
    }

    public static function token(): string
    {
        self::ensureStarted();
        
        if (!self::has('_token')) {
            self::set('_token', bin2hex(random_bytes(32)));
        }
        
        return self::get('_token');
    }

    public static function validateToken(string $token): bool
    {
        return hash_equals(self::token(), $token);
    }

    public static function csrf(): string
    {
        return self::token();
    }

    public static function validateCsrf(string $token): bool
    {
        return self::validateToken($token);
    }

    public static function previousUrl(): ?string
    {
        return self::get('_previous_url');
    }

    public static function setPreviousUrl(string $url): void
    {
        self::set('_previous_url', $url);
    }

    public static function keep(array $keys): void
    {
        self::ensureStarted();
        
        foreach ($keys as $key) {
            if (self::has($key)) {
                self::flash($key, self::get($key));
            }
        }
    }

    public static function reflash(): void
    {
        self::ensureStarted();
        
        if (isset($_SESSION['_flash'])) {
            foreach ($_SESSION['_flash'] as $key => $value) {
                self::flash($key, $value);
            }
        }
    }

    private static function ensureStarted(): void
    {
        if (!self::isStarted()) {
            self::start();
        }
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function setConfig(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }
}
