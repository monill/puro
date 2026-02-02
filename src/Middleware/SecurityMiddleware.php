<?php

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Helpers\CacheHelper;
use App\Helpers\LogHelper;
use App\Helpers\SessionHelper;

/**
 * Security Middleware - Middleware de Segurança
 * 
 * Protege a aplicação contra:
 * - Brute Force
 * - DDoS
 * - XSS
 * - CSRF
 * - IPs maliciosos
 */
class SecurityMiddleware {
    
    private $request;
    private $response;
    
    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Handle principal do middleware
     */
    public function handle() {
        $ip = $this->getClientIp();
        $uri = $this->request->getUri();
        
        LogHelper::info("Security middleware check", [
            'ip' => $ip,
            'uri' => $uri,
            'user_agent' => $this->request->getUserAgent()
        ]);
        
        // 1. Verificar blacklist
        if (BlackListManager::isBlacklisted($ip)) {
            return $this->blockRequest('IP Blacklisted', 403);
        }
        
        // 2. Rate limiting por IP
        if (!$this->checkRateLimit($ip, $uri)) {
            return $this->blockRequest('Rate Limit Exceeded', 429);
        }
        
        // 3. CSRF Protection para POST/PUT/DELETE
        if ($this->isMethodProtected() && !$this->validateCSRF()) {
            return $this->blockRequest('Invalid CSRF Token', 419);
        }
        
        // 4. XSS Protection
        $this->sanitizeInput();
        
        // 5. Log de atividades suspeitas
        $this->logSuspiciousActivity($ip, $uri);
        
        // Continue com a requisição
        return null; // null = continue
    }
    
    /**
     * Verifica rate limit baseado na URI
     */
    private function checkRateLimit($ip, $uri) {
        $limits = $this->getRateLimits();
        
        foreach ($limits as $pattern => $config) {
            if ($this->matchesPattern($uri, $pattern)) {
                $key = "{$ip}:{$pattern}";
                
                if (!RateLimiter::attempt($key, $config['attempts'], $config['minutes'])) {
                    // Adiciona à blacklist se exceder muito
                    $stats = RateLimiter::getStats($key);
                    if ($stats['attempts'] > $config['attempts'] * 2) {
                        BlackListManager::add($ip, 30, 'Excessive rate limit violations');
                    }
                    
                    return false;
                }
                
                break; // Aplica primeira regra que match
            }
        }
        
        return true;
    }
    
    /**
     * Configurações de rate limit
     */
    private function getRateLimits() {
        return [
            // Login - mais restritivo
            '/login' => ['attempts' => 5, 'minutes' => 5],
            '/auth/login' => ['attempts' => 5, 'minutes' => 5],
            
            // Registro - restritivo
            '/register' => ['attempts' => 3, 'minutes' => 10],
            '/auth/register' => ['attempts' => 3, 'minutes' => 10],
            
            // API - mais liberal
            '/api/' => ['attempts' => 100, 'minutes' => 1],
            
            // Páginas públicas - liberal
            '/' => ['attempts' => 60, 'minutes' => 1],
            
            // Default para tudo
            'default' => ['attempts' => 60, 'minutes' => 1]
        ];
    }
    
    /**
     * Verifica se URI match com pattern
     */
    private function matchesPattern($uri, $pattern) {
        if ($pattern === 'default') {
            return true;
        }
        
        return strpos($uri, $pattern) === 0;
    }
    
    /**
     * Verifica se método precisa de CSRF
     */
    private function isMethodProtected() {
        $method = $this->request->getMethod();
        
        return in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']);
    }
    
    /**
     * Valida token CSRF
     */
    private function validateCSRF() {
        // Obter token da sessão
        $sessionToken = SessionHelper::get('_csrf_token');
        
        if (!$sessionToken) {
            $this->generateCSRFToken();
            $sessionToken = SessionHelper::get('_csrf_token');
        }
        
        // Obter token da requisição
        $requestToken = $this->request->getPost('_csrf_token') ?? 
                       $this->request->getHeader('X-CSRF-TOKEN');
        
        if (!$requestToken) {
            LogHelper::warning("CSRF token missing", [
                'ip' => $this->getClientIp(),
                'uri' => $this->request->getUri()
            ]);
            return false;
        }
        
        // Verificar se os tokens batem
        $isValid = hash_equals($sessionToken, $requestToken);
        
        if (!$isValid) {
            LogHelper::warning("CSRF token mismatch", [
                'ip' => $this->getClientIp(),
                'uri' => $this->request->getUri(),
                'session_token' => substr($sessionToken, 0, 8) . '...',
                'request_token' => substr($requestToken, 0, 8) . '...'
            ]);
        }
        
        return $isValid;
    }
    
