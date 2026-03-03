<?php

declare(strict_types=1);

/**
 * Helper Functions for Minimal PHP Framework
 * General utility functions for common operations
 */

if (!function_exists('dd')) {
    /**
     * Dump and die - Debug helper function
     */
    function dd(mixed ...$vars): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; margin: 10px; font-family: Monaco, monospace;">';
        
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
     * Dump variable without dying
     */
    function dump(mixed ...$vars): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; margin: 10px; font-family: Monaco, monospace;">';
        
        foreach ($vars as $var) {
            var_dump($var);
            echo '<hr>';
        }
        
        echo '</pre>';
    }
}

if (!function_exists('asset')) {
    /**
     * Get asset URL
     */
    function asset(string $path): string
    {
        return \App\Core\AssetManager::asset($path);
    }
}

if (!function_exists('mix')) {
    /**
     * Get Mix manifest URL
     */
    function mix(string $path): string
    {
        return \App\Core\AssetManager::mix($path);
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL for given path
     */
    function url(string $path = ''): string
    {
        return \App\Core\View::url($path);
    }
}

if (!function_exists('route')) {
    /**
     * Generate URL for named route
     */
    function route(string $name, array $params = []): string
    {
        return \App\Core\View::route($name, $params);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return \App\Core\View::old($key, $default);
    }
}

if (!function_exists('error')) {
    /**
     * Get validation error
     */
    function error(string $key, string $default = ''): string
    {
        return \App\Core\View::error($key, $default);
    }
}

if (!function_exists('hasError')) {
    /**
     * Check if field has errors
     */
    function hasError(string $key): bool
    {
        return \App\Core\View::hasError($key);
    }
}

if (!function_exists('csrf')) {
    /**
     * Get CSRF token
     */
    function csrf(): string
    {
        return \App\Core\View::csrf();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Get CSRF token field HTML
     */
    function csrf_field(): string
    {
        return \App\Core\View::csrfField();
    }
}

if (!function_exists('method_field')) {
    /**
     * Get HTTP method override field HTML
     */
    function method_field(string $method): string
    {
        return \App\Core\View::method($method);
    }
}

if (!function_exists('auth')) {
    /**
     * Get authenticated user
     */
    function auth(): ?object
    {
        return \App\Core\View::auth();
    }
}

if (!function_exists('guest')) {
    /**
     * Check if user is guest
     */
    function guest(): bool
    {
        return \App\Core\View::guest();
    }
}

if (!function_exists('user')) {
    /**
     * Get current user
     */
    function user(): ?object
    {
        return \App\Core\View::user();
    }
}

if (!function_exists('can')) {
    /**
     * Check user permission
     */
    function can(string $ability): bool
    {
        return \App\Core\View::can($ability);
    }
}

if (!function_exists('cannot')) {
    /**
     * Check user cannot permission
     */
    function cannot(string $ability): bool
    {
        return \App\Core\View::cannot($ability);
    }
}

if (!function_exists('__')) {
    /**
     * Get translation
     */
    function __(string $key, array $replace = []): string
    {
        return \App\Core\View::trans($key, $replace);
    }
}

if (!function_exists('trans')) {
    /**
     * Get translation
     */
    function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return \App\Core\View::trans($key, $replace, $locale);
    }
}

if (!function_exists('config')) {
    /**
     * Get config value
     */
    function config(string $key, mixed $default = null): mixed
    {
        return \App\Core\View::config($key, $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Get flash message
     */
    function flash(string $key, mixed $default = ''): mixed
    {
        return \App\Core\View::flash($key, $default);
    }
}

if (!function_exists('now')) {
    /**
     * Get current date/time
     */
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return \App\Core\View::now($format);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date(\DateTimeInterface $date, string $format = 'Y-m-d H:i:s'): string
    {
        return \App\Core\View::formatDateTime($date, $format);
    }
}

if (!function_exists('diff_for_humans')) {
    /**
     * Get human readable time difference
     */
    function diff_for_humans(\DateTimeInterface $date): string
    {
        return \App\Core\View::diffForHumans($date);
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit string length
     */
    function str_limit(string $string, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        
        return mb_substr($string, 0, $limit) . $end;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Convert string to slug
     */
    function str_slug(string $string): string
    {
        return \App\Core\Security\InputSanitizer::sanitizeSlug($string);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string
     */
    function str_random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('str_uuid')) {
    /**
     * Generate UUID v4
     */
    function str_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x0fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('array_get')) {
    /**
     * Get value from nested array using dot notation
     */
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

if (!function_exists('array_set')) {
    /**
     * Set value in nested array using dot notation
     */
    function array_set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $k) {
            if (!is_array($current)) {
                $current = [];
            }
            
            if (!array_key_exists($k, $current)) {
                $current[$k] = [];
            }
            
            $current = &$current[$k];
        }
        
        $current = $value;
    }
}

if (!function_exists('array_has')) {
    /**
     * Check if nested array has key using dot notation
     */
    function array_has(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }
}

if (!function_exists('array_first')) {
    /**
     * Get first element of array
     */
    function array_first(array $array): mixed
    {
        return empty($array) ? null : reset($array);
    }
}

if (!function_exists('array_last')) {
    /**
     * Get last element of array
     */
    function array_last(array $array): mixed
    {
        return empty($array) ? null : end($array);
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck values from array of arrays
     */
    function array_pluck(array $array, string $key): array
    {
        return array_map(fn($item) => $item[$key] ?? null, $array);
    }
}

if (!function_exists('array_where')) {
    /**
     * Filter array by callback
     */
    function array_where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}

if (!function_exists('array_sort')) {
    /**
     * Sort array by callback
     */
    function array_sort(array $array, callable $callback): array
    {
        uasort($array, $callback);
        return $array;
    }
}

if (!function_exists('bytes_to_human')) {
    /**
     * Convert bytes to human readable format
     */
    function bytes_to_human(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

if (!function_exists('human_to_bytes')) {
    /**
     * Convert human readable to bytes
     */
    function human_to_bytes(string $human): int
    {
        $units = ['B' => 1, 'KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824, 'TB' => 1099511627776];
        
        $human = strtoupper(trim($human));
        $number = (float) $human;
        
        foreach ($units as $unit => $bytes) {
            if (str_ends_with($human, $unit)) {
                return (int) ($number * $bytes);
            }
        }
        
        return (int) $number;
    }
}

if (!function_exists('is_json')) {
    /**
     * Check if string is valid JSON
     */
    function is_json(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('is_email')) {
    /**
     * Validate email
     */
    function is_email(string $email): bool
    {
        return \App\Core\Security\InputSanitizer::validateEmail($email);
    }
}

if (!function_exists('is_url')) {
    /**
     * Validate URL
     */
    function is_url(string $url): bool
    {
        return \App\Core\Security\InputSanitizer::validateUrl($url);
    }
}

if (!function_exists('is_cpf')) {
    /**
     * Validate CPF
     */
    function is_cpf(string $cpf): bool
    {
        return \App\Core\Security\InputSanitizer::validateCpf($cpf);
    }
}

if (!function_exists('is_cnpj')) {
    /**
     * Validate CNPJ
     */
    function is_cnpj(string $cnpj): bool
    {
        return \App\Core\Security\InputSanitizer::validateCnpj($cnpj);
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize input
     */
    function sanitize(string $input): string
    {
        return \App\Core\Security\InputSanitizer::clean($input);
    }
}

if (!function_exists('escape')) {
    /**
     * Escape HTML entities
     */
    function escape(string $string): string
    {
        return \App\Core\View::escape($string);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities (alias)
     */
    function e(string $string): string
    {
        return \App\Core\View::escape($string);
    }
}

if (!function_exists('cache')) {
    /**
     * Cache helper
     */
    function cache(): \App\Core\Cache
    {
        return \App\Core\Cache::getInstance();
    }
}

if (!function_exists('session')) {
    /**
     * Session helper
     */
    function session(): \App\Core\Session
    {
        return \App\Core\Session::getInstance();
    }
}

if (!function_exists('auth_manager')) {
    /**
     * Auth manager helper
     */
    function auth_manager(): \App\Core\AuthManager
    {
        return \App\Core\AuthManager::getInstance();
    }
}

if (!function_exists('logger')) {
    /**
     * Logger helper
     */
    function logger(): \App\Core\Log
    {
        return \App\Core\Log::getInstance();
    }
}

if (!function_exists('validator')) {
    /**
     * Validator helper
     */
    function validator(array $data, array $rules, array $messages = []): \App\Core\RequestValidator
    {
        return \App\Core\RequestValidator::make($data, $rules, $messages);
    }
}

if (!function_exists('redirect')) {
    /**
     * Create redirect response
     */
    function redirect(string $url, int $status = 302): \App\Core\Response
    {
        return (new \App\Core\Response())->redirect($url, $status);
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back
     */
    function back(): \App\Core\Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return redirect($referer);
    }
}

if (!function_exists('response')) {
    /**
     * Create response
     */
    function response(mixed $content = '', int $status = 200, array $headers = []): \App\Core\Response
    {
        return new \App\Core\Response($content, $status, $headers);
    }
}

if (!function_exists('json')) {
    /**
     * Create JSON response
     */
    function json(mixed $data, int $status = 200, array $headers = []): \App\Core\Response
    {
        return response()->json($data, $status, $headers);
    }
}

if (!function_exists('view')) {
    /**
     * Render view
     */
    function view(string $template, array $data = []): string
    {
        return \App\Core\View::make($template, $data);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP error
     */
    function abort(int $code, string $message = ''): void
    {
        \App\Core\ErrorHandler::customErrorPage($code, $message);
        exit;
    }
}

if (!function_exists('abort_if')) {
    /**
     * Abort if condition is true
     */
    function abort_if(bool $condition, int $code, string $message = ''): void
    {
        if ($condition) {
            abort($code, $message);
        }
    }
}

if (!function_exists('abort_unless')) {
    /**
     * Abort unless condition is true
     */
    function abort_unless(bool $condition, int $code, string $message = ''): void
    {
        if (!$condition) {
            abort($code, $message);
        }
    }
}

if (!function_exists('collect')) {
    /**
     * Create collection from array
     */
    function collect(array $items): \App\Core\Collection
    {
        return new \App\Core\Collection($items);
    }
}

if (!function_exists('request')) {
    /**
     * Get current request
     */
    function request(): \App\Core\Request
    {
        return \App\Core\Request::getInstance();
    }
}

if (!function_exists('response_time')) {
    /**
     * Get response time in milliseconds
     */
    function response_time(): float
    {
        return round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
    }
}

if (!function_exists('memory_usage')) {
    /**
     * Get memory usage
     */
    function memory_usage(): string
    {
        return bytes_to_human(memory_get_usage(true));
    }
}

if (!function_exists('peak_memory_usage')) {
    /**
     * Get peak memory usage
     */
    function peak_memory_usage(): string
    {
        return bytes_to_human(memory_get_peak_usage(true));
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            'empty' => '',
            default => $value,
        };
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path
     */
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/../' . ltrim($path, '/');
    }
}

if (!function_exists('app_path')) {
    /**
     * Get app path
     */
    function app_path(string $path = ''): string
    {
        return base_path('app/' . ltrim($path, '/'));
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public path
     */
    function public_path(string $path = ''): string
    {
        return base_path('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('config_path')) {
    /**
     * Get config path
     */
    function config_path(string $path = ''): string
    {
        return base_path(ltrim($path, '/'));
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get resources path
     */
    function resource_path(string $path = ''): string
    {
        return base_path('resources/' . ltrim($path, '/'));
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get class basename
     */
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Get all traits used by a class
     */
    function class_uses_recursive(string|object $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        $traits = [];
        
        foreach (class_parents($class) as $parent) {
            $traits += class_uses_recursive($parent);
        }
        
        $traits += class_uses($class);
        
        return $traits;
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Get all traits used by a trait
     */
    function trait_uses_recursive(string $trait): array
    {
        $traits = [];
        
        foreach (class_uses($trait) as $trait) {
            $traits += trait_uses_recursive($trait);
        }
        
        return $traits;
    }
}

if (!function_exists('value')) {
    /**
     * Return the value if it's a callable, otherwise return the value
     */
    function value(mixed $value): mixed
    {
        return is_callable($value) ? $value() : $value;
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value
     */
    function tap(mixed $value, callable $callback): mixed
    {
        $callback($value);
        return $value;
    }
}

if (!function_exists('throw_if')) {
    /**
     * Throw exception if condition is true
     */
    function throw_if(bool $condition, \Throwable|string $exception, string $message = ''): void
    {
        if ($condition) {
            throw is_string($exception) ? new \Exception($message) : $exception;
        }
    }
}

if (!function_exists('throw_unless')) {
    /**
     * Throw exception unless condition is true
     */
    function throw_unless(bool $condition, \Throwable|string $exception, string $message = ''): void
    {
        if (!$condition) {
            throw is_string($exception) ? new \Exception($message) : $exception;
        }
    }
}
