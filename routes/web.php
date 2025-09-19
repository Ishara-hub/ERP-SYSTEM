<?php

use Illuminate\Support\Facades\Route;

// Simple welcome route for API backend
Route::get('/', function () {
    return response()->json([
        'message' => 'ERP System API Backend',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'api' => '/api',
            'health' => '/up',
            'documentation' => '/api/documentation'
        ]
    ]);
})->name('home');

// Health check route
Route::get('/up', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
});

// API documentation route
Route::get('/api/documentation', function () {
    return response()->json([
        'title' => 'ERP System API Documentation',
        'version' => '1.0.0',
        'description' => 'Complete API documentation for ERP System',
        'endpoints' => [
            'authentication' => '/api/auth/*',
            'dashboard' => '/api/dashboard',
            'users' => '/api/users',
            'customers' => '/api/customers',
            'suppliers' => '/api/suppliers',
            'invoices' => '/api/invoices',
            'items' => '/api/items',
            'payments' => '/api/payments',
            'purchase-orders' => '/api/purchase-orders',
            'accounts' => '/api/accounts',
            'roles' => '/api/roles'
        ],
        'authentication' => 'Bearer Token (Laravel Sanctum)',
        'base_url' => url('/api')
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';