    /**
     * Gera token CSRF
     */
    private function generateCSRFToken() {
        $token = bin2hex(random_bytes(32));
        SessionHelper::put('_csrf_token', $token);
        
        LogHelper::debug("CSRF token generated");
        
        return $token;
    }
    
    /**
     * Obtém token CSRF para usar em forms
     */
    public static function getCSRFToken() {
        if (!SessionHelper::has('_csrf_token')) {
            $token = bin2hex(random_bytes(32));
            SessionHelper::put('_csrf_token', $token);
        }
        
        return SessionHelper::get('_csrf_token');
    }
    
    /**
     * Campo CSRF para formulários
     */
    public static function csrfField() {
        $token = self::getCSRFToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Sanitiza input contra XSS
     */
    private function sanitizeInput() {
        // Sanitiza $_GET
        if ($_GET) {
            foreach ($_GET as $key => $value) {
                $_GET[$key] = $this->sanitizeString($value);
            }
        }
        
        // Sanitiza $_POST
        if ($_POST) {
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $_POST[$key] = $this->sanitizeArray($value);
                } else {
                    $_POST[$key] = $this->sanitizeString($value);
                }
            }
        }
        
        // Sanitiza cookies (exceto tokens)
        if ($_COOKIE) {
            foreach ($_COOKIE as $key => $value) {
                if (!str_contains($key, 'token') && !str_contains($key, 'csrf')) {
                    $_COOKIE[$key] = $this->sanitizeString($value);
                }
            }
        }
    }
    
    /**
     * Sanitiza string contra XSS
     */
    private function sanitizeString($string) {
        if (is_string($string)) {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }
        
        return $string;
    }
    
    /**
     * Sanitiza array recursivamente
     */
    private function sanitizeArray($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitizeArray($value);
            } else {
                $array[$key] = $this->sanitizeString($value);
            }
        }
        
        return $array;
    }
    
    /**
     * Log de atividades suspeitas
     */
    private function logSuspiciousActivity($ip, $uri) {
        $userAgent = $this->request->getUserAgent();
        
        // Detectar bots suspeitos
        $suspiciousPatterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'curl',
            'wget'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                LogHelper::info("Suspicious user agent detected", [
                    'ip' => $ip,
                    'uri' => $uri,
                    'user_agent' => $userAgent,
                    'pattern' => $pattern
                ]);
                
                // Se tiver muitas requisições, pode bloquear
                $key = "suspicious:$ip";
                if (!RateLimiter::attempt($key, 30, 1)) {
                    BlackListManager::add($ip, 15, 'Suspicious bot activity');
                }
                
                break;
            }
        }
        
        // Detectar requisições muito rápidas
        $key = "rapid_requests:$ip";
        if (!RateLimiter::attempt($key, 200, 1)) {
            LogHelper::warning("Too many rapid requests", [
                'ip' => $ip,
                'uri' => $uri
            ]);
        }
    }
    
    /**
     * Bloqueia requisição
     */
    private function blockRequest($reason, $statusCode = 403) {
        $ip = $this->getClientIp();
        
        LogHelper::warning("Request blocked", [
            'ip' => $ip,
            'uri' => $this->request->getUri(),
            'reason' => $reason,
            'status_code' => $statusCode
        ]);
        
        // Adiciona à blacklist se for violação grave
        if ($statusCode === 403) {
            BlackListManager::add($ip, 30, $reason);
        }
        
        return $this->response->json([
            'error' => $reason,
            'status' => 'blocked'
        ], $statusCode);
    }
    
    /**
     * Obtém IP real do cliente
     */
    private function getClientIp() {
        // Headers comuns que podem conter o IP real
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Load balancer
            'HTTP_X_REAL_IP',           // Nginx
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'               // Default
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Remove port se existir
                if (($pos = strpos($ip, ',')) !== false) {
                    $ip = trim(substr($ip, 0, $pos));
                }
                
                // Valida IPv4/IPv6
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Obtém estatísticas de segurança
     */
    public static function getSecurityStats() {
        $stats = [
            'blacklisted_ips' => count(BlackListManager::getAllBlocked()),
            'rate_limits' => 'Active',
            'csrf_protection' => 'Active',
            'xss_protection' => 'Active'
        ];
        
        return $stats;
    }
}
