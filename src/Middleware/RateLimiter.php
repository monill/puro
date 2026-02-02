<?php

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Helpers\CacheHelper;
use App\Helpers\LogHelper;

/**
 * Rate Limiter - Limitador de Tentativas
 * 
 * Protege contra brute force, DDoS e abuso
 */
class RateLimiter {
    
    /**
     * Verifica se pode fazer a requisição
     */
    public static function attempt($key, $maxAttempts = 60, $minutes = 1) {
        $cacheKey = "rate_limit:$key";
        $current = CacheHelper::get($cacheKey, 0);
        
        // Se excedeu o limite
        if ($current >= $maxAttempts) {
            LogHelper::warning("Rate limit exceeded for key: $key", [
                'attempts' => $current,
                'limit' => $maxAttempts,
                'minutes' => $minutes
            ]);
            return false;
        }
        
        // Incrementa contador
        $newCount = $current + 1;
        CacheHelper::put($cacheKey, $newCount, $minutes * 60);
        
        LogHelper::info("Rate limit attempt for key: $key", [
            'attempts' => $newCount,
            'limit' => $maxAttempts
        ]);
        
        return true;
    }
    
    /**
     * Verifica se está bloqueado
     */
    public static function isBlocked($key) {
        $cacheKey = "rate_limit:$key";
        $current = CacheHelper::get($cacheKey, 0);
        
        return $current >= 60; // Bloqueado após 60 tentativas
    }
    
    /**
     * Reseta o contador
     */
    public static function reset($key) {
        $cacheKey = "rate_limit:$key";
        CacheHelper::forget($cacheKey);
        
        LogHelper::info("Rate limit reset for key: $key");
    }
    
    /**
     * Obtém estatísticas
     */
    public static function getStats($key) {
        $cacheKey = "rate_limit:$key";
        $current = CacheHelper::get($cacheKey, 0);
        
        return [
            'attempts' => $current,
            'remaining' => max(0, 60 - $current),
            'is_blocked' => $current >= 60
        ];
    }
}
