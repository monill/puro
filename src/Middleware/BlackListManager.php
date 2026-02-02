<?php

namespace App\Middleware;

use App\Helpers\CacheHelper;
use App\Helpers\LogHelper;

/**
 * Black List Manager - Gerenciador de IPs Bloqueados
 * 
 * Gerencia blacklist de IPs de forma eficiente com cache
 */
class BlackListManager {
    
    /**
     * Adiciona IP à blacklist
     */
    public static function add($ip, $minutes = 60, $reason = 'Security violation') {
        $cacheKey = "blacklist:$ip";
        $data = [
            'ip' => $ip,
            'reason' => $reason,
            'blocked_at' => time(),
            'expires_at' => time() + ($minutes * 60)
        ];
        
        CacheHelper::put($cacheKey, $data, $minutes * 60);
        
        LogHelper::warning("IP added to blacklist", [
            'ip' => $ip,
            'reason' => $reason,
            'duration_minutes' => $minutes
        ]);
        
        return true;
    }
    
    /**
     * Verifica se IP está na blacklist
     */
    public static function isBlacklisted($ip) {
        $cacheKey = "blacklist:$ip";
        $data = CacheHelper::get($cacheKey);
        
        if (!$data) {
            return false;
        }
        
        // Verifica se expirou
        if (time() > $data['expires_at']) {
            self::remove($ip);
            return false;
        }
        
        return true;
    }
    
    /**
     * Remove IP da blacklist
     */
    public static function remove($ip) {
        $cacheKey = "blacklist:$ip";
        CacheHelper::forget($cacheKey);
        
        LogHelper::info("IP removed from blacklist", ['ip' => $ip]);
        
        return true;
    }
    
    /**
     * Bloqueia IP permanentemente (24h)
     */
    public static function blockPermanent($ip, $reason = 'Permanent block') {
        return self::add($ip, 1440, $reason); // 24 horas = 1440 minutos
    }
    
    /**
     * Bloqueia IP por brute force
     */
    public static function blockBruteForce($ip) {
        return self::add($ip, 300, 'Brute force attack'); // 5 minutos
    }
    
    /**
     * Bloqueia IP por DDoS
     */
    public static function blockDDoS($ip) {
        return self::add($ip, 60, 'DDoS suspected'); // 1 minuto
    }
    
    /**
     * Obtém informações do bloqueio
     */
    public static function getBlockInfo($ip) {
        $cacheKey = "blacklist:$ip";
        $data = CacheHelper::get($cacheKey);
        
        if (!$data) {
            return null;
        }
        
        $remaining = $data['expires_at'] - time();
        
        return [
            'ip' => $data['ip'],
            'reason' => $data['reason'],
            'blocked_at' => date('Y-m-d H:i:s', $data['blocked_at']),
            'expires_at' => date('Y-m-d H:i:s', $data['expires_at']),
            'remaining_minutes' => max(0, ceil($remaining / 60)),
            'is_expired' => $remaining <= 0
        ];
    }
    
    /**
     * Limpa blacklist expirada
     */
    public static function cleanup() {
        // Como usa cache com TTL, não precisa limpar manualmente
        // Cache expira automaticamente
        
        LogHelper::info("Blacklist cleanup completed (auto-expired entries)");
        
        return true;
    }
    
    /**
     * Obtém todos os IPs bloqueados (para admin)
     */
    public static function getAllBlocked() {
        // Isso depende da implementação do CacheHelper
        // Se o CacheHelper suportar listagem de chaves:
        
        $blocked = [];
        $pattern = "blacklist:*";
        
        // Implementação depende do CacheHelper
        // Por enquanto, retorna array vazio
        
        LogHelper::info("Retrieved all blocked IPs", ['count' => count($blocked)]);
        
        return $blocked;
    }
    
    /**
     * Verifica e adiciona baseado em tentativas
     */
    public static function checkAndBlock($ip, $attempts, $threshold = 10) {
        if ($attempts >= $threshold) {
            $minutes = min(1440, $attempts * 10); // Escala: 10 min por tentativa acima do threshold
            self::add($ip, $minutes, "Too many attempts: $attempts");
            
            LogHelper::warning("Auto-blocked IP due to excessive attempts", [
                'ip' => $ip,
                'attempts' => $attempts,
                'threshold' => $threshold,
                'block_minutes' => $minutes
            ]);
            
            return true;
        }
        
        return false;
    }
}
