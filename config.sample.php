<?php
// /config.php

return [
    // Banco de Dados
    'db' => [
        'host' => 'localhost',
        'name' => 'minimal_framework',
        'user' => 'root',
        'pass' => '',
        'charset'  => 'utf8mb4',
        'timezone' => '-03:00',
    ],

    // Informações do Site
    'app' => [
        'name' => 'Minimal PHP Framework',
        'url'  => 'http://localhost',
        'lang' => 'pt-br',
        'timezone' => 'America/Sao_Paulo',
        'debug' => true
    ],

    // Configurações de E-mail (PHPMailer)
    'mail' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'user' => '',
        'pass' => '',
        'from_email' => 'noreply@example.com',
        'from_name'  => 'Minimal Framework',
    ],

    // Configurações de Autenticação Multi-Guard
    'auth' => [
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
                'model' => 'App\\Models\\User',
            ],
            'customer' => [
                'driver' => 'session',
                'provider' => 'customers',
                'model' => 'App\\Models\\Customer',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'database',
                'table' => 'users',
            ],
            'customers' => [
                'driver' => 'database',
                'table' => 'customers',
            ],
        ],
    ],

    // Configurações de Cache com múltiplos drivers
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/storage/cache',
            ],
            'session' => [
                'driver' => 'session',
            ],
            'memory' => [
                'driver' => 'memory',
            ],
            'redis' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'port' => 6379,
                'password' => null,
                'database' => 0,
            ],
            'memcached' => [
                'driver' => 'memcached',
                'servers' => [
                    ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1],
                ],
            ],
        ],
    ],

    // Configurações de Sessão
    'session' => [
        'driver' => 'file',
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    // Configurações de Upload
    'upload' => [
        'path' => __DIR__ . '/storage/uploads',
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        'max_file_size' => 10485760, // 10MB
        'image_quality' => 85,
    ],

    // Configurações de Rate Limiting
    'rate_limiting' => [
        'enabled' => true,
        'driver' => 'cache',
        'default_limit' => 60,
        'default_window' => 60,
    ],

    // Configurações de CORS
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 86400,
        'allow_credentials' => false,
    ],

    // Configurações de Security Headers
    'security' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'content_security_policy' => null,
    ],

    // Configurações de View/Template Engine
    'view' => [
        'path' => __DIR__ . '/templates',
        'extension' => '.php',
        'layout' => 'layout',
    ],

    // Configurações de Assets
    'assets' => [
        'path' => __DIR__ . '/public/assets',
        'versioning' => true,
        'minify' => false,
        'cache_busting' => true,
    ],

    // Configurações de Debug Toolbar
    'debug_toolbar' => [
        'enabled' => true,
        'position' => 'bottom-right',
        'max_tabs' => 10,
    ],

    // Configurações de Error Handler
    'error_handler' => [
        'debug' => true,
        'display_errors' => false,
        'log_errors' => true,
        'ignore_errors' => [],
    ],

    // Configurações de Logging
    'logging' => [
        'enabled' => true,
        'path' => __DIR__ . '/storage/logs',
        'level' => 'debug',
        'rotation' => true,
        'max_files' => 30,
    ],

    // Configurações de Input Sanitization
    'sanitization' => [
        'encoding' => 'UTF-8',
        'strip_tags' => true,
        'remove_comments' => true,
        'normalize_whitespace' => true,
        'allowed_tags' => [],
        'max_length' => null,
    ],

    // Configurações de Query Cache
    'query_cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'max_size' => 1000,
    ],

    // Configurações de Response Cache
    'response_cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'vary_headers' => ['Accept', 'Accept-Language', 'Cookie'],
        'skip_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
    ],
];
