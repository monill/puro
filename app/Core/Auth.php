<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Session;

class Auth
{
    private static ?object $user = null;
    private static ?string $userClass = null;
    private static array $config = [
        'session_key' => 'auth_user',
        'remember_key' => 'remember_token',
        'remember_cookie' => 'remember_me',
        'remember_expires' => 2592000, // 30 days
        'password_min_length' => 8,
        'password_algorithm' => PASSWORD_DEFAULT,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function setUserClass(string $userClass): void
    {
        if (!class_exists($userClass)) {
            throw new \InvalidArgumentException("User class not found: {$userClass}");
        }
        
        self::$userClass = $userClass;
    }

    public static function attempt(array $credentials, bool $remember = false): bool
    {
        $user = self::validateCredentials($credentials);
        
        if ($user) {
            self::login($user, $remember);
            return true;
        }
        
        return false;
    }

    public static function validateCredentials(array $credentials): ?object
    {
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return null;
        }

        $user = self::findUserByEmail($credentials['email']);
        
        if ($user && self::verifyPassword($credentials['password'], $user->password ?? '')) {
            return $user;
        }
        
        return null;
    }

    private static function findUserByEmail(string $email): ?object
    {
        if (!self::$userClass) {
            throw new \RuntimeException('User class not set. Call Auth::setUserClass() first.');
        }

        $userClass = self::$userClass;
        
        if (method_exists($userClass, 'findByEmail')) {
            return $userClass::findByEmail($email);
        }
        
        if (method_exists($userClass, 'where')) {
            return $userClass::where('email', $email)->first();
        }
        
        throw new \RuntimeException('User class must implement findByEmail() or where() method.');
    }

    public static function login(object $user, bool $remember = false): void
    {
        Session::start();
        
        self::$user = $user;
        Session::set(self::$config['session_key'], self::getUserId($user));
        
        if ($remember) {
            self::createRememberToken($user);
        }
        
        self::regenerateSession();
    }

    public static function logout(): void
    {
        Session::start();
        
        Session::remove(self::$config['session_key']);
        
        if (self::hasRememberCookie()) {
            self::clearRememberToken();
        }
        
        self::regenerateSession();
        self::$user = null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?object
    {
        if (self::$user !== null) {
            return self::$user;
        }

        Session::start();
        
        $userId = Session::get(self::$config['session_key']);
        
        if ($userId) {
            self::$user = self::findUserById($userId);
            return self::$user;
        }
        
        if (self::hasRememberCookie()) {
            $user = self::authenticateViaRememberToken();
            if ($user) {
                self::login($user);
                return $user;
            }
        }
        
        return null;
    }

    private static function findUserById($userId): ?object
    {
        if (!self::$userClass) {
            throw new \RuntimeException('User class not set. Call Auth::setUserClass() first.');
        }

        $userClass = self::$userClass;
        
        if (method_exists($userClass, 'find')) {
            return $userClass::find($userId);
        }
        
        throw new \RuntimeException('User class must implement find() method.');
    }

    private static function getUserId(object $user): mixed
    {
        return $user->id ?? $user->user_id ?? $user->getId() ?? null;
    }

    public static function id(): mixed
    {
        return self::user() ? self::getUserId(self::$user) : null;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::$config['password_algorithm']);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < self::$config['password_min_length']) {
            $errors[] = "Password must be at least " . self::$config['password_min_length'] . " characters long.";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        
        return $errors;
    }

    private static function createRememberToken(object $user): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = self::hashPassword($token);
        
        self::setRememberTokenInDatabase($user, $hashedToken);
        
        $cookieValue = base64_encode(self::getUserId($user) . ':' . $token);
        setcookie(
            self::$config['remember_cookie'],
            $cookieValue,
            time() + self::$config['remember_expires'],
            '/',
            '',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            true
        );
    }

    private static function setRememberTokenInDatabase(object $user, string $hashedToken): void
    {
        $userClass = self::$userClass;
        
        if (method_exists($user, 'setRememberToken')) {
            $user->setRememberToken($hashedToken);
            $user->save();
        } elseif (method_exists($userClass, 'setRememberToken')) {
            $userClass::setRememberToken(self::getUserId($user), $hashedToken);
        }
    }

    private static function hasRememberCookie(): bool
    {
        return isset($_COOKIE[self::$config['remember_cookie']]);
    }

    private static function authenticateViaRememberToken(): ?object
    {
        $cookieValue = $_COOKIE[self::$config['remember_cookie']] ?? '';
        $data = explode(':', base64_decode($cookieValue));
        
        if (count($data) !== 2) {
            return null;
        }
        
        [$userId, $token] = $data;
        
        $user = self::findUserById($userId);
        
        if (!$user) {
            return null;
        }
        
        $hashedToken = self::getRememberTokenFromDatabase($user);
        
        if ($hashedToken && self::verifyPassword($token, $hashedToken)) {
            return $user;
        }
        
        return null;
    }

    private static function getRememberTokenFromDatabase(object $user): ?string
    {
        if (method_exists($user, 'getRememberToken')) {
            return $user->getRememberToken();
        }
        
        return null;
    }

    private static function clearRememberToken(): void
    {
        setcookie(
            self::$config['remember_cookie'],
            '',
            time() - 3600,
            '/',
            '',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            true
        );
        
        if (self::$user) {
            self::setRememberTokenInDatabase(self::$user, '');
        }
    }

    private static function regenerateSession(): void
    {
        Session::regenerate(true);
    }

    public static function validateUser(object $user): bool
    {
        if (!self::$userClass) {
            return false;
        }
        
        return $user instanceof self::$userClass;
    }

    public static function can(string $ability): bool
    {
        $user = self::user();
        
        if (!$user) {
            return false;
        }
        
        if (method_exists($user, 'can')) {
            return $user->can($ability);
        }
        
        return false;
    }

    public static function cannot(string $ability): bool
    {
        return !self::can($ability);
    }

    public static function policy(string $policyClass): void
    {
        if (!class_exists($policyClass)) {
            throw new \InvalidArgumentException("Policy class not found: {$policyClass}");
        }
    }

    public static function authorize(string $ability, mixed $resource = null): bool
    {
        if (self::can($ability)) {
            return true;
        }
        
        throw new \RuntimeException("Unauthorized action: {$ability}");
    }

    public static function getConfig(): array
    {
        return self::$config;
    }
}
