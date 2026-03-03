<?php

/**
 * Web Routes
 * Todas as rotas da aplicação web
 */

use App\Helpers\FileHelper;
use App\Http\Request;
use App\Http\Router;

// =============================================================================
// ROTAS DE INSTALAÇÃO
// =============================================================================

Router::get('/', 'HomeController@index');

// Rotas de instalação (só se não estiver instalado)
if (!FileHelper::exists(FileHelper::storage('.installed'))) {
    Router::get('/install', 'InstallController@index');
    Router::get('/install/database', 'InstallController@database');
    Router::get('/install/setup', 'InstallController@setup');
    Router::get('/install/admin', 'InstallController@admin');
    Router::get('/install/finish', 'InstallController@finish');

    Router::post('/install/save-database', 'InstallController@saveDatabase');
    Router::post('/install/save-setup', 'InstallController@saveSetup');
    Router::post('/install/create-admin', 'InstallController@createAdmin');
}

// =============================================================================
// ROTAS PRINCIPAIS (só se estiver instalado)
// =============================================================================

if (FileHelper::exists(FileHelper::storage('.installed'))) {

    // -----------------------------------------------------------------------
    // ROTAS PÚBLICAS (sem autenticação)
    // -----------------------------------------------------------------------

    Router::get('/', 'HomeController@index');
    Router::get('/home', 'HomeController@index');
    Router::get('/about', 'HomeController@about');
    Router::get('/contact', 'HomeController@contact');
    Router::get('/rules', 'HomeController@rules');
    Router::get('/statistics', 'HomeController@statistics');

    // Rotas de autenticação
    Router::get('/login', 'AuthController@showLogin');
    Router::post('/login', 'AuthController@login');
    Router::get('/register', 'AuthController@showRegister');
    Router::post('/register', 'AuthController@register');
    Router::get('/logout', 'AuthController@logout');
    Router::get('/forgot-password', 'AuthController@showForgotPassword');
    Router::post('/forgot-password', 'AuthController@forgotPassword');
    Router::get('/reset-password/{token}', 'AuthController@showResetPassword');
    Router::post('/reset-password', 'AuthController@resetPassword');

    // Rotas de teste
    Router::get('/test', 'TestController@index');
    Router::get('/test/database', 'TestController@database');
    Router::get('/test/cache', 'TestController@cache');
    Router::get('/test/auth', 'TestController@auth');

    // -----------------------------------------------------------------------
    // ROTAS DE API (JSON)
    // -----------------------------------------------------------------------

    // API de Usuários
    Router::get('/api/users', 'Api\UserController@index');
    Router::get('/api/users/{id}', 'Api\UserController@show');
    Router::post('/api/users', 'Api\UserController@store');
    Router::put('/api/users/{id}', 'Api\UserController@update');
    Router::delete('/api/users/{id}', 'Api\UserController@delete');

    // API de Estatísticas
    Router::get('/api/stats', 'Api\StatsController@index');
    Router::get('/api/stats/users', 'Api\StatsController@users');
    Router::get('/api/stats/villages', 'Api\StatsController@villages');

    // -----------------------------------------------------------------------
    // ROTAS DE TESTE E DEBUG
    // -----------------------------------------------------------------------

    // Testes do framework
    Router::get('/global-example', 'GlobalExampleController@index');
    Router::post('/global-example/validate', 'GlobalExampleController@testValidation');
    Router::get('/global-example/cache', 'GlobalExampleController@testCache');
    Router::get('/global-example/auth', 'GlobalExampleController@testAuth');
    Router::get('/global-example/language', 'GlobalExampleController@testLanguage');

    // Testes de cache
    Router::get('/cache-test', 'CacheTestController@index');
    Router::get('/cache-test/clear', 'CacheTestController@clear');
    Router::get('/cache-test/remember', 'CacheTestController@remember');

    // Testes de conexões
    Router::get('/connection-test', 'ConnectionTestController@index');
    Router::get('/connection-test/stress', 'ConnectionTestController@stressTest');

    // Testes real world
    Router::get('/real-world-test', 'RealWorldTestController@index');
    Router::get('/real-world-test/simulate-multiple-users', 'RealWorldTestController@simulateMultipleUsers');
    Router::get('/real-world-test/test-with-cache', 'RealWorldTestController@testWithCache');

    // Testes de ambiente (.env)
    Router::get('/env-test', 'EnvTestController@index');
    Router::post('/env-test/reload', 'EnvTestController@reload');
    Router::get('/env-test/test-database', 'EnvTestController@testDatabaseConnection');
    Router::post('/env-test/update', 'EnvTestController@updateVar');

    // -----------------------------------------------------------------------
    // ROTAS DE IDIOMAS
    // -----------------------------------------------------------------------

    Router::post('/language/change', 'LanguageController@change');
    Router::get('/language/{locale}', 'LanguageController@set');

    // -----------------------------------------------------------------------
    // ROTAS DE ERRO (Fallback)
    // -----------------------------------------------------------------------

    Router::get('/404', 'ErrorController@notFound');
    Router::get('/500', 'ErrorController@serverError');
    Router::get('/403', 'ErrorController@forbidden');
}

// =============================================================================
// MIDDLEWARE GLOBAL
// =============================================================================

// Middleware de CORS (para todas as rotas)
Router::globalMiddleware('cors');

// Middleware de logging (para todas as rotas)
Router::globalMiddleware('logging');

// Middleware de rate limiting (para rotas de API)
Router::middlewareGroup('api', ['cors', 'rate_limit:60,1']);

// =============================================================================
// GRUPOS DE ROTAS
// =============================================================================

// Grupo de rotas de API
Router::group(['prefix' => 'api/v1', 'middleware' => 'api'], function () {
    Router::get('/users', 'Api\UserController@index');
    Router::post('/users', 'Api\UserController@store');
    Router::get('/stats', 'Api\StatsController@index');
});

// =============================================================================
// DISPATCH
// =============================================================================

// Despachar a requisição atual
Router::dispatch(Request::capture());
