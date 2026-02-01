<?php

namespace App\Helpers;

class FileHelper {
    /**
     * Obter path absoluto usando constantes
     */
    public static function path($path = '') {
        return app_path() . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Obter path do storage
     */
    public static function storage($path = '') {
        return storage_path() . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Obter path dos logs
     */
    public static function logs($path = '') {
        return storage_path('logs') . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Obter path do public
     */
    public static function public($path = '') {
        return public_path() . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Criar diretório se não existir
     */
    public static function ensureDirectory($path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    /**
     * Verificar se arquivo existe
     */
    public static function exists($path) {
        return file_exists($path);
    }

    /**
     * Escrever arquivo (cria diretório se necessário)
     */
    public static function put($path, $content) {
        $dir = dirname($path);
        self::ensureDirectory($dir);
        return file_put_contents($path, $content);
    }

    /**
     * Ler arquivo
     */
    public static function get($path) {
        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**
     * Deletar arquivo
     */
    public static function delete($path) {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Obter tamanho do arquivo
     */
    public static function size($path) {
        return file_exists($path) ? filesize($path) : 0;
    }

    /**
     * Obter extensão do arquivo
     */
    public static function extension($path) {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Criar diretório (se não existir)
     */
    public static function makeDirectory(string $dir, int $mode = 0755, bool $recursive = true): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, $mode, $recursive);
    }

    /**
     * Formatar bytes para KB, MB, GB...
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        $bytes /= (1024 ** $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
}
