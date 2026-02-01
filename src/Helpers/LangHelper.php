<?php

namespace App\Helpers;

class LangHelper
{
    private static $locale = 'pt-br';
    private static $fallback = 'en';
    private static $translations = [];
    private static $loaded = [];

    /**
     * Definir locale atual
     */
    public static function setLocale($locale)
    {
        self::$locale = $locale;

        // Salvar em sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['locale'] = $locale;

        LogHelper::debug('Locale alterado', ['locale' => $locale]);
    }

    /**
     * Obter locale atual
     */
    public static function getLocale()
    {
        // Verificar sessão primeiro
        if (session_status() !== PHP_SESSION_NONE && isset($_SESSION['locale'])) {
            return $_SESSION['locale'];
        }

        // Verificar cookie
        if (isset($_COOKIE['locale'])) {
            return $_COOKIE['locale'];
        }

        // Verificar header Accept-Language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            return self::mapBrowserLocale($browserLang);
        }

        return self::$locale;
    }

    /**
     * Mapear locale do navegador
     */
    private static function mapBrowserLocale($browserLang)
    {
        $map = [
            'pt' => 'pt-br',
            'en' => 'en',
            'es' => 'es',
            'fr' => 'fr',
            'de' => 'de',
            'it' => 'it'
        ];

        return $map[$browserLang] ?? self::$fallback;
    }

    /**
     * Obter locale de fallback
     */
    public static function getFallback()
    {
        return self::$fallback;
    }

    /**
     * Carregar arquivo de tradução
     */
    public static function load($file)
    {
        $locale = self::getLocale();
        $key = "{$locale}.{$file}";

        if (isset(self::$loaded[$key])) {
            return self::$translations[$key] ?? [];
        }

        $filePath = FileHelper::path("lang/{$locale}/{$file}.php");

        if (!FileHelper::exists($filePath)) {
            // Tentar fallback
            $fallbackPath = FileHelper::path("lang/" . self::$fallback . "/{$file}.php");

            if (FileHelper::exists($fallbackPath)) {
                $filePath = $fallbackPath;
            } else {
                LogHelper::warning('Arquivo de tradução não encontrado', [
                    'file' => $file,
                    'locale' => $locale,
                    'fallback' => self::$fallback
                ]);
                return [];
            }
        }

        self::$translations[$key] = include $filePath;
        self::$loaded[$key] = true;

        LogHelper::debug('Tradução carregada', [
            'file' => $file,
            'locale' => $locale,
            'translations_count' => count(self::$translations[$key])
        ]);

        return self::$translations[$key];
    }

    /**
     * Obter tradução
     */
    public static function get($key, $replace = [], $locale = null)
    {
        $locale = $locale ?? self::getLocale();

        // Parse key (ex: 'users.welcome')
        $parts = explode('.', $key);
        $file = $parts[0];
        $translationKey = implode('.', array_slice($parts, 1));

        // Carregar tradução
        $translations = self::load($file);

        // Obter valor
        $value = $translations[$translationKey] ?? $key;

        // Se não encontrou, tentar fallback
        if ($value === $key && $locale !== self::$fallback) {
            $fallbackTranslations = self::load($file);
            $value = $fallbackTranslations[$translationKey] ?? $key;
        }

        // Fazer replace
        if (!empty($replace)) {
            foreach ($replace as $search => $replaceValue) {
                $value = str_replace(":{$search}", $replaceValue, $value);
            }
        }

        return $value;
    }

    /**
     * Tradução plural
     */
    public static function choice($key, $number, $replace = [])
    {
        $locale = self::getLocale();
        $translations = self::load(explode('.', $key)[0]);
        $translationKey = implode('.', array_slice(explode('.', $key), 1));

        $value = $translations[$translationKey] ?? $key;

        // Lógica de pluralização baseada no locale
        if (is_array($value)) {
            $index = self::getPluralIndex($number, $locale);
            $value = $value[$index] ?? $value[0];
        } else {
            // Fallback para lógica simples
            $value = $number == 1 ? $value : str_replace('{count}', $number, $value);
        }

        // Fazer replace
        $replace['count'] = $number;
        foreach ($replace as $search => $replaceValue) {
            $value = str_replace(":{$search}", $replaceValue, $value);
        }

        return $value;
    }

    /**
     * Obter índice de pluralização
     */
    private static function getPluralIndex($number, $locale)
    {
        // Lógica simplificada para português/inglês
        if ($locale === 'pt-br') {
            // Português: 0 itens, 1 item, 2+ itens
            if ($number == 0) return 0;
            if ($number == 1) return 1;
            return 2;
        } else {
            // Inglês: 1 item, 2+ itens
            return ($number == 1) ? 0 : 1;
        }
    }

    /**
     * Verificar se tradução existe
     */
    public static function has($key, $locale = null)
    {
        $locale = $locale ?? self::getLocale();
        $parts = explode('.', $key);
        $file = $parts[0];
        $translationKey = implode('.', array_slice($parts, 1));

        $translations = self::load($file);

        return isset($translations[$translationKey]);
    }

    /**
     * Obter todos os idiomas disponíveis
     */
    public static function getAvailableLocales()
    {
        $langDir = FileHelper::path('lang');
        $locales = [];

        if (is_dir($langDir)) {
            $dirs = scandir($langDir);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($langDir . '/' . $dir)) {
                    $locales[$dir] = self::getLocaleName($dir);
                }
            }
        }

        return $locales;
    }

    /**
     * Obter nome do locale
     */
    private static function getLocaleName($locale)
    {
        $names = [
            'pt-br' => 'Português (Brasil)',
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano'
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Obter bandeira do locale
     */
    public static function getFlag($locale)
    {
        $flags = [
            'pt-br' => '🇧🇷',
            'en' => '🇺🇸',
            'es' => '🇪🇸',
            'fr' => '🇫🇷',
            'de' => '🇩🇪',
            'it' => '🇮🇹'
        ];

        return $flags[$locale] ?? '🌍';
    }

    /**
     * Gerar seletor de idiomas
     */
    public static function languageSelector()
    {
        $currentLocale = self::getLocale();
        $availableLocales = self::getAvailableLocales();

        $html = '<div class="language-selector">';
        $html .= '<select onchange="changeLanguage(this.value)">';

        foreach ($availableLocales as $locale => $name) {
            $selected = ($locale === $currentLocale) ? 'selected' : '';
            $flag = self::getFlag($locale);
            $html .= "<option value=\"{$locale}\" {$selected}>{$flag} {$name}</option>";
        }

        $html .= '</select>';
        $html .= '</div>';

        $html .= '<script>
            function changeLanguage(locale) {
                document.cookie = "locale=" + locale + "; path=/; max-age=31536000";
                location.reload();
            }
        </script>';

        return $html;
    }

    /**
     * Obter tradução formatada com HTML
     */
    public static function trans($key, $replace = [], $locale = null)
    {
        return self::get($key, $replace, $locale);
    }

    /**
     * Alias para get()
     */
    public static function __($key, $replace = [])
    {
        return self::get($key, $replace);
    }
}
