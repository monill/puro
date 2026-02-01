<?php

/**
 * API Routes
 * Todas as rotas da API REST
 */

use App\Http\Request;
use App\Http\Router;

// =============================================================================
// GRUPO DE ROTAS DA API
// =============================================================================

Router::group(['prefix' => 'api/v1', 'middleware' => ['cors', 'rate_limit:60,1']], function() {
    
    // -------------------------------------------------------------------------
    // ROTAS PÚBLICAS DA API
    // -------------------------------------------------------------------------
    
    // Autenticação API
    Router::post('/auth/login', 'Api\AuthController@login');
    Router::post('/auth/register', 'Api\AuthController@register');
    Router::post('/auth/refresh', 'Api\AuthController@refresh');
    Router::post('/auth/logout', 'Api\AuthController@logout');
    
    // Informações públicas
    Router::get('/info', 'Api\InfoController@index');
    Router::get('/stats/public', 'Api\StatsController@public');
    Router::get('/version', 'Api\InfoController@version');
    
    // -------------------------------------------------------------------------
    // ROTAS AUTENTICADAS DA API
    // -------------------------------------------------------------------------
    
    Router::group(['middleware' => ['api.auth']], function() {
        
        // Usuários
        Router::get('/users', 'Api\UserController@index');
        Router::get('/users/{id}', 'Api\UserController@show');
        Router::post('/users', 'Api\UserController@store');
        Router::put('/users/{id}', 'Api\UserController@update');
        Router::delete('/users/{id}', 'Api\UserController@delete');
        
        // Aldeias
        Router::get('/villages', 'Api\VillageController@index');
        Router::get('/villages/{id}', 'Api\VillageController@show');
        Router::post('/villages', 'Api\VillageController@store');
        Router::put('/villages/{id}', 'Api\VillageController@update');
        Router::delete('/villages/{id}', 'Api\VillageController@delete');
        
        // Estatísticas
        Router::get('/stats', 'Api\StatsController@index');
        Router::get('/stats/users', 'Api\StatsController@users');
        Router::get('/stats/villages', 'Api\StatsController@villages');
        Router::get('/stats/online', 'Api\StatsController@online');
        Router::get('/stats/growth', 'Api\StatsController@growth');
        
        // Recursos
        Router::get('/resources/{village_id}', 'Api\ResourceController@show');
        Router::post('/resources/{village_id}/collect', 'Api\ResourceController@collect');
        
        // Construções
        Router::get('/buildings/{village_id}', 'Api\BuildingController@index');
        Router::post('/buildings/{village_id}/build', 'Api\BuildingController@build');
        Router::post('/buildings/{village_id}/upgrade', 'Api\BuildingController@upgrade');
        
        // Tropas
        Router::get('/troops/{village_id}', 'Api\TroopController@index');
        Router::post('/troops/{village_id}/train', 'Api\TroopController@train');
        Router::post('/troops/{village_id}/move', 'Api\TroopController@move');
        
        // Batalhas
        Router::get('/battles', 'Api\BattleController@index');
        Router::get('/battles/{id}', 'Api\BattleController@show');
        Router::post('/battles', 'Api\BattleController@create');
        
        // Mensagens
        Router::get('/messages', 'Api\MessageController@index');
        Router::get('/messages/{id}', 'Api\MessageController@show');
        Router::post('/messages', 'Api\MessageController@store');
        Router::delete('/messages/{id}', 'Api\MessageController@delete');
        
        // Alianças
        Router::get('/alliances', 'Api\AllianceController@index');
        Router::get('/alliances/{id}', 'Api\AllianceController@show');
        Router::post('/alliances', 'Api\AllianceController@store');
        Router::put('/alliances/{id}', 'Api\AllianceController@update');
        Router::delete('/alliances/{id}', 'Api\AllianceController@delete');
        
        // Mercado
        Router::get('/market/offers', 'Api\MarketController@offers');
        Router::post('/market/offers', 'Api\MarketController@createOffer');
        Router::post('/market/accept/{offer_id}', 'Api\MarketController@acceptOffer');
        
        // Relatórios
        Router::get('/reports', 'Api\ReportController@index');
        Router::get('/reports/{id}', 'Api\ReportController@show');
        
        // Perfil do usuário
        Router::get('/profile', 'Api\ProfileController@show');
        Router::put('/profile', 'Api\ProfileController@update');
        Router::post('/profile/password', 'Api\ProfileController@updatePassword');
        Router::post('/profile/avatar', 'Api\ProfileController@updateAvatar');
        
        // Notificações
        Router::get('/notifications', 'Api\NotificationController@index');
        Router::put('/notifications/{id}/read', 'Api\NotificationController@markAsRead');
        Router::put('/notifications/read-all', 'Api\NotificationController@markAllAsRead');
        
        // Configurações do usuário
        Router::get('/settings', 'Api\SettingsController@index');
        Router::put('/settings', 'Api\SettingsController@update');
        
    });
    
    // -------------------------------------------------------------------------
    // ROTAS ADMIN DA API
    // -------------------------------------------------------------------------
    
    Router::group(['middleware' => ['api.auth', 'api.admin']], function() {
        
        // Admin - Usuários
        Router::get('/admin/users', 'Api\Admin\UserController@index');
        Router::post('/admin/users/{id}/ban', 'Api\Admin\UserController@ban');
        Router::post('/admin/users/{id}/unban', 'Api\Admin\UserController@unban');
        Router::post('/admin/users/{id}/promote', 'Api\Admin\UserController@promote');
        Router::post('/admin/users/{id}/demote', 'Api\Admin\UserController@demote');
        
        // Admin - Sistema
        Router::get('/admin/system/info', 'Api\Admin\SystemController@info');
        Router::get('/admin/system/stats', 'Api\Admin\SystemController@stats');
        Router::post('/admin/system/maintenance', 'Api\Admin\SystemController@toggleMaintenance');
        Router::post('/admin/system/cache/clear', 'Api\Admin\SystemController@clearCache');
        
        // Admin - Logs
        Router::get('/admin/logs', 'Api\Admin\LogController@index');
        Router::get('/admin/logs/{file}', 'Api\Admin\LogController@show');
        Router::delete('/admin/logs/{file}', 'Api\Admin\LogController@delete');
        
        // Admin - Configurações
        Router::get('/admin/config', 'Api\Admin\ConfigController@index');
        Router::put('/admin/config', 'Api\Admin\ConfigController@update');
        
        // Admin - Backup
        Router::post('/admin/backup/create', 'Api\Admin\BackupController@create');
        Router::get('/admin/backup/list', 'Api\Admin\BackupController@list');
        Router::post('/admin/backup/restore', 'Api\Admin\BackupController@restore');
        
    });
    
});

// =============================================================================
// MIDDLEWARE ESPECÍFICOS DA API
// =============================================================================

// Rate limiting por endpoint
Router::middleware('api.auth.rate_limit', function($request, $next) {
    $limit = 100; // 100 requisições por hora para usuários autenticados
    $window = 3600; // 1 hora
    
    $key = 'api_rate_limit_' . auth()->id();
    $current = cache($key, 0);
    
    if ($current >= $limit) {
        return response()->json(['error' => 'Too many requests'], 429);
    }
    
    cache($key, $current + 1, $window);
    return $next($request);
});

// CORS específico para API
Router::middleware('api.cors', function($request, $next) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($request->method() === 'OPTIONS') {
        return response()->json('', 200);
    }
    
    return $next($request);
});

// Formato de resposta JSON
Router::middleware('api.response', function($request, $next) {
    $response = $next($request);
    
    // Garantir que todas as respostas sejam JSON
    if (!$response instanceof \App\Http\Response) {
        return response()->json($response);
    }
    
    return $response;
});
