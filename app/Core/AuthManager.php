<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Session;

class AuthManager
{
    private static array $guards = [];
    private static array $config = [];
    private static ?string $currentGuard = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function guard(?string $name = null): Auth
    {
        $guard = $name ?? self::$config['default'] ?? 'web';
        
        if (!isset(self::$guards[$guard])) {
            self::$guards[$guard] = self::createGuard($guard);
        }
        
        self::$currentGuard = $guard;
        return self::$guards[$guard];
    }

    private static function createGuard(string $name): Auth
    {
        $guardConfig = self::$config['guards'][$name] ?? [];
        $provider = $guardConfig['provider'] ?? null;
        $model = $guardConfig['model'] ?? null;
        
        if (!$provider || !$model) {
            throw new \InvalidArgumentException("Guard configuration incomplete for: {$name}");
        }
        
        $auth = new Auth();
        $auth->setUserClass($model);
        
        // Configurar session key específico para o guard
        $sessionConfig = [
            'session_key' => "auth_{$name}_user",
            'remember_cookie' => "remember_{$name}_token",
        ];
        
        $auth->configure($sessionConfig);
        
        return $auth;
    }

    public static function user(?string $guard = null): ?object
    {
        return self::guard($guard)->user();
    }

    public static function check(?string $guard = null): bool
    {
        return self::guard($guard)->check();
    }

    public static function guest(?string $guard = null): bool
    {
        return self::guard($guard)->guest();
    }

    public static function id(?string $guard = null): mixed
    {
        return self::guard($guard)->id();
    }

    public static function attempt(array $credentials, bool $remember = false, ?string $guard = null): bool
    {
        return self::guard($guard)->attempt($credentials, $remember);
    }

    public static function login(object $user, bool $remember = false, ?string $guard = null): void
    {
        self::guard($guard)->login($user);
    }

    public static function logout(?string $guard = null): void
    {
        self::guard($guard)->logout();
    }

    public static function validateCredentials(array $credentials, ?string $guard = null): ?object
    {
        return self::guard($guard)->validateCredentials($credentials);
    }

    public static function getCurrentGuard(): ?string
    {
        return self::$currentGuard;
    }

    public static function getProvider(string $name): array
    {
        return self::$config['providers'][$name] ?? [];
    }

    public static function getGuardConfig(string $name): array
    {
        return self::$config['guards'][$name] ?? [];
    }

    // Métodos para autenticação específica
    public static function admin(): Auth
    {
        return self::guard('web');
    }

    public static function customer(): Auth
    {
        return self::guard('customer');
    }

    // Verificar se usuário está autenticado em qualquer guard
    public static function authenticated(): bool
    {
        foreach (array_keys(self::$config['guards'] ?? []) as $guard) {
            if (self::check($guard)) {
                return true;
            }
        }
        
        return false;
    }

    // Obter todos os usuários autenticados
    public static function allUsers(): array
    {
        $users = [];
        
        foreach (array_keys(self::$config['guards'] ?? []) as $guard) {
            $user = self::user($guard);
            if ($user) {
                $users[$guard] = $user;
            }
        }
        
        return $users;
    }

    // Logout de todos os guards
    public static function logoutAll(): void
    {
        foreach (array_keys(self::$config['guards'] ?? []) as $guard) {
            self::logout($guard);
        }
    }

    // Middleware helper
    public static function shouldPassThrough(array $guards, callable $callback): mixed
    {
        foreach ($guards as $guard) {
            if (self::check($guard)) {
                return $callback(self::user($guard), $guard);
            }
        }
        
        return null;
    }
}
