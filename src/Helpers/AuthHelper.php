<?php

namespace App\Helpers;

class AuthHelper {
    private static $user = null;
    
    /**
     * Iniciar sessão
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Fazer login
     */
    public static function login($user, $remember = false) {
        self::start();
        
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->username;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role ?? 'player';
        $_SESSION['login_time'] = time();
        
        if ($remember) {
            // Criar token remember
            $token = ValidationHelper::randomToken(64);
            $expires = time() + (30 * 24 * 60 * 60); // 30 dias
            
            // Salvar token no banco
            // TODO: Implementar tabela de remember_tokens
            
            setcookie('remember_token', $token, $expires, '/', '', false, true);
        }
        
        // Atualizar último login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
        
        LogHelper::info('Login realizado', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return true;
    }
    
    /**
     * Fazer logout
     */
    public static function logout() {
        self::start();
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // Limpar sessão
        $_SESSION = [];
        
        // Destruir cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        // Destruir sessão
        session_destroy();
        
        // Limpar cookie remember
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        
        if ($userId) {
            LogHelper::info('Logout realizado', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
        
        return true;
    }
    
    /**
     * Verificar se está logado
     */
    public static function check() {
        self::start();
        
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Verificar cookie remember
        if (isset($_COOKIE['remember_token'])) {
            return self::attemptRemember($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Tentar login com remember token
     */
    private static function attemptRemember($token) {
        // TODO: Implementar verificação de remember token no banco
        return false;
    }
    
    /**
     * Obter usuário logado
     */
    public static function user() {
        if (self::$user === null) {
            if (self::check()) {
                $userId = $_SESSION['user_id'];
                self::$user = \App\Database\Models\User::find($userId);
            }
        }
        
        return self::$user;
    }
    
    /**
     * Obter ID do usuário logado
     */
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Verificar se é admin
     */
    public static function isAdmin() {
        return ($_SESSION['user_role'] ?? 'player') === 'admin';
    }
    
    /**
     * Verificar se é moderador
     */
    public static function isModerator() {
        $role = $_SESSION['user_role'] ?? 'player';
        return in_array($role, ['admin', 'moderator']);
    }
    
    /**
     * Verificar se o usuário tem permissão
     */
    public static function can($permission) {
        $user = self::user();
        
        if (!$user) {
            return false;
        }
        
        // Admin pode tudo
        if (self::isAdmin()) {
            return true;
        }
        
        // TODO: Implementar sistema de permissões
        return false;
    }
    
    /**
     * Verificar se o usuário é dono do recurso
     */
    public static function owns($resource, $ownerField = 'user_id') {
        $user = self::user();
        
        if (!$user) {
            return false;
        }
        
        if (self::isAdmin()) {
            return true;
        }
        
        return $resource->$ownerField === $user->id;
    }
    
    /**
     * Gerar hash de senha
     */
    public static function hash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verificar senha
     */
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Gerar token de reset de senha
     */
    public static function generateResetToken($email) {
        $token = ValidationHelper::randomToken(32);
        $expires = time() + (60 * 60); // 1 hora
        
        // TODO: Salvar token no banco
        
        LogHelper::info('Token de reset gerado', [
            'email' => $email,
            'token' => $token,
            'expires' => $expires
        ]);
        
        return $token;
    }
    
    /**
     * Verificar token de reset de senha
     */
    public static function verifyResetToken($token) {
        // TODO: Implementar verificação no banco
        return false;
    }
    
    /**
     * Obter tempo de sessão
     */
    public static function sessionTime() {
        if (!self::check()) {
            return 0;
        }
        
        $loginTime = $_SESSION['login_time'] ?? time();
        return time() - $loginTime;
    }
    
    /**
     * Verificar se sessão expirou
     */
    public static function isExpired($maxTime = 3600) {
        return self::sessionTime() > $maxTime;
    }
    
    /**
     * Renovar sessão
     */
    public static function renew() {
        if (self::check()) {
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
        }
    }
}
