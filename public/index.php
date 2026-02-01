<?php

/**
 * Front Controller - Entry Point
 * Arquivo principal que recebe todas as requisições HTTP
 */

// =============================================================================
// BOOTSTRAP
// =============================================================================

// Autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Iniciar sessão
session_start();

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting (desenvolvimento)
if (env('APP_ENV', 'local') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// =============================================================================
// CONSTANTES DA APLICAÇÃO
// =============================================================================

define('APP_NAME', env('APP_NAME', 'Puro'));
define('APP_VERSION', env('APP_VERSION', '1.0.0'));
define('APP_URL', env('APP_URL', 'http://localhost/puro'));
define('APP_ENV', env('APP_ENV', 'local'));
define('APP_DEBUG', env('APP_DEBUG', false));

// =============================================================================
// MIDDLEWARE GLOBAL
// =============================================================================

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;

// Middleware de CORS global
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit(0);
}

// Middleware de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// if (APP_ENV === 'production') { // REMOVIDO - Agora usa config()
//     header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
// }

// =============================================================================
// CARREGAR ROTAS
// =============================================================================

// Carregar configurações (usando ConfigHelper)
// require_once __DIR__ . '/../src/Config/App.php'; // REMOVIDO - Agora usa config/

// Verificar se está em modo manutenção
$maintenanceFile = storage_path('maintenance');
if (FileHelper::exists($maintenanceFile)) {
    $maintenanceData = json_decode(FileHelper::get($maintenanceFile), true);

    if ($maintenanceData['enabled'] ?? false) {
        http_response_code(503);
        echo '<h1>503 - Service Unavailable</h1>';
        echo '<p>O sistema está em manutenção. Tente novamente mais tarde.</p>';
        exit;
    }
}

// Carregar rotas da web
if (file_exists(__DIR__ . '/../routes/web.php')) {
    require_once __DIR__ . '/../routes/web.php';
} else {
    // Fallback se o arquivo de rotas não existir
    Router::get('/', function() {
        return new Response('Bem-vindo ao ' . APP_NAME);
    });
}

// =============================================================================
// TRATAMENTO DE ERROS
// =============================================================================

// Error handler personalizado
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }

    error("PHP Error: {$message} in {$file}:{$line}", [
        'severity' => $severity,
        'file' => $file,
        'line' => $line
    ]);

    if (APP_DEBUG) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// Exception handler
set_exception_handler(function($exception) {
    error("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (APP_DEBUG) {
        // Em desenvolvimento, mostrar detalhes do erro
        http_response_code(500);
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<h2>' . get_class($exception) . '</h2>';
        echo '<p><strong>Message:</strong> ' . $exception->getMessage() . '</p>';
        echo '<p><strong>File:</strong> ' . $exception->getFile() . ':' . $exception->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
    } else {
        // Em produção, mostrar página de erro genérica
        http_response_code(500);
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Ocorreu um erro interno. Tente novamente mais tarde.</p>';
    }
});

// Shutdown handler para erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error("Fatal Error: {$error['message']}", [
            'file' => $error['file'],
            'line' => $error['line']
        ]);

        if (!APP_DEBUG) {
            http_response_code(500);
            echo '<h1>500 - Internal Server Error</h1>';
            echo '<p>Ocorreu um erro interno. Tente novamente mais tarde.</p>';
        }
    }
});

// =============================================================================
// PERFORMANCE MONITORING
// =============================================================================

if (APP_DEBUG) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    register_shutdown_function(function() use ($startTime, $startMemory) {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000;
        $memoryUsed = $endMemory - $startMemory;

        debug('Request Performance', [
            'execution_time' => round($executionTime, 2) . 'ms',
            'memory_used' => round($memoryUsed / 1024 / 1024, 2) . 'MB',
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]);
    });
}

// =============================================================================
// RESPOSTA PADRÃO PARA ROTAS NÃO ENCONTRADAS
// =============================================================================

// Se nenhuma rota corresponder, mostrar 404
if (!Router::hasRoute(Request::capture()->method(), Request::capture()->uri())) {
    http_response_code(404);

    echo '<h1>404 - Page Not Found</h1>';
    echo '<p>The requested URL was not found on this server.</p>';
    exit;
}

// =============================================================================
// NOTA: O DISPATCH É FEITO DENTRO DO ARQUIVO DE ROTAS
// =============================================================================

/*
 * IMPORTANTE:
 * O despacho das rotas (Router::dispatch()) é feito dentro do arquivo
 * routes/web.php para permitir melhor organização e separação de responsabilidades.
 *
 * Este arquivo (index.php) serve apenas como:
 * - Bootstrap da aplicação
 * - Configuração de middleware global
 * - Tratamento de erros
 * - Monitoramento de performance
 *
 * As rotas específicas da aplicação estão em:
 * - routes/web.php (rotas da aplicação web)
 * - routes/api.php (rotas da API REST)
 */
$response = Router::dispatch($request);

// Enviar resposta
if ($response instanceof \App\Http\Response) {
    $response->send();
} else {
    echo $response;
}
