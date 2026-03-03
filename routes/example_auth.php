<?php

declare(strict_types=1);

use App\Core\Route;
use App\Core\AuthManager;
use App\Core\Response;
use App\Middlewares\AdminAuthMiddleware;
use App\Middlewares\CustomerAuthMiddleware;
use App\Middlewares\GuestMiddleware;

// Rotas de Admin
Route::group(['prefix' => 'admin'], function() {
    
    Route::get('/login', function() {
        return 'Admin Login Page';
    })->middleware([GuestMiddleware::class]);
    
    Route::post('/login', function($request) {
        $credentials = [
            'email' => $request->post('email'),
            'password' => $request->post('password')
        ];
        
        if (AuthManager::admin()->attempt($credentials, $request->post('remember'))) {
            return (new Response())->redirect('/admin/dashboard');
        }
        
        return 'Admin login failed';
    })->middleware([GuestMiddleware::class]);
    
    Route::get('/dashboard', function() {
        $user = AuthManager::admin()->user();
        return "Admin Dashboard - Welcome, {$user->name}!";
    })->middleware([AdminAuthMiddleware::class]);
    
    Route::post('/logout', function() {
        AuthManager::admin()->logout();
        return (new Response())->redirect('/admin/login');
    })->middleware([AdminAuthMiddleware::class]);
});

// Rotas de Customer
Route::group(['prefix' => 'customer'], function() {
    
    Route::get('/login', function() {
        return 'Customer Login Page';
    })->middleware([GuestMiddleware::class]);
    
    Route::post('/login', function($request) {
        $credentials = [
            'email' => $request->post('email'),
            'password' => $request->post('password')
        ];
        
        if (AuthManager::customer()->attempt($credentials, $request->post('remember'))) {
            return (new Response())->redirect('/customer/dashboard');
        }
        
        return 'Customer login failed';
    })->middleware([GuestMiddleware::class]);
    
    Route::get('/dashboard', function() {
        $user = AuthManager::customer()->user();
        return "Customer Dashboard - Welcome, {$user->name}!";
    })->middleware([CustomerAuthMiddleware::class]);
    
    Route::post('/logout', function() {
        AuthManager::customer()->logout();
        return (new Response())->redirect('/customer/login');
    })->middleware([CustomerAuthMiddleware::class]);
});

// Rota principal - verifica autenticação em qualquer guard
Route::get('/', function() {
    if (AuthManager::admin()->check()) {
        return (new Response())->redirect('/admin/dashboard');
    }
    
    if (AuthManager::customer()->check()) {
        return (new Response())->redirect('/customer/dashboard');
    }
    
    return 'Welcome! Please login as <a href="/admin/login">Admin</a> or <a href="/customer/login">Customer</a>';
});

// API endpoints para teste
Route::group(['prefix' => 'api'], function() {
    
    Route::get('/admin/user', function() {
        $user = AuthManager::admin()->user();
        return (new Response())->json([
            'guard' => 'admin',
            'user' => $user ? $user->toArray() : null,
            'authenticated' => AuthManager::admin()->check()
        ]);
    });
    
    Route::get('/customer/user', function() {
        $user = AuthManager::customer()->user();
        return (new Response())->json([
            'guard' => 'customer',
            'user' => $user ? $user->toArray() : null,
            'authenticated' => AuthManager::customer()->check()
        ]);
    });
    
    Route::get('/all-users', function() {
        return (new Response())->json([
            'authenticated' => AuthManager::authenticated(),
            'users' => AuthManager::allUsers(),
            'current_guard' => AuthManager::getCurrentGuard()
        ]);
    });
});
