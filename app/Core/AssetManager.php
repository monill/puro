<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\File;

class AssetManager
{
    private static array $config = [
        'public_path' => __DIR__ . '/../../public',
        'assets_path' => __DIR__ . '/../../public/assets',
        'versioning' => true,
        'minify' => false,
        'cache_busting' => true,
    ];
    
    private static array $manifest = [];
    private static array $versioned = [];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
        self::loadManifest();
    }

    public static function css(string $path, array $attributes = []): string
    {
        $url = self::asset($path);
        $attributes = array_merge([
            'rel' => 'stylesheet',
            'href' => $url,
        ], $attributes);

        return '<link ' . self::buildAttributes($attributes) . '>';
    }

    public static function js(string $path, array $attributes = []): string
    {
        $url = self::asset($path);
        $attributes = array_merge([
            'src' => $url,
        ], $attributes);

        return '<script ' . self::buildAttributes($attributes) . '></script>';
    }

    public static function img(string $path, array $attributes = []): string
    {
        $url = self::asset($path);
        $attributes = array_merge([
            'src' => $url,
            'alt' => pathinfo($path, PATHINFO_FILENAME),
        ], $attributes);

        return '<img ' . self::buildAttributes($attributes) . '>';
    }

    public static function asset(string $path): string
    {
        if (self::isExternal($path)) {
            return $path;
        }

        $versionedPath = self::getVersionedPath($path);
        $baseUrl = self::getBaseUrl();
        
        return $baseUrl . '/' . ltrim($versionedPath, '/');
    }

    public static function mix(string $path): string
    {
        if (empty(self::$manifest)) {
            throw new \RuntimeException('Mix manifest not found. Run npm run dev or npm run prod.');
        }

        if (!isset(self::$manifest[$path])) {
            throw new \RuntimeException("Unable to locate Mix file: {$path}");
        }

        $baseUrl = self::getBaseUrl();
        return $baseUrl . self::$manifest[$path];
    }

    public static function version(string $path): string
    {
        if (self::isExternal($path)) {
            return $path;
        }

        $fullPath = self::$config['public_path'] . '/' . ltrim($path, '/');
        
        if (!File::exists($fullPath)) {
            return $path;
        }

        $timestamp = File::lastModified($fullPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $pathWithoutExt = substr($path, 0, -(strlen($extension) + 1));
        
        return "{$pathWithoutExt}.v{$timestamp}.{$extension}";
    }

    public static function minify(string $content, string $type): string
    {
        if (!self::$config['minify']) {
            return $content;
        }

        return match ($type) {
            'css' => self::minifyCss($content),
            'js' => self::minifyJs($content),
            default => $content,
        };
    }

    private static function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove unnecessary semicolons
        $css = preg_replace('/;\s*}/', '}', $css);
        
        // Remove spaces around braces and colons
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/\s*}\s*/', '}', $css);
        $css = preg_replace('/\s*;\s*/', ';', $css);
        $css = preg_replace('/\s*:\s*/', ':', $css);
        
        return trim($css);
    }

    private static function minifyJs(string $js): string
    {
        // Remove comments
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove spaces around operators
        $js = preg_replace('/\s*([=+\-*/<>!&|,{}();])\s*/', '$1', $js);
        
        return trim($js);
    }

    public static function inline(string $path, string $type = 'auto'): string
    {
        $fullPath = self::$config['public_path'] . '/' . ltrim($path, '/');
        
        if (!File::exists($fullPath)) {
            return '';
        }

        $content = File::get($fullPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        if ($type === 'auto') {
            $type = match ($extension) {
                'css' => 'css',
                'js' => 'js',
                default => 'text',
            };
        }

        $content = self::minify($content, $type);

        return match ($type) {
            'css' => '<style>' . $content . '</style>',
            'js' => '<script>' . $content . '</script>',
            default => $content,
        };
    }

    public static function svg(string $path, array $attributes = []): string
    {
        $fullPath = self::$config['public_path'] . '/' . ltrim($path, '/');
        
        if (!File::exists($fullPath)) {
            return '';
        }

        $content = File::get($fullPath);
        
        if (str_starts_with($content, '<?xml')) {
            $content = preg_replace('/<\?xml.*?\?>/', '', $content);
        }

        $attributes = self::buildAttributes($attributes);
        
        return '<svg ' . $attributes . '>' . $content . '</svg>';
    }

    public static function font(string $path, string $format = 'woff2'): string
    {
        $fontPath = pathinfo($path, PATHINFO_DIRNAME) . '/' . pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        if ($extension !== $format) {
            $fontPath .= '.' . $format;
        }

        return self::asset($fontPath);
    }

    public static function preload(string $path, ?string $as = null): string
    {
        $url = self::asset($path);
        
        if ($as === null) {
            $as = match (pathinfo($path, PATHINFO_EXTENSION)) {
                'css' => 'style',
                'js' => 'script',
                'woff', 'woff2' => 'font',
                'ttf' => 'font',
                'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
                default => 'fetch',
            };
        }

        return '<link rel="preload" href="' . $url . '" as="' . $as . '">';
    }

    public static function dnsPrefetch(string $domain): string
    {
        return '<link rel="dns-prefetch" href="//' . ltrim($domain, '/') . '">';
    }

    public static function preconnect(string $domain): string
    {
        return '<link rel="preconnect" href="https://' . ltrim($domain, '/') . '">';
    }

    public static function critical(string $css): string
    {
        $minified = self::minifyCss($css);
        return '<style>' . $minified . '</style>';
    }

    public static function generateManifest(): void
    {
        $assetsPath = self::$config['assets_path'];
        $manifest = [];
        
        if (!is_dir($assetsPath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($assetsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace('\\', '/', $file->getPathname());
                $relativePath = str_replace($assetsPath, '', $relativePath);
                $relativePath = ltrim($relativePath, '/');
                
                $timestamp = $file->getMTime();
                $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
                $pathWithoutExt = substr($relativePath, 0, -(strlen($extension) + 1));
                
                $versionedPath = "{$pathWithoutExt}.v{$timestamp}.{$extension}";
                $manifest[$relativePath] = '/' . $versionedPath;
            }
        }

        $manifestPath = self::$config['public_path'] . '/manifest.json';
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
    }

    public static function generateServiceWorker(): void
    {
        $template = <<<JS
const CACHE_NAME = 'v1';
const urlsToCache = [
    '/',
    '/offline.html'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});
JS;

        $swPath = self::$config['public_path'] . '/sw.js';
        File::put($swPath, $template);
    }

    private static function loadManifest(): void
    {
        $manifestPath = self::$config['public_path'] . '/manifest.json';
        
        if (File::exists($manifestPath)) {
            $content = File::get($manifestPath);
            self::$manifest = json_decode($content, true) ?: [];
        }
    }

    private static function getVersionedPath(string $path): string
    {
        if (!self::$config['cache_busting']) {
            return $path;
        }

        if (isset(self::$manifest[$path])) {
            return ltrim(self::$manifest[$path], '/');
        }

        if (isset(self::$versioned[$path])) {
            return self::$versioned[$path];
        }

        $fullPath = self::$config['public_path'] . '/' . ltrim($path, '/');
        
        if (File::exists($fullPath)) {
            $timestamp = File::lastModified($fullPath);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $pathWithoutExt = substr($path, 0, -(strlen($extension) + 1));
            
            $versionedPath = "{$pathWithoutExt}.v{$timestamp}.{$extension}";
            self::$versioned[$path] = $versionedPath;
            
            return $versionedPath;
        }

        return $path;
    }

    private static function isExternal(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private static function getBaseUrl(): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    private static function buildAttributes(array $attributes): string
    {
        $html = [];
        
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html[] = $key;
                }
            } else {
                $html[] = $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        
        return implode(' ', $html);
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function getManifest(): array
    {
        return self::$manifest;
    }

    public static function clearCache(): void
    {
        self::$manifest = [];
        self::$versioned = [];
    }
}
