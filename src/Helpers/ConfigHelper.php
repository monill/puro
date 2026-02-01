<?php

namespace App\Helpers;

use Exception;

class ConfigHelper {
    private static $configs = [];
    private static $loaded = [];

    /**
     * Carregar configurações da pasta /config/
     */
    public static function load($file) {
        // Normalizar nome do arquivo
        $file = str_replace('.php', '', $file);

        // Verificar se já foi carregado
        if (isset(self::$loaded[$file])) {
            return true;
        }

        // Tentar diferentes caminhos
        $paths = [
            FileHelper::path("config/{$file}.php"),
            FileHelper::path("{$file}.php")
        ];

        $configFile = null;
        foreach ($paths as $path) {
            if (FileHelper::exists($path)) {
                $configFile = $path;
                break;
            }
        }

        if (!$configFile) {
            LogHelper::warning('Config file not found', ['file' => $file, 'paths' => $paths]);
            return false;
        }

        try {
            $config = include $configFile;

            if (!is_array($config)) {
                LogHelper::error('Config file must return array', ['file' => $configFile]);
                return false;
            }

            self::$configs[$file] = $config;
            self::$loaded[$file] = true;

            LogHelper::info('Config loaded', ['file' => $file, 'path' => $configFile]);

            return true;

        } catch (Exception $e) {
            LogHelper::error('Failed to load config', ['file' => $file, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obter valor da configuração com notação de ponto
     */
    public static function get($key, $default = null) {
        // Se não tem ponto, assume que é um arquivo completo
        if (strpos($key, '.') === false) {
            return self::getFile($key, $default);
        }

        // Parse da chave: arquivo.chave.subchave
        $parts = explode('.', $key);
        $file = array_shift($parts);

        // Carregar arquivo se necessário
        if (!self::load($file)) {
            return $default;
        }

        // Navegar até a chave
        $value = self::$configs[$file];
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    /**
     * Obter arquivo de configuração completo
     */
    public static function getFile($file, $default = null) {
        if (!self::load($file)) {
            return $default;
        }

        return self::$configs[$file];
    }

    /**
     * Definir valor da configuração
     */
    public static function set($key, $value) {
        if (strpos($key, '.') === false) {
            self::$configs[$key] = $value;
            LogHelper::debug('Config file set', ['file' => $key]);
            return true;
        }

        $parts = explode('.', $key);
        $file = array_shift($parts);

        // Carregar arquivo se necessário
        if (!self::load($file)) {
            return false;
        }

        // Navegar e definir valor
        $config = &self::$configs[$file];
        foreach ($parts as $part) {
            if (!is_array($config)) {
                $config = [];
            }
            if (!array_key_exists($part, $config)) {
                $config[$part] = [];
            }
            $config = &$config[$part];
        }

        $config = $value;

        LogHelper::debug('Config set', ['key' => $key, 'value' => $value]);

        return true;
    }

    /**
     * Verificar se configuração existe
     */
    public static function has($key) {
        return self::get($key) !== null;
    }

    /**
     * Obter todas as configurações carregadas
     */
    public static function all() {
        return self::$configs;
    }

    /**
     * Obter arquivos carregados
     */
    public static function loaded() {
        return array_keys(self::$loaded);
    }

    /**
     * Limpar cache de configurações
     */
    public static function clear() {
        self::$configs = [];
        self::$loaded = [];

        LogHelper::info('Config cache cleared');
    }

    /**
     * Recarregar arquivo específico
     */
    public static function reload($file) {
        unset(self::$configs[$file]);
        unset(self::$loaded[$file]);

        return self::load($file);
    }

    /**
     * Salvar configuração em arquivo
     */
    public static function save($file, $config = null) {
        $file = str_replace('.php', '', $file);

        if ($config === null) {
            if (!isset(self::$configs[$file])) {
                return false;
            }
            $config = self::$configs[$file];
        }

        $configFile = FileHelper::path("config/{$file}.php");

        // Criar diretório se não existir
        $dir = dirname($configFile);
        if (!FileHelper::exists($dir)) {
            FileHelper::makeDirectory($dir, 0755, true);
        }

        // Gerar conteúdo PHP
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * {$file} Configuration\n";
        $content .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
        $content .= " */\n\n";
        $content .= "return " . var_export($config, true) . ";\n";

        // Salvar arquivo
        if (FileHelper::put($configFile, $content)) {
            LogHelper::info('Config saved', ['file' => $file, 'path' => $configFile]);

            // Recarregar arquivo
            self::reload($file);

            return true;
        }

        LogHelper::error('Failed to save config', ['file' => $file, 'path' => $configFile]);

        return false;
    }

    /**
     * Obter configuração com fallback
     */
    public static function getWithFallback($key, $fallbackKey, $default = null) {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        return self::get($fallbackKey, $default);
    }

    /**
     * Obter configuração de ambiente
     */
    public static function env($key, $default = null) {
        // Tentar obter das variáveis de ambiente
        $env = getenv($key);
        if ($env !== false) {
            return $env;
        }

        // Tentar obter de $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        // Tentar obter de $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return $default;
    }

    /**
     * Verificar se está em ambiente de desenvolvimento
     */
    public static function isLocal() {
        return self::get('app.env', 'local') === 'local';
    }

    /**
     * Verificar se está em ambiente de produção
     */
    public static function isProduction() {
        return self::get('app.env', 'local') === 'production';
    }

    /**
     * Verificar se debug está ativado
     */
    public static function isDebug() {
        return self::get('app.debug', false);
    }

    /**
     * Obter URL base da aplicação
     */
    public static function url($path = '') {
        $baseUrl = self::get('app.url', 'http://localhost');

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Obter timezone da aplicação
     */
    public static function timezone() {
        return self::get('app.timezone', 'America/Sao_Paulo');
    }

    /**
     * Obter locale da aplicação
     */
    public static function locale() {
        return self::get('app.locale', 'pt-br');
    }

    /**
     * Obter nome da aplicação
     */
    public static function name() {
        return self::get('app.name', 'Puro');
    }

    /**
     * Obter versão da aplicação
     */
    public static function version() {
        return self::get('app.version', '1.0.0');
    }

    /**
     * Obter chave da aplicação
     */
    public static function key() {
        return self::get('app.key', '');
    }

    /**
     * Obter configuração de cache
     */
    public static function cache($key = null, $default = null) {
        if ($key === null) {
            return self::getFile('cache', []);
        }

        return self::get("cache.{$key}", $default);
    }

    /**
     * Obter configuração de database
     */
    public static function database($key = null, $default = null) {
        if ($key === null) {
            return self::getFile('database', []);
        }

        return self::get("database.{$key}", $default);
    }

    /**
     * Obter configuração de email
     */
    public static function email($key = null, $default = null) {
        if ($key === null) {
            return self::getFile('email', []);
        }

        return self::get("email.{$key}", $default);
    }

    /**
     * Obter configuração de segurança
     */
    public static function security($key = null, $default = null) {
        if ($key === null) {
            return self::getFile('security', []);
        }

        return self::get("security.{$key}", $default);
    }

    /**
     * Obter configuração do jogo
     */
    public static function game($key = null, $default = null) {
        if ($key === null) {
            return self::get('app.game', []);
        }

        return self::get("app.game.{$key}", $default);
    }

    /**
     * Obter configuração de registro
     */
    public static function registration($key = null, $default = null) {
        if ($key === null) {
            return self::get('app.registration', []);
        }

        return self::get("app.registration.{$key}", $default);
    }

    /**
     * Obter configuração de sessão
     */
    public static function session($key = null, $default = null) {
        if ($key === null) {
            return self::get('app.session', []);
        }

        return self::get("app.session.{$key}", $default);
    }
}
