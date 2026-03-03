<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Log;
use Throwable;

class ErrorHandler
{
    private static array $config = [
        'debug' => false,
        'error_reporting' => E_ALL,
        'display_errors' => false,
        'log_errors' => true,
        'error_log' => null,
        'ignore_errors' => [],
        'fatal_error_handler' => true,
    ];

    private static array $levels = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
        self::applySettings();
    }

    public static function register(): void
    {
        error_reporting(self::$config['error_reporting']);
        ini_set('display_errors', self::$config['display_errors'] ? '1' : '0');
        ini_set('log_errors', self::$config['log_errors'] ? '1' : '0');
        
        if (self::$config['error_log']) {
            ini_set('error_log', self::$config['error_log']);
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        
        if (self::$config['fatal_error_handler']) {
            register_shutdown_function([self::class, 'handleFatalError']);
        }
    }

    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        $error = [
            'type' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'level' => self::$levels[$level] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        ];

        if (self::shouldIgnoreError($error)) {
            return false;
        }

        self::logError($error);

        if (self::$config['display_errors']) {
            self::displayError($error);
        }

        return true;
    }

    public static function handleException(Throwable $exception): void
    {
        $error = [
            'type' => E_ERROR,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'level' => 'Exception',
            'exception' => get_class($exception),
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => $exception->getTrace(),
        ];

        self::logError($error);

        if (self::$config['display_errors']) {
            self::displayException($exception);
        }
    }

    public static function handleFatalError(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            self::handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private static function shouldIgnoreError(array $error): bool
    {
        foreach (self::$config['ignore_errors'] as $pattern) {
            if (self::matchesPattern($error, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    private static function matchesPattern(array $error, string $pattern): bool
    {
        if (str_starts_with($pattern, 'file:')) {
            $filePattern = substr($pattern, 5);
            return fnmatch($filePattern, $error['file']);
        }
        
        if (str_starts_with($pattern, 'message:')) {
            $messagePattern = substr($pattern, 8);
            return fnmatch($messagePattern, $error['message']);
        }
        
        if (str_starts_with($pattern, 'level:')) {
            $levelPattern = substr($pattern, 6);
            return fnmatch($levelPattern, $error['level']);
        }
        
        return false;
    }

    private static function logError(array $error): void
    {
        if (!self::$config['log_errors']) {
            return;
        }

        $context = [
            'type' => $error['type'],
            'level' => $error['level'],
            'file' => $error['file'],
            'line' => $error['line'],
            'timestamp' => $error['timestamp'],
        ];

        if (isset($error['exception'])) {
            $context['exception'] = $error['exception'];
        }

        Log::error($error['message'], $context);
    }

    private static function displayError(array $error): void
    {
        if (!self::$config['debug']) {
            self::displayProductionError();
            return;
        }

        self::displayDevelopmentError($error);
    }

    private static function displayException(Throwable $exception): void
    {
        if (!self::$config['debug']) {
            self::displayProductionError();
            return;
        }

        $error = [
            'type' => E_ERROR,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'level' => 'Exception',
            'exception' => get_class($exception),
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => $exception->getTrace(),
        ];

        self::displayDevelopmentError($error);
    }

    private static function displayDevelopmentError(array $error): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('HTTP/1.1 500 Internal Server Error');
        }

        echo self::getErrorPage($error);
    }

    private static function displayProductionError(): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('HTTP/1.1 500 Internal Server Error');
        }

        echo self::getProductionErrorPage();
    }

    private static function getErrorPage(array $error): string
    {
        $trace = self::formatTrace($error['trace'] ?? []);
        $context = self::getContext($error['file'], $error['line']);

        ob_start();
        include __DIR__ . '/../../templates/errors/debug.php';
        return ob_get_clean();
    }

    private static function getProductionErrorPage(): string
    {
        ob_start();
        include __DIR__ . '/../../templates/errors/production.php';
        return ob_get_clean();
    }

    private static function formatTrace(array $trace): string
    {
        $formatted = [];
        
        foreach ($trace as $i => $trace) {
            $formatted[] = sprintf(
                '#%d %s(%d): %s',
                $i,
                $trace['file'] ?? 'unknown',
                $trace['line'] ?? 0,
                $trace['function'] ?? 'unknown'
            );
        }
        
        return implode("\n", $formatted);
    }

    private static function getContext(string $file, int $line): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $context = [];
        
        $start = max(0, $line - 5);
        $end = min(count($lines), $line + 5);
        
        for ($i = $start; $i < $end; $i++) {
            $context[$i + 1] = [
                'line' => $i + 1,
                'code' => $lines[$i] ?? '',
                'current' => ($i + 1) === $line,
            ];
        }
        
        return $context;
    }

    public static function getLastError(): ?array
    {
        $error = error_get_last();
        
        if (!$error) {
            return null;
        }

        return [
            'type' => $error['type'],
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'level' => self::$levels[$error['type']] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public static function addIgnorePattern(string $pattern): void
    {
        self::$config['ignore_errors'][] = $pattern;
    }

    public static function removeIgnorePattern(string $pattern): void
    {
        self::$config['ignore_errors'] = array_filter(
            self::$config['ignore_errors'],
            fn($p) => $p !== $pattern
        );
    }

    public static function clearIgnorePatterns(): void
    {
        self::$config['ignore_errors'] = [];
    }

    public static function isDebugMode(): bool
    {
        return self::$config['debug'];
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function getLevels(): array
    {
        return self::$levels;
    }

    // Custom error pages
    public static function customErrorPage(int $statusCode, string $message = '', ?string $template = null): void
    {
        if (!headers_sent()) {
            $statusText = self::getStatusText($statusCode);
            header("HTTP/1.1 {$statusCode} {$statusText}");
            header('Content-Type: text/html; charset=UTF-8');
        }

        if ($template && file_exists($template)) {
            include $template;
        } else {
            echo self::generateErrorPage($statusCode, $message);
        }
    }

    private static function getStatusText(int $statusCode): string
    {
        $statusTexts = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return $statusTexts[$statusCode] ?? 'Unknown Status';
    }

    private static function generateErrorPage(int $statusCode, string $message): string
    {
        $title = self::getStatusText($statusCode);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$statusCode} - {$title}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .error-code {
            font-size: 48px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        .error-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .error-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">{$statusCode}</div>
        <div class="error-title">{$title}</div>
        <div class="error-message">{$message}</div>
        <a href="/" class="error-link">Go Home</a>
    </div>
</body>
</html>
HTML;
    }
}
