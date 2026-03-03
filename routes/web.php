<?php

declare(strict_types=1);

use App\Core\Route;
use App\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/hello/{name}', [HomeController::class, 'hello']);
Route::get('/json', [HomeController::class, 'api']);
Route::get('/docs', [HomeController::class, 'docs']);
Route::get('/examples', [HomeController::class, 'examples']);
Route::get('/test', function() {
    return view('test');
});

// Incluir rotas de autenticação (descomente quando criar config.php)
// require_once __DIR__ . '/example_auth.php';

// Rotas administrativas
Route::group(['prefix' => 'admin'], function() {
    Route::get('dashboard', [App\Controllers\Admin\DashboardController::class, 'index']);
});
