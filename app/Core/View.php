<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\File;
use App\Core\Log;

class View
{
    private static array $data = [];
    private static array $composers = [];
    private static string $path = __DIR__ . '/../../templates';
    private static string $extension = '.php';
    private static ?string $layout = null;

    public static function configure(array $config): void
    {
        self::$path = $config['path'] ?? __DIR__ . '/../../templates';
        self::$extension = $config['extension'] ?? '.php';
        self::$layout = $config['layout'] ?? null;
    }

    public static function make(string $template, array $data = []): string
    {
        $templatePath = self::resolveTemplatePath($template);
        
        if (!File::exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        self::shareData($data);
        self::executeComposers($template);
        
        $content = self::renderTemplate($templatePath);
        
        if (self::$layout) {
            $content = self::renderLayout($content);
        }
        
        return $content;
    }

    public static function render(string $template, array $data = []): void
    {
        echo self::make($template, $data);
    }

    public static function exists(string $template): bool
    {
        $templatePath = self::resolveTemplatePath($template);
        return File::exists($templatePath);
    }

    public static function share(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    public static function shareData(array $data): void
    {
        self::$data = array_merge(self::$data, $data);
    }

    public static function composer(string $template, callable $callback): void
    {
        self::$composers[$template] = $callback;
    }

    public static function composerPattern(string $pattern, callable $callback): void
    {
        self::$composers[$pattern] = $callback;
    }

    public static function layout(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function section(string $name, ?string $content = null): string
    {
        if ($content !== null) {
            self::$data['_sections'][$name] = $content;
        }
        
        return self::$data['_sections'][$name] ?? '';
    }

    public static function startSection(string $name): void
    {
        ob_start();
        self::$data['_current_section'] = $name;
    }

    public static function endSection(): void
    {
        $content = ob_get_clean();
        $section = self::$data['_current_section'] ?? null;
        
        if ($section) {
            self::section($section, $content);
            unset(self::$data['_current_section']);
        }
    }

    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::section($name, $default);
    }

    public static function include(string $template, array $data = []): string
    {
        $templatePath = self::resolveTemplatePath($template);
        
        if (!File::exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        $originalData = self::$data;
        self::$data = array_merge(self::$data, $data);
        
        $content = self::renderTemplate($templatePath);
        
        self::$data = $originalData;
        
        return $content;
    }

    public static function asset(string $path, bool $version = true): string
    {
        $baseUrl = self::$data['app_url'] ?? '';
        $manifestPath = self::$path . '/manifest.json';
        
        if ($version && File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            return $baseUrl . ($manifest[$path] ?? $path);
        }
        
        if ($version && File::exists(self::$path . '/' . $path)) {
            $path .= '?v=' . File::lastModified(self::$path . '/' . $path);
        }
        
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function mix(string $path): string
    {
        $manifestPath = self::$path . '/mix-manifest.json';
        
        if (!File::exists($manifestPath)) {
            throw new \RuntimeException('Mix manifest not found');
        }
        
        $manifest = json_decode(File::get($manifestPath), true);
        
        if (!isset($manifest[$path])) {
            throw new \RuntimeException("Unable to locate Mix file: {$path}");
        }
        
        $baseUrl = self::$data['app_url'] ?? '';
        return $baseUrl . $manifest[$path];
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function e(string $value): string
    {
        return self::escape($value);
    }

    public static function csrf(): string
    {
        return Session::token();
    }

    public static function csrfField(): string
    {
        $token = self::csrf();
        return '<input type="hidden" name="_token" value="' . self::escape($token) . '">';
    }

    public static function method(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . self::escape($method) . '">';
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return Session::getFlash('_old_input')[$key] ?? $default;
    }

    public static function error(string $key, string $default = ''): string
    {
        return Session::getFlash('_errors')[$key] ?? $default;
    }

    public static function hasError(string $key): bool
    {
        return !empty(Session::getFlash('_errors')[$key]);
    }

    public static function errors(): array
    {
        return Session::getFlash('_errors') ?? [];
    }

    public static function hasErrors(): bool
    {
        return !empty(self::errors());
    }

    public static function flash(string $key, mixed $default = ''): mixed
    {
        return Session::getFlash($key, $default);
    }

    public static function auth(): ?object
    {
        $user = Session::get('auth_user');
        if ($user) {
            return (object) $user;
        }
        return null;
    }

    public static function guest(): bool
    {
        return self::auth() === null;
    }

    public static function user(): ?object
    {
        return self::auth();
    }

    public static function can(string $ability): bool
    {
        $user = self::user();
        return $user && method_exists($user, 'can') ? $user->can($ability) : false;
    }

    public static function cannot(string $ability): bool
    {
        return !self::can($ability);
    }

    public static function route(string $name, array $params = []): string
    {
        if (class_exists('\App\Core\Route')) {
            return \App\Core\Route::url($name, $params);
        }
        
        return '#';
    }

    public static function url(string $path = ''): string
    {
        $baseUrl = self::$data['app_url'] ?? '';
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $config = self::$data['config'] ?? [];
        
        foreach ($keys as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }
        
        return $config;
    }

    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        if (class_exists('\App\Core\Lang')) {
            return \App\Core\Lang::get($key, $replace, $locale);
        }
        
        return $key;
    }

    public static function __(string $key, array $replace = []): string
    {
        return self::trans($key, $replace);
    }

    public static function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    public static function formatDateTime(\DateTimeInterface $date, string $format = 'Y-m-d H:i:s'): string
    {
        return $date->format($format);
    }

    public static function diffForHumans(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);
        
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        
        return 'Just now';
    }

    public static function paginate(array $items, int $perPage = 15, array $options = []): array
    {
        $page = (int) ($_GET['page'] ?? 1);
        $total = count($items);
        $lastPage = (int) ceil($total / $perPage);
        
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($items, $offset, $perPage);
        
        return [
            'data' => $sliced,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'has_more' => $page < $lastPage,
            'links' => self::generatePaginationLinks($page, $lastPage, $options),
        ];
    }

    private static function generatePaginationLinks(int $currentPage, int $lastPage, array $options): array
    {
        $onEachSide = $options['on_each_side'] ?? 3;
        $onEnds = $options['on_ends'] ?? 1;
        
        $links = [];
        
        // Previous
        if ($currentPage > 1) {
            $links[] = [
                'url' => self::buildPageUrl($currentPage - 1),
                'label' => 'Previous',
                'active' => false,
                'disabled' => false,
            ];
        }
        
        // Pages
        $window = self::buildPaginationWindow($currentPage, $lastPage, $onEachSide, $onEnds);
        
        foreach ($window as $page) {
            if ($page === '...') {
                $links[] = [
                    'url' => null,
                    'label' => '...',
                    'active' => false,
                    'disabled' => true,
                ];
            } else {
                $links[] = [
                    'url' => self::buildPageUrl($page),
                    'label' => (string) $page,
                    'active' => $page === $currentPage,
                    'disabled' => false,
                ];
            }
        }
        
        // Next
        if ($currentPage < $lastPage) {
            $links[] = [
                'url' => self::buildPageUrl($currentPage + 1),
                'label' => 'Next',
                'active' => false,
                'disabled' => false,
            ];
        }
        
        return $links;
    }

    private static function buildPaginationWindow(int $currentPage, int $lastPage, int $onEachSide, int $onEnds): array
    {
        $window = [];
        
        // First pages
        for ($i = 1; $i <= min($onEnds, $lastPage); $i++) {
            $window[] = $i;
        }
        
        // Current window
        $start = max($onEnds + 1, $currentPage - $onEachSide);
        $end = min($lastPage - $onEnds, $currentPage + $onEachSide);
        
        if ($start > $onEnds + 1) {
            $window[] = '...';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i > 0 && $i <= $lastPage) {
                $window[] = $i;
            }
        }
        
        if ($end < $lastPage - $onEnds) {
            $window[] = '...';
        }
        
        // Last pages
        for ($i = max($lastPage - $onEnds + 1, $end + 1); $i <= $lastPage; $i++) {
            $window[] = $i;
        }
        
        return array_unique($window);
    }

    private static function buildPageUrl(int $page): string
    {
        $query = $_GET;
        $query['page'] = $page;
        
        return $_SERVER['REQUEST_URI'] . '?' . http_build_query($query);
    }

    private static function resolveTemplatePath(string $template): string
    {
        return self::$path . '/' . str_replace('.', '/', $template) . self::$extension;
    }

    private static function renderTemplate(string $templatePath): string
    {
        extract(self::$data, EXTR_SKIP);
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private static function renderLayout(string $content): string
    {
        $layoutPath = self::$path . '/' . self::$layout . self::$extension;
        
        if (!File::exists($layoutPath)) {
            throw new \RuntimeException("Layout not found: " . self::$layout);
        }
        
        self::$data['content'] = $content;
        
        return self::renderTemplate($layoutPath);
    }

    private static function executeComposers(string $template): void
    {
        foreach (self::$composers as $key => $composer) {
            if ($key === $template) {
                $composer(self::$data);
            } elseif (str_contains($key, '*')) {
                $pattern = '/^' . str_replace('*', '.*', preg_quote($key, '/')) . '$/';
                if (preg_match($pattern, $template)) {
                    $composer(self::$data);
                }
            }
        }
    }

    public static function clearData(): void
    {
        self::$data = [];
    }

    public static function clearComposers(): void
    {
        self::$composers = [];
    }

    public static function getData(): array
    {
        return self::$data;
    }

    public static function getComposers(): array
    {
        return self::$composers;
    }
}
