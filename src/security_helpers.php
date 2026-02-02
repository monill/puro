<?php

// =============================================================================
// FUNÇÕES DE SEGURANÇA - Adicionar ao helpers.php
// =============================================================================

if (!function_exists('csrf_field')) {
    /**
     * Gera campo CSRF para formulários
     */
    function csrf_field() {
        return \App\Middleware\SecurityMiddleware::csrfField();
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Obtém token CSRF
     */
    function csrf_token() {
        return \App\Middleware\SecurityMiddleware::getCSRFToken();
    }
}

if (!function_exists('queue_push')) {
    /**
     * Adiciona job à fila
     */
    function queue_push($jobClass, $data = [], $priority = 'normal') {
        return \App\Queue\Queue::push($jobClass, $data, $priority);
    }
}

if (!function_exists('queue_later')) {
    /**
     * Adiciona job para executar depois
     */
    function queue_later($seconds, $jobClass, $data = []) {
        return \App\Queue\Queue::later($seconds, $jobClass, $data);
    }
}

if (!function_exists('blacklist_ip')) {
    /**
     * Adiciona IP à blacklist
     */
    function blacklist_ip($ip, $minutes = 60, $reason = 'Security violation') {
        return \App\Middleware\BlackListManager::add($ip, $minutes, $reason);
    }
}

if (!function_exists('is_ip_blacklisted')) {
    /**
     * Verifica se IP está na blacklist
     */
    function is_ip_blacklisted($ip) {
        return \App\Middleware\BlackListManager::isBlacklisted($ip);
    }
}

// =============================================================================
// EXEMPLOS DE USO
// =============================================================================

/*
// CSRF em formulários:
<form method="POST">
    <?= csrf_field() ?>
    <input type="text" name="username">
    <button type="submit">Enviar</button>
</form>

// Queue system:
queue_push('IncrementResourcesJob', [
    'village_id' => 123,
    'resource_type' => 'wood',
    'amount' => 50
]);

// Executar job em 5 minutos:
queue_later(300, 'GameTickJob', ['tick_number' => 100]);

// Blacklist IP:
blacklist_ip('192.168.1.100', 60, 'Brute force attempt');

// Verificar IP:
if (is_ip_blacklisted($ip)) {
    // Bloquear acesso
}
*/
