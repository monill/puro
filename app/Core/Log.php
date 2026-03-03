<?php

declare(strict_types=1);

namespace App\Core;

class Log
{
    private static array $config = [
        'path' => __DIR__ . '/../../storage/logs',
        'level' => 'debug',
        'max_files' => 30,
        'date_format' => 'Y-m-d H:i:s',
        'log_format' => '[%datetime%] %level_name%: %message% %context% %extra%',
    ];
    
    private static array $levels = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function notice(string $message, array $context = []): void
    {
        self::log('notice', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }

    public static function alert(string $message, array $context = []): void
    {
        self::log('alert', $message, $context);
    }

    public static function emergency(string $message, array $context = []): void
    {
        self::log('emergency', $message, $context);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (!self::shouldLog($level)) {
            return;
        }

        $logEntry = self::formatLogEntry($level, $message, $context);
        self::writeToFile($logEntry);
    }

    private static function shouldLog(string $level): bool
    {
        $currentLevel = self::$levels[$level] ?? 0;
        $minimumLevel = self::$levels[self::$config['level']] ?? 0;
        
        return $currentLevel >= $minimumLevel;
    }

    private static function formatLogEntry(string $level, string $message, array $context): string
    {
        $datetime = date(self::$config['date_format']);
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $extraStr = self::getExtraInfo();
        
        $logEntry = str_replace(
            ['%datetime%', '%level_name%', '%message%', '%context%', '%extra%'],
            [$datetime, strtoupper($level), $message, $contextStr, $extraStr],
            self::$config['log_format']
        );
        
        return trim($logEntry) . PHP_EOL;
    }

    private static function getExtraInfo(): string
    {
        $extra = [];
        
        if (isset($_SERVER['REQUEST_URI'])) {
            $extra['url'] = $_SERVER['REQUEST_URI'];
        }
        
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $extra['method'] = $_SERVER['REQUEST_METHOD'];
        }
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $extra['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $extra['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        if (isset($backtrace[2])) {
            $caller = $backtrace[2];
            $extra['file'] = $caller['file'] ?? 'unknown';
            $extra['line'] = $caller['line'] ?? 0;
        }
        
        return !empty($extra) ? json_encode($extra, JSON_UNESCAPED_UNICODE) : '';
    }

    private static function writeToFile(string $logEntry): void
    {
        $logFile = self::getLogFilePath();
        
        $directory = dirname($logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        self::rotateLogs();
    }

    private static function getLogFilePath(): string
    {
        $date = date('Y-m-d');
        return self::$config['path'] . DIRECTORY_SEPARATOR . "app-{$date}.log";
    }

    private static function rotateLogs(): void
    {
        $logFiles = glob(self::$config['path'] . DIRECTORY_SEPARATOR . 'app-*.log');
        
        if (count($logFiles) <= self::$config['max_files']) {
            return;
        }
        
        usort($logFiles, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $filesToDelete = array_slice($logFiles, 0, count($logFiles) - self::$config['max_files']);
        
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    public static function sql(string $query, array $bindings = [], ?float $time = null): void
    {
        $message = $query;
        
        if (!empty($bindings)) {
            $message .= ' | Bindings: ' . json_encode($bindings, JSON_UNESCAPED_UNICODE);
        }
        
        if ($time !== null) {
            $message .= ' | Time: ' . number_format($time, 4) . 's';
        }
        
        self::debug($message, ['type' => 'sql']);
    }

    public static function request(array $data = []): void
    {
        $defaultData = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'headers' => self::getRequestHeaders(),
            'body' => self::getRequestBody(),
        ];
        
        $data = array_merge($defaultData, $data);
        
        self::info('HTTP Request', $data);
    }

    public static function response(int $statusCode, array $data = []): void
    {
        $defaultData = [
            'status_code' => $statusCode,
            'status_text' => self::getStatusText($statusCode),
        ];
        
        $data = array_merge($defaultData, $data);
        
        self::info('HTTP Response', $data);
    }

    private static function getRequestHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }

    private static function getRequestBody(): array
    {
        $body = file_get_contents('php://input');
        
        if (empty($body)) {
            return [];
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        return ['raw' => $body];
    }

    private static function getStatusText(int $statusCode): string
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];
        
        return $statusTexts[$statusCode] ?? 'Unknown';
    }

    public static function exception(\Throwable $exception, array $context = []): void
    {
        $data = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
        
        $data = array_merge($data, $context);
        
        self::error($exception->getMessage(), $data);
    }

    public static function clear(): void
    {
        $logFiles = glob(self::$config['path'] . DIRECTORY_SEPARATOR . '*.log');
        
        foreach ($logFiles as $file) {
            unlink($file);
        }
    }

    public static function getLogs(?string $date = null, ?string $level = null): array
    {
        $date = $date ?? date('Y-m-d');
        $logFile = self::$config['path'] . DIRECTORY_SEPARATOR . "app-{$date}.log";
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];
        
        foreach ($lines as $line) {
            $parsed = self::parseLogLine($line);
            
            if ($parsed && (!$level || strtolower($parsed['level']) === strtolower($level))) {
                $logs[] = $parsed;
            }
        }
        
        return $logs;
    }

    private static function parseLogLine(string $line): ?array
    {
        if (!preg_match('/^\[([^\]]+)\]\s+(\w+):\s+(.+)$/', $line, $matches)) {
            return null;
        }
        
        $datetime = $matches[1];
        $level = $matches[2];
        $message = $matches[3];
        
        $context = [];
        $extra = [];
        
        if (preg_match('/\s({.+})\s*$/', $message, $jsonMatches)) {
            $jsonData = json_decode($jsonMatches[1], true);
            
            if ($jsonData !== null) {
                if (isset($jsonData['type']) && $jsonData['type'] === 'sql') {
                    $context = $jsonData;
                } else {
                    $extra = $jsonData;
                }
                
                $message = str_replace($jsonMatches[0], '', $message);
            }
        }
        
        return [
            'datetime' => $datetime,
            'level' => $level,
            'message' => trim($message),
            'context' => $context,
            'extra' => $extra,
        ];
    }

    public static function tail(?string $date = null, int $lines = 50): array
    {
        $date = $date ?? date('Y-m-d');
        $logFile = self::$config['path'] . DIRECTORY_SEPARATOR . "app-{$date}.log";
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $content = file_get_contents($logFile);
        $allLines = explode(PHP_EOL, trim($content));
        $lastLines = array_slice($allLines, -$lines);
        
        $logs = [];
        
        foreach ($lastLines as $line) {
            $parsed = self::parseLogLine($line);
            
            if ($parsed) {
                $logs[] = $parsed;
            }
        }
        
        return $logs;
    }

    public static function getConfig(): array
    {
        return self::$config;
    }
}
