<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\AuthManager;

class ViewComposer
{
    private static array $composers = [];
    private static array $shared = [];

    public static function compose(string $view, callable $callback): void
    {
        self::$composers[$view] = $callback;
    }

    public static function pattern(string $pattern, callable $callback): void
    {
        self::$composers[$pattern] = $callback;
    }

    public static function global(callable $callback): void
    {
        self::$composers['*'] = $callback;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function shareData(array $data): void
    {
        self::$shared = array_merge(self::$shared, $data);
    }

    public static function execute(string $view, array &$data): void
    {
        // Apply shared data first
        $data = array_merge(self::$shared, $data);

        // Apply global composers
        if (isset(self::$composers['*'])) {
            self::$composers['*']($data);
        }

        // Apply specific composers
        foreach (self::$composers as $key => $composer) {
            if ($key === $view) {
                $composer($data);
            } elseif (str_contains($key, '*')) {
                $pattern = '/^' . str_replace('*', '.*', preg_quote($key, '/')) . '$/';
                if (preg_match($pattern, $view)) {
                    $composer($data);
                }
            }
        }
    }

    public static function withAuth(array &$data): void
    {
        $data['auth'] = [
            'user' => AuthManager::user(),
            'check' => AuthManager::check(),
            'guest' => AuthManager::guest(),
        ];
    }

    public static function withErrors(array &$data): void
    {
        $data['errors'] = Session::getFlash('_errors') ?? [];
        $data['hasErrors'] = !empty($data['errors']);
    }

    public static function withInput(array &$data): void
    {
        $data['old'] = Session::getFlash('_old_input') ?? [];
        $data['hasOld'] = !empty($data['old']);
    }

    public static function withMessages(array &$data): void
    {
        $data['messages'] = [
            'success' => Session::getFlash('success') ?? [],
            'error' => Session::getFlash('error') ?? [],
            'warning' => Session::getFlash('warning') ?? [],
            'info' => Session::getFlash('info') ?? [],
        ];
        $data['hasMessages'] = !empty($data['messages']['success']) || 
                              !empty($data['messages']['error']) || 
                              !empty($data['messages']['warning']) || 
                              !empty($data['messages']['info']);
    }

    public static function withRequest(array &$data): void
    {
        $data['request'] = [
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'ajax' => !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       ($_SERVER['SERVER_PORT'] ?? 80) == 443,
        ];
    }

    public static function withConfig(array &$data): void
    {
        global $config;
        $data['config'] = $config ?? [];
    }

    public static function withCsrf(array &$data): void
    {
        $data['csrf'] = [
            'token' => Session::token(),
            'field' => '<input type="hidden" name="_token" value="' . Session::token() . '">',
        ];
    }

    public static function withNavigation(array &$data): void
    {
        $data['navigation'] = [
            'current' => $_SERVER['REQUEST_URI'] ?? '/',
            'segments' => explode('/', trim($_SERVER['REQUEST_URI'] ?? '/', '/')),
        ];
    }

    public static function withPagination(array &$data): void
    {
        if (isset($data['items'])) {
            $page = (int) ($_GET['page'] ?? 1);
            $perPage = $data['per_page'] ?? 15;
            $total = count($data['items']);
            
            $data['pagination'] = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
                'has_more' => $page < ceil($total / $perPage),
            ];
        }
    }

    public static function withBreadcrumbs(array &$data): void
    {
        $data['breadcrumbs'] = $data['breadcrumbs'] ?? [];
    }

    public static function withMeta(array &$data): void
    {
        $data['meta'] = array_merge([
            'title' => 'My Application',
            'description' => 'Default description',
            'keywords' => 'default, keywords',
            'author' => 'Author Name',
        ], $data['meta'] ?? []);
    }

    public static function withFlash(array &$data): void
    {
        self::withErrors($data);
        self::withInput($data);
        self::withMessages($data);
    }

    public static function withCommon(array &$data): void
    {
        self::withAuth($data);
        self::withRequest($data);
        self::withConfig($data);
        self::withCsrf($data);
        self::withNavigation($data);
        self::withMeta($data);
    }

    public static function registerDefaults(): void
    {
        // Global composer for all views
        self::global(function(&$data) {
            self::withCommon($data);
        });

        // Auth views
        self::pattern('auth.*', function(&$data) {
            self::withFlash($data);
        });

        // Dashboard views
        self::pattern('dashboard.*', function(&$data) {
            self::withAuth($data);
            self::withBreadcrumbs($data);
        });

        // Profile views
        self::pattern('profile.*', function(&$data) {
            self::withAuth($data);
            self::withBreadcrumbs($data);
        });

        // Admin views
        self::pattern('admin.*', function(&$data) {
            self::withAuth($data);
            self::withBreadcrumbs($data);
            $data['admin_section'] = true;
        });

        // API views
        self::pattern('api.*', function(&$data) {
            $data['api'] = true;
            $data['version'] = '1.0.0';
        });
    }

    public static function getComposers(): array
    {
        return self::$composers;
    }

    public static function getShared(): array
    {
        return self::$shared;
    }

    public static function clear(): void
    {
        self::$composers = [];
        self::$shared = [];
    }
}
