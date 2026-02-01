<?php

/**
 * Web Routes
 * Todas as rotas da aplicação web
 */

use App\Http\Request;
use App\Http\Router;

// =============================================================================
// ROTAS DE INSTALAÇÃO
// =============================================================================

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
    // ROTAS AUTENTICADAS
    // -----------------------------------------------------------------------
    
    // Dashboard
    Router::get('/dashboard', 'DashboardController@index')->middleware('auth');
    Router::get('/profile', 'ProfileController@index')->middleware('auth');
    Router::post('/profile', 'ProfileController@update')->middleware('auth');
    
    // Usuários
    Router::get('/users', 'UserController@index')->middleware('auth');
    Router::get('/users/create', 'UserController@create')->middleware('auth');
    Router::post('/users', 'UserController@store')->middleware('auth');
    Router::get('/users/{id}', 'UserController@show')->middleware('auth');
    Router::get('/users/{id}/edit', 'UserController@edit')->middleware('auth');
    Router::post('/users/{id}', 'UserController@update')->middleware('auth');
    Router::post('/users/{id}/delete', 'UserController@delete')->middleware('auth');
    
    // Aldeias
    Router::get('/villages', 'VillageController@index')->middleware('auth');
    Router::get('/villages/{id}', 'VillageController@show')->middleware('auth');
    Router::get('/villages/{id}/edit', 'VillageController@edit')->middleware('auth');
    Router::post('/villages/{id}', 'VillageController@update')->middleware('auth');
    
    // -----------------------------------------------------------------------
    // ROTAS ADMINISTRATIVAS
    // -----------------------------------------------------------------------
    
    // Painel Administrativo
    Router::get('/admin', 'AdminController@index')->middleware('auth', 'admin');
    Router::get('/admin/dashboard', 'AdminController@dashboard')->middleware('auth', 'admin');
    Router::get('/admin/users', 'AdminController@users')->middleware('auth', 'admin');
    Router::get('/admin/settings', 'AdminController@settings')->middleware('auth', 'admin');
    Router::post('/admin/settings', 'AdminController@saveSettings')->middleware('auth', 'admin');
    
    // Sistema de Logs
    Router::get('/logs', 'LogController@index')->middleware('auth', 'admin');
    Router::get('/logs/view/{filename}', 'LogController@view')->middleware('auth', 'admin');
    Router::post('/logs/clear', 'LogController@clear')->middleware('auth', 'admin');
    Router::get('/logs/download/{filename}', 'LogController@download')->middleware('auth', 'admin');
    Router::get('/logs/search', 'LogController@search')->middleware('auth', 'admin');
    
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
    // ROTAS DE RECURSOS (Assets)
    // -----------------------------------------------------------------------
    
    Router::get('/assets/{path}', 'AssetController@show')->where('path', '.*');
    
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

// Grupo de rotas de admin
Router::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    Router::get('/', 'AdminController@index');
    Router::get('/users', 'AdminController@users');
    Router::get('/settings', 'AdminController@settings');
});

// Grupo de rotas de API
Router::group(['prefix' => 'api/v1', 'middleware' => 'api'], function() {
    Router::get('/users', 'Api\UserController@index');
    Router::post('/users', 'Api\UserController@store');
    Router::get('/stats', 'Api\StatsController@index');
});

// Grupo de rotas com idioma
Router::group(['prefix' => '{locale}'], function() {
    Router::get('/', 'HomeController@index');
    Router::get('/about', 'HomeController@about');
})->where('locale', 'pt-br|en|es|fr');

// =============================================================================
// ROTAS NOMEADAS (para facilitar referência)
// =============================================================================

Router::name('users.index', '/users');
Router::name('users.create', '/users/create');
Router::name('users.store', '/users');
Router::name('users.show', '/users/{id}');
Router::name('users.edit', '/users/{id}/edit');
Router::name('users.update', '/users/{id}');
Router::name('users.delete', '/users/{id}/delete');

Router::name('login', '/login');
Router::name('register', '/register');
Router::name('logout', '/logout');
Router::name('dashboard', '/dashboard');

Router::name('admin.index', '/admin');
Router::name('admin.users', '/admin/users');
Router::name('admin.settings', '/admin/settings');

// =============================================================================
// DISPATCH
// =============================================================================

// Despachar a requisição atual
Router::dispatch(Request::capture());
