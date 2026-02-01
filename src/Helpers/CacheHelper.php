<?php

namespace App\Helpers;

class CacheHelper {
    private static $cache = [];
    private static $cacheDir;

    public static function init() {
        self::$cacheDir = FileHelper::storage('cache');
        FileHelper::ensureDirectory(self::$cacheDir);
    }

    /**
     * Obter item do cache
     */
    public static function get($key, $default = null) {
        self::init();

        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        if (!FileHelper::exists($cacheFile)) {
            return $default;
        }

        $data = FileHelper::get($cacheFile);
        $cacheData = unserialize($data);

        // Verificar se expirou (24 horas por padrão)
        if ($cacheData['expires'] < time()) {
            FileHelper::delete($cacheFile);
            return $default;
        }

        return $cacheData['value'];
    }

    /**
     * Salvar item no cache
     */
    public static function put($key, $value, $ttl = 3600) {
        self::init();

        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        $cacheData = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        FileHelper::put($cacheFile, serialize($cacheData));

        LogHelper::debug('Cache salvo', [
            'key' => $key,
            'ttl' => $ttl,
            'file' => $cacheFile
        ]);
    }

    /**
     * Remover item do cache
     */
    public static function forget($key) {
        self::init();

        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        if (FileHelper::exists($cacheFile)) {
            FileHelper::delete($cacheFile);
            LogHelper::debug('Cache removido', ['key' => $key]);
        }
    }

    /**
     * Limpar todo o cache
     */
    public static function flush() {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');

        foreach ($files as $file) {
            FileHelper::delete($file);
        }

        LogHelper::info('Cache limpo', ['files_removed' => count($files)]);
    }

    /**
     * Verificar se item existe no cache
     */
    public static function has($key) {
        return self::get($key) !== null;
    }

    /**
     * Obter estatísticas do cache
     */
    public static function stats() {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += FileHelper::size($file);
        }

        return [
            'total_files' => count($files),
            'total_size' => FileHelper::formatBytes($totalSize),
            'cache_dir' => self::$cacheDir
        ];
    }

    /**
     * Cache com tags (para invalidação seletiva)
     */
    public static function tags($tags, $callback) {
        $tagKey = 'cache_' . implode('_', $tags);

        if (self::has($tagKey)) {
            return self::get($tagKey);
        }

        $value = call_user_func($callback);
        self::put($tagKey, $value);

        return $value;
    }

    /**
     * Cache remember (salva se não existir)
     */
    public static function remember($key, $callback, $ttl = 3600) {
        if (self::has($key)) {
            return self::get($key);
        }

        $value = call_user_func($callback);
        self::put($key, $value, $ttl);

        return $value;
    }
}
