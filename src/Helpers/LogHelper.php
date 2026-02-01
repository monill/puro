<?php

namespace App\Helpers;

class LogHelper {
    private static $logLevels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7,
    ];

    /**
     * Escrever log de emergência
     */
    public static function emergency($message, $context = []) {
        self::log('emergency', $message, $context);
    }

    /**
     * Escrever log de alerta
     */
    public static function alert($message, $context = []) {
        self::log('alert', $message, $context);
    }

    /**
     * Escrever log crítico
     */
    public static function critical($message, $context = []) {
        self::log('critical', $message, $context);
    }

    /**
     * Escrever log de erro
     */
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }

    /**
     * Escrever log de aviso
     */
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }

    /**
     * Escrever log de informação
     */
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }

    /**
     * Escrever log de debug
     */
    public static function debug($message, $context = []) {
        if (APP_DEBUG) {
            self::log('debug', $message, $context);
        }
    }

    /**
     * Escrever log genérico
     */
    public static function log($level, $message, $context = []) {
        if (!self::shouldLog($level)) {
            return;
        }

        $logFile = self::getLogFile($level);
        FileHelper::ensureDirectory(dirname($logFile));

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' | ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Obter arquivo de log baseado no nível
     */
    private static function getLogFile($level) {
        $date = date('Y-m-d');

        // Logs diários para debug
        if ($level === 'debug') {
            return FileHelper::logs("debug-{$date}.log");
        }

        // Logs de erro em arquivo separado
        if (in_array($level, ['emergency', 'alert', 'critical', 'error'])) {
            return FileHelper::logs("error-{$date}.log");
        }

        // Logs gerais
        return FileHelper::logs("app-{$date}.log");
    }

    /**
     * Verificar se deve logar baseado no nível
     */
    private static function shouldLog($level) {
        $configLevel = APP_DEBUG ? 'debug' : 'info';

        return self::$logLevels[$level] <= self::$logLevels[$configLevel];
    }

    /**
     * Log de SQL queries
     */
    public static function sql($query, $bindings = [], $time = null) {
        if (!APP_DEBUG) {
            return;
        }

        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'time' => $time ? number_format($time * 1000, 2) . 'ms' : null
        ];

        self::debug('SQL Query', $context);
    }

    /**
     * Log de requisições HTTP
     */
    public static function request($method, $uri, $status, $responseTime = null) {
        $context = [
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'response_time' => $responseTime ? number_format($responseTime * 1000, 2) . 'ms' : null
        ];

        $level = $status >= 400 ? 'error' : 'info';
        self::log($level, "HTTP Request", $context);
    }

    /**
     * Log de exceções
     */
    public static function exception($exception, $context = []) {
        $context = array_merge([
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ], $context);

        self::error('Exception', $context);
    }

    /**
     * Limpar logs antigos
     */
    public static function clear($days = 7) {
        $logDir = storage_path('logs');;
        $files = glob($logDir . '/*.log');

        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$days} days")) {
                unlink($file);
            }
        }
    }

    /**
     * Obter estatísticas dos logs
     */
    public static function stats() {
        $logDir = storage_path('logs');
        $files = glob($logDir . '/*.log');

        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'files' => []
        ];

        foreach ($files as $file) {
            $size = filesize($file);
            $stats['total_size'] += $size;
            $stats['files'][] = [
                'name' => basename($file),
                'size' => self::formatBytes($size),
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        return $stats;
    }

    /**
     * Format bytes em KB/MB/GB
     */
    private static function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
