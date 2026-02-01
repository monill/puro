<?php

/**
 * Funções Globais - Helpers
 * Estas funções estão disponíveis globalmente em toda a aplicação
 * Como as helpers functions do Laravel!
 */

use App\Helpers\FileHelper;
use App\Helpers\LogHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\AuthHelper;
use App\Helpers\LangHelper;
use App\Helpers\TemplateHelper;
use App\Helpers\EmailHelper;

// ============================================================================
// FILE HELPER FUNCTIONS
// ============================================================================

if (!function_exists('storage_path')) {
    /**
     * Obter path do storage
     */
    function storage_path($path = '') {
        return FileHelper::storage($path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Obter path do public
     */
    function public_path($path = '') {
        return FileHelper::public($path);
    }
}

if (!function_exists('base_path')) {
    /**
     * Obter path base da aplicação
     */
    function base_path($path = '') {
        return FileHelper::path($path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Obter path do src
     */
    function app_path($path = '') {
        return FileHelper::path('src/' . $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Obter path do config
     */
    function config_path($path = '') {
        return FileHelper::path('config/' . $path);
    }
}

if (!function_exists('resource_path')) {
    /**
     * Obter path do resources
     */
    function resource_path($path = '') {
        return FileHelper::path('resources/' . $path);
    }
}

// ============================================================================
// LOG HELPER FUNCTIONS
// ============================================================================

if (!function_exists('logger')) {
    /**
     * Log genérico
     */
    function logger($level, $message, $context = []) {
        LogHelper::log($level, $message, $context);
    }
}

if (!function_exists('info')) {
    /**
     * Log de informação
     */
    function info($message, $context = []) {
        LogHelper::info($message, $context);
    }
}

if (!function_exists('debug')) {
    /**
     * Log de debug
     */
    function debug($message, $context = []) {
        LogHelper::debug($message, $context);
    }
}

if (!function_exists('error')) {
    /**
     * Log de erro
     */
    function error($message, $context = []) {
        LogHelper::error($message, $context);
    }
}

if (!function_exists('warning')) {
    /**
     * Log de aviso
     */
    function warning($message, $context = []) {
        LogHelper::warning($message, $context);
    }
}

if (!function_exists('log_sql')) {
    /**
     * Log de SQL
     */
    function log_sql($query, $bindings = [], $time = null) {
        LogHelper::sql($query, $bindings, $time);
    }
}

// ============================================================================
// CACHE HELPER FUNCTIONS
// ============================================================================

if (!function_exists('cache')) {
    /**
     * Cache helper
     */
    function cache($key = null, $value = null, $ttl = null) {
        if ($key === null) {
            return new class {
                public function remember($key, $callback, $ttl = 3600) {
                    return CacheHelper::remember($key, $callback, $ttl);
                }
                
                public function forget($key) {
                    return CacheHelper::forget($key);
                }
                
                public function flush() {
                    return CacheHelper::flush();
                }
                
                public function get($key, $default = null) {
                    return CacheHelper::get($key, $default);
                }
                
                public function put($key, $value, $ttl = 3600) {
                    return CacheHelper::put($key, $value, $ttl);
                }
            };
        }
        
        if ($value === null) {
            return CacheHelper::get($key);
        }
        
        return CacheHelper::put($key, $value, $ttl);
    }
}

if (!function_exists('remember')) {
    /**
     * Cache remember
     */
    function remember($key, $callback, $ttl = 3600) {
        return CacheHelper::remember($key, $callback, $ttl);
    }
}

// ============================================================================
// TEMPLATE PATH FUNCTIONS
// ============================================================================

if (!function_exists('template_path')) {
    /**
     * Obter path do template
     */
    function template_path($path = '') {
        return FileHelper::path("templates/{$path}");
    }
}

if (!function_exists('layout_path')) {
    /**
     * Obter path do layout
     */
    function layout_path($path = '') {
        return FileHelper::path("templates/layout/{$path}");
    }
}

// ============================================================================
// CONFIG HELPER FUNCTIONS
// ============================================================================

if (!function_exists('config')) {
    /**
     * Obter valor da configuração
     * Uso: config('app.name'), config('database.host'), etc.
     */
    function config($key, $default = null) {
        return ConfigHelper::get($key, $default);
    }
}

if (!function_exists('app_url')) {
    /**
     * Obter URL base da aplicação
     */
    function app_url($path = '') {
        return ConfigHelper::url($path);
    }
}

if (!function_exists('is_debug')) {
    /**
     * Verificar se está em debug
     */
    function is_debug() {
        return ConfigHelper::isDebug();
    }
}

if (!function_exists('is_local')) {
    /**
     * Verificar se está em ambiente local
     */
    function is_local() {
        return ConfigHelper::isLocal();
    }
}

// ============================================================================
// VALIDATION HELPER FUNCTIONS
// ============================================================================

if (!function_exists('validate')) {
    /**
     * Validar dados
     */
    function validate($data, $rules) {
        return ValidationHelper::validate($data, $rules);
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitizar string
     */
    function sanitize($string) {
        return ValidationHelper::sanitize($string);
    }
}

if (!function_exists('is_email')) {
    /**
     * Validar email
     */
    function is_email($email) {
        return ValidationHelper::email($email);
    }
}

if (!function_exists('is_url')) {
    /**
     * Validar URL
     */
    function is_url($url) {
        return ValidationHelper::url($url);
    }
}

if (!function_exists('slug')) {
    /**
     * Gerar slug
     */
    function slug($string) {
        return ValidationHelper::slug($string);
    }
}

if (!function_exists('uuid')) {
    /**
     * Gerar UUID
     */
    function uuid() {
        return ValidationHelper::uuid();
    }
}

if (!function_exists('random_token')) {
    /**
     * Gerar token aleatório
     */
    function random_token($length = 32) {
        return ValidationHelper::randomToken($length);
    }
}

// ============================================================================
// AUTH HELPER FUNCTIONS
// ============================================================================

if (!function_exists('auth')) {
    /**
     * Auth helper
     */
    function auth() {
        return new class {
            public function check() {
                return AuthHelper::check();
            }
            
            public function user() {
                return AuthHelper::user();
            }
            
            public function id() {
                return AuthHelper::id();
            }
            
            public function guest() {
                return !AuthHelper::check();
            }
            
            public function isAdmin() {
                return AuthHelper::isAdmin();
            }
            
            public function login($user, $remember = false) {
                return AuthHelper::login($user, $remember);
            }
            
            public function logout() {
                return AuthHelper::logout();
            }
            
            public function can($permission) {
                return AuthHelper::can($permission);
            }
        };
    }
}

if (!function_exists('user')) {
    /**
     * Obter usuário autenticado
     */
    function user() {
        return AuthHelper::user();
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Verificar se está logado
     */
    function is_logged_in() {
        return AuthHelper::check();
    }
}

if (!function_exists('is_admin')) {
    /**
     * Verificar se é admin
     */
    function is_admin() {
        return AuthHelper::isAdmin();
    }
}

// ============================================================================
// LANGUAGE HELPER FUNCTIONS
// ============================================================================

if (!function_exists('trans')) {
    /**
     * Tradução (alias para LangHelper::get)
     */
    function trans($key, $replace = [], $locale = null) {
        return LangHelper::get($key, $replace, $locale);
    }
}

if (!function_exists('__')) {
    /**
     * Tradução curta (alias para trans)
     */
    function __($key, $replace = []) {
        return LangHelper::get($key, $replace);
    }
}

if (!function_exists('trans_choice')) {
    /**
     * Tradução plural
     */
    function trans_choice($key, $number, $replace = []) {
        return LangHelper::choice($key, $number, $replace);
    }
}

if (!function_exists('locale')) {
    /**
     * Obter locale atual
     */
    function locale() {
        return LangHelper::getLocale();
    }
}

if (!function_exists('set_locale')) {
    /**
     * Definir locale
     */
    function set_locale($locale) {
        return LangHelper::setLocale($locale);
    }
}

// ============================================================================
// TEMPLATE HELPER FUNCTIONS
// ============================================================================

if (!function_exists('format_number')) {
    /**
     * Formatar número
     */
    function format_number($number, $decimals = 0) {
        return TemplateHelper::formatNumber($number, $decimals);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formatar moeda
     */
    function format_currency($amount, $currency = 'USD') {
        return TemplateHelper::formatCurrency($amount, $currency);
    }
}

if (!function_exists('format_date')) {
    /**
     * Formatar data
     */
    function format_date($date, $format = 'medium') {
        return TemplateHelper::formatDate($date, $format);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Tempo relativo
     */
    function time_ago($date) {
        return TemplateHelper::timeAgo($date);
    }
}

if (!function_exists('language_selector')) {
    /**
     * Gerar seletor de idiomas
     */
    function language_selector() {
        return LangHelper::languageSelector();
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

if (!function_exists('dd')) {
    /**
     * Die and Dump
     */
    function dd(...$vars) {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
            echo '<hr>';
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variables
     */
    function dump(...$vars) {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
            echo '<hr>';
        }
        echo '</pre>';
    }
}

if (!function_exists('old')) {
    /**
     * Obter valor antigo do formulário
     */
    function old($key, $default = '') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('back')) {
    /**
     * Redirecionar para página anterior
     */
    function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirecionar para URL
     */
    function redirect($url, $status = 302) {
        header("Location: {$url}", true, $status);
        exit;
    }
}

if (!function_exists('asset')) {
    /**
     * Obter URL de asset
     */
    function asset($path) {
        return app_url('assets/' . $path);
    }
}

if (!function_exists('url')) {
    /**
     * Obter URL
     */
    function url($path = '') {
        return app_url($path);
    }
}

if (!function_exists('route')) {
    /**
     * Obter URL de rota nomeada
     */
    function route($name, $params = []) {
        // TODO: Implementar sistema de rotas nomeadas
        return url($name);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Gerar token CSRF
     */
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = random_token(32);
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Gerar campo CSRF hidden
     */
    function csrf_field() {
        $token = csrf_token();
        return "<input type='hidden' name='csrf_token' value='{$token}'>";
    }
}

if (!function_exists('method_field')) {
    /**
     * Gerar campo method para forms
     */
    function method_field($method) {
        return "<input type='hidden' name='_method' value='{$method}'>";
    }
}

if (!function_exists('flash')) {
    /**
     * Obter mensagem flash
     */
    function flash($type = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($type === null) {
            $messages = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $messages;
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        
        return $message;
    }
}

if (!function_exists('session')) {
    /**
     * Obter/definir valor da sessão
     */
    function session($key = null, $default = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($key === null) {
            return $_SESSION;
        }
        
        if (func_num_args() === 2) {
            $_SESSION[$key] = $default;
            return $default;
        }
        
        return $_SESSION[$key] ?? $default;
    }
}

// ============================================================================
// CONFIG HELPER FUNCTIONS
// ============================================================================

// Função config() já definida acima (linha 211-218)

if (!function_exists('config_file')) {
    /**
     * Obter arquivo de configuração completo
     * Uso: config_file('app'), config_file('database'), etc.
     */
    function config_file($file, $default = null) {
        return ConfigHelper::getFile($file, $default);
    }
}

if (!function_exists('config_has')) {
    /**
     * Verificar se configuração existe
     * Uso: config_has('app.name'), config_has('database.host'), etc.
     */
    function config_has($key) {
        return ConfigHelper::has($key);
    }
}

if (!function_exists('config_set')) {
    /**
     * Definir valor da configuração
     * Uso: config_set('app.name', 'Novo Nome')
     */
    function config_set($key, $value) {
        return ConfigHelper::set($key, $value);
    }
}

if (!function_exists('config_save')) {
    /**
     * Salvar configuração em arquivo
     * Uso: config_save('app'), config_save('database', $config)
     */
    function config_save($file, $config = null) {
        return ConfigHelper::save($file, $config);
    }
}

if (!function_exists('config_reload')) {
    /**
     * Recarregar arquivo de configuração
     * Uso: config_reload('app'), config_reload('database')
     */
    function config_reload($file) {
        return ConfigHelper::reload($file);
    }
}

if (!function_exists('config_clear')) {
    /**
     * Limpar cache de configurações
     * Uso: config_clear()
     */
    function config_clear() {
        return ConfigHelper::clear();
    }
}

if (!function_exists('config_all')) {
    /**
     * Obter todas as configurações carregadas
     * Uso: config_all()
     */
    function config_all() {
        return ConfigHelper::all();
    }
}

if (!function_exists('config_loaded')) {
    /**
     * Obter arquivos de configuração carregados
     * Uso: config_loaded()
     */
    function config_loaded() {
        return ConfigHelper::loaded();
    }
}

// Configurações específicas com funções globais
if (!function_exists('app_name')) {
    /**
     * Obter nome da aplicação
     */
    function app_name() {
        return ConfigHelper::name();
    }
}

// Função app_url() já definida acima (linha 221-228)

if (!function_exists('app_version')) {
    /**
     * Obter versão da aplicação
     */
    function app_version() {
        return ConfigHelper::version();
    }
}

if (!function_exists('app_env')) {
    /**
     * Obter ambiente da aplicação
     */
    function app_env() {
        return config('app.env', 'local');
    }
}

if (!function_exists('app_debug')) {
    /**
     * Verificar se debug está ativado
     */
    function app_debug() {
        return ConfigHelper::isDebug();
    }
}

if (!function_exists('app_local')) {
    /**
     * Verificar se está em ambiente local
     */
    function app_local() {
        return ConfigHelper::isLocal();
    }
}

if (!function_exists('app_production')) {
    /**
     * Verificar se está em ambiente de produção
     */
    function app_production() {
        return ConfigHelper::isProduction();
    }
}

if (!function_exists('db_config')) {
    /**
     * Obter configuração do database
     */
    function db_config($key = null, $default = null) {
        return ConfigHelper::database($key, $default);
    }
}

if (!function_exists('cache_config')) {
    /**
     * Obter configuração do cache
     */
    function cache_config($key = null, $default = null) {
        return ConfigHelper::cache($key, $default);
    }
}

if (!function_exists('email_config')) {
    /**
     * Obter configuração do email
     */
    function email_config($key = null, $default = null) {
        return ConfigHelper::email($key, $default);
    }
}

if (!function_exists('security_config')) {
    /**
     * Obter configuração de segurança
     */
    function security_config($key = null, $default = null) {
        return ConfigHelper::security($key, $default);
    }
}

if (!function_exists('game_config')) {
    /**
     * Obter configuração do jogo
     */
    function game_config($key = null, $default = null) {
        return ConfigHelper::game($key, $default);
    }
}

if (!function_exists('session_config')) {
    /**
     * Obter configuração de sessão
     */
    function session_config($key = null, $default = null) {
        return ConfigHelper::session($key, $default);
    }
}

// ============================================================================
// EMAIL HELPER FUNCTIONS
// ============================================================================

if (!function_exists('email')) {
    /**
     * Send email
     */
    function email($to, $subject, $body, $options = []) {
        return EmailHelper::getInstance()->send($to, $subject, $body, $options);
    }
}

if (!function_exists('send_welcome_email')) {
    /**
     * Send welcome email
     */
    function send_welcome_email($user, $password = null) {
        return EmailHelper::getInstance()->sendWelcome($user, $password);
    }
}

if (!function_exists('send_password_reset_email')) {
    /**
     * Send password reset email
     */
    function send_password_reset_email($user, $token) {
        return EmailHelper::getInstance()->sendPasswordReset($user, $token);
    }
}

if (!function_exists('send_email_verification')) {
    /**
     * Send email verification
     */
    function send_email_verification($user, $token) {
        return EmailHelper::getInstance()->sendEmailVerification($user, $token);
    }
}

if (!function_exists('send_notification_email')) {
    /**
     * Send notification email
     */
    function send_notification_email($user, $title, $message, $data = []) {
        return EmailHelper::getInstance()->sendNotification($user, $title, $message, $data);
    }
}

if (!function_exists('send_battle_report_email')) {
    /**
     * Send battle report email
     */
    function send_battle_report_email($user, $battle) {
        return EmailHelper::getInstance()->sendBattleReport($user, $battle);
    }
}

if (!function_exists('send_alliance_invitation_email')) {
    /**
     * Send alliance invitation email
     */
    function send_alliance_invitation_email($user, $alliance, $inviter) {
        return EmailHelper::getInstance()->sendAllianceInvitation($user, $alliance, $inviter);
    }
}

if (!function_exists('test_email')) {
    /**
     * Test email configuration
     */
    function test_email($to = null) {
        return EmailHelper::getInstance()->test($to);
    }
}

// Função email_config() já definida acima (linha 817-824)

if (!function_exists('email_is_configured')) {
    /**
     * Check if email is configured
     */
    function email_is_configured() {
        return EmailHelper::getInstance()->isConfigured();
    }
}
