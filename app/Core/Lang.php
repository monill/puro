<?php

declare(strict_types=1);

namespace App\Core;

class Lang
{
    private static array $config = [
        'path' => __DIR__ . '/../../lang',
        'fallback' => 'en-us',
        'locale' => 'en-us',
    ];
    
    private static array $loaded = [];
    private static array $translations = [];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function setLocale(string $locale): void
    {
        self::$config['locale'] = $locale;
    }

    public static function getLocale(): string
    {
        return self::$config['locale'];
    }

    public static function setFallback(string $fallback): void
    {
        self::$config['fallback'] = $fallback;
    }

    public static function getFallback(): string
    {
        return self::$config['fallback'];
    }

    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::$config['locale'];
        
        $translation = self::getTranslation($key, $locale);
        
        if ($translation === null) {
            $translation = self::getTranslation($key, self::$config['fallback']);
        }
        
        if ($translation === null) {
            return $key;
        }
        
        return self::makeReplacements($translation, $replace);
    }

    public static function choice(string $key, int $number, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::$config['locale'];
        
        $translation = self::getTranslation($key, $locale);
        
        if ($translation === null) {
            $translation = self::getTranslation($key, self::$config['fallback']);
        }
        
        if ($translation === null) {
            return $key;
        }
        
        if (is_array($translation)) {
            $translation = self::selectPlural($translation, $number, $locale);
        }
        
        $replace['count'] = $number;
        
        return self::makeReplacements($translation, $replace);
    }

    private static function getTranslation(string $key, string $locale): mixed
    {
        if (!isset(self::$loaded[$locale])) {
            self::loadTranslations($locale);
        }
        
        $keys = explode('.', $key);
        $value = self::$translations[$locale] ?? [];
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    private static function loadTranslations(string $locale): void
    {
        $path = self::$config['path'] . DIRECTORY_SEPARATOR . $locale;
        
        if (!is_dir($path)) {
            self::$translations[$locale] = [];
            self::$loaded[$locale] = true;
            return;
        }
        
        $files = glob($path . DIRECTORY_SEPARATOR . '*.php');
        
        foreach ($files as $file) {
            $group = basename($file, '.php');
            $translations = include $file;
            
            if (is_array($translations)) {
                self::$translations[$locale][$group] = $translations;
            }
        }
        
        self::$loaded[$locale] = true;
    }

    private static function makeReplacements(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }
        
        foreach ($replace as $key => $value) {
            $line = str_replace(':' . $key, (string) $value, $line);
        }
        
        return $line;
    }

    private static function selectPlural(array $translations, int $number, string $locale): string
    {
        $pluralIndex = self::getPluralIndex($number, $locale);
        
        if (isset($translations[$pluralIndex])) {
            return $translations[$pluralIndex];
        }
        
        return $translations[0] ?? '';
    }

    private static function getPluralIndex(int $number, string $locale): int
    {
        $localeRules = [
            'en-us' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'pt-br' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'es' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'fr' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'de' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'it' => function ($number) {
                return ($number == 1) ? 0 : 1;
            },
            'ru' => function ($number) {
                $tens = $number % 100;
                $singles = $number % 10;
                
                if ($tens >= 10 && $tens <= 20) {
                    return 2;
                } elseif ($singles == 1) {
                    return 0;
                } elseif ($singles >= 2 && $singles <= 4) {
                    return 1;
                } else {
                    return 2;
                }
            },
        ];
        
        $rule = $localeRules[$locale] ?? $localeRules['en-us'];
        
        return $rule($number);
    }

    public static function addTranslation(string $locale, string $group, array $translations): void
    {
        if (!isset(self::$translations[$locale][$group])) {
            self::$translations[$locale][$group] = [];
        }
        
        self::$translations[$locale][$group] = array_merge(
            self::$translations[$locale][$group],
            $translations
        );
    }

    public static function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? self::$config['locale'];
        
        return self::getTranslation($key, $locale) !== null;
    }

    public static function getAvailableLocales(): array
    {
        $path = self::$config['path'];
        
        if (!is_dir($path)) {
            return [];
        }
        
        $locales = [];
        
        $directories = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $locales[] = basename($directory);
        }
        
        return $locales;
    }

    public static function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, self::getAvailableLocales());
    }

    public static function detectLocaleFromRequest(): string
    {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage)) {
            return self::$config['fallback'];
        }
        
        $languages = [];
        
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $matches);
        
        if (!empty($matches[1])) {
            $languages = array_combine($matches[1], $matches[3]);
            
            foreach ($languages as $lang => $q) {
                $languages[$lang] = $q ? (float) $q : 1;
            }
            
            arsort($languages);
        }
        
        $availableLocales = self::getAvailableLocales();
        
        foreach ($languages as $lang => $q) {
            $lang = strtolower($lang);
            
            if (in_array($lang, $availableLocales)) {
                return $lang;
            }
            
            $shortLang = substr($lang, 0, 2);
            
            foreach ($availableLocales as $locale) {
                if (str_starts_with($locale, $shortLang)) {
                    return $locale;
                }
            }
        }
        
        return self::$config['fallback'];
    }

    public static function setLocaleFromRequest(): void
    {
        $locale = self::detectLocaleFromRequest();
        self::setLocale($locale);
    }

    public static function getTranslations(?string $locale = null): array
    {
        $locale = $locale ?? self::$config['locale'];
        
        if (!isset(self::$loaded[$locale])) {
            self::loadTranslations($locale);
        }
        
        return self::$translations[$locale] ?? [];
    }

    public static function getGroup(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? self::$config['locale'];
        
        if (!isset(self::$loaded[$locale])) {
            self::loadTranslations($locale);
        }
        
        return self::$translations[$locale][$group] ?? [];
    }

    public static function validateTranslations(string $locale): array
    {
        $errors = [];
        
        if (!self::isLocaleSupported($locale)) {
            $errors[] = "Locale '{$locale}' is not supported";
            return $errors;
        }
        
        $fallbackTranslations = self::getTranslations(self::$config['fallback']);
        $localeTranslations = self::getTranslations($locale);
        
        foreach ($fallbackTranslations as $group => $fallbackGroup) {
            if (!isset($localeTranslations[$group])) {
                $errors[] = "Missing group '{$group}' in locale '{$locale}'";
                continue;
            }
            
            $missingKeys = array_diff_key($fallbackGroup, $localeTranslations[$group]);
            
            foreach ($missingKeys as $key => $value) {
                $errors[] = "Missing key '{$group}.{$key}' in locale '{$locale}'";
            }
        }
        
        return $errors;
    }

    public static function exportTranslations(string $locale, string $format = 'php'): string
    {
        $translations = self::getTranslations($locale);
        
        return match ($format) {
            'json' => json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'php' => '<?php' . PHP_EOL . 'return ' . var_export($translations, true) . ';',
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}")
        };
    }

    public static function importTranslations(string $locale, string $content, string $format = 'php'): void
    {
        $translations = match ($format) {
            'json' => json_decode($content, true),
            'php' => eval('?>' . $content),
            default => throw new \InvalidArgumentException("Unsupported import format: {$format}")
        };
        
        if (!is_array($translations)) {
            throw new \InvalidArgumentException("Invalid translation data");
        }
        
        foreach ($translations as $group => $groupTranslations) {
            self::addTranslation($locale, $group, $groupTranslations);
        }
    }
}
