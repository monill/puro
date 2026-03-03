<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Route;
use App\Core\Session;
use App\Core\AuthManager;
use App\Core\Cache;
use App\Core\Lang;
use App\Core\Log;

// Carregar configuração
$config = require_once __DIR__ . '/../config.php';

// Configurar componentes
Session::start($config['session'] ?? []);

AuthManager::configure($config['auth'] ?? []);

Cache::configure($config['cache'] ?? []);

Lang::configure($config['app'] ?? []);
Lang::setLocale($config['app']['lang'] ?? 'pt-br');

Log::configure([
    'path' => __DIR__ . '/../storage/logs',
    'level' => $config['app']['debug'] ? 'debug' : 'info'
]);

$request = new Request();
$response = new Response();

require_once __DIR__ . '/../routes/web.php';

$routeResponse = Route::dispatch($request);

if ($routeResponse instanceof Response) {
    $routeResponse->send();
} else {
    echo $routeResponse;
}
