<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\Users\UsersApiController;
use App\Http\Controllers\Api\Customers\CustomersApiController;
use App\Http\Controllers\Api\Suppliers\SuppliersApiController;
use App\Http\Controllers\Api\Invoices\InvoicesApiController;
use App\Http\Controllers\Api\Items\ItemsApiController;
use App\Http\Controllers\Api\Payments\PaymentsApiController;
use App\Http\Controllers\Api\PurchaseOrders\PurchaseOrdersApiController;
use App\Http\Controllers\Api\Accounts\ChartOfAccountsApiController;
use App\Http\Controllers\Api\Roles\RolesApiController;
use App\Http\Controllers\Api\Settings\ProfileApiController;
use App\Http\Controllers\Api\Settings\PasswordApiController;
use App\Http\Controllers\Api\Auth\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

// Test route with middleware
Route::get('/test-middleware', function () {
    return response()->json(['message' => 'API middleware is working!', 'timestamp' => now()]);
})->middleware('api');

// Public routes
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/user', [AuthApiController::class, 'user']);
    Route::post('/auth/refresh', [AuthApiController::class, 'refresh']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    
    // Users
    Route::apiResource('users', UsersApiController::class);
    Route::post('/users/{user}/assign-roles', [UsersApiController::class, 'assignRoles']);
    Route::delete('/users/{user}/remove-roles', [UsersApiController::class, 'removeRoles']);
    
    // Customers
    Route::apiResource('customers', CustomersApiController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomersApiController::class, 'toggleStatus']);
    
    // Suppliers
    Route::apiResource('suppliers', SuppliersApiController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SuppliersApiController::class, 'toggleStatus']);
    
    // Invoices
    Route::apiResource('invoices', InvoicesApiController::class);
    Route::post('/invoices/{invoice}/mark-paid', [InvoicesApiController::class, 'markAsPaid']);
    Route::post('/invoices/{invoice}/print', [InvoicesApiController::class, 'print']);
    Route::post('/invoices/{invoice}/email', [InvoicesApiController::class, 'email']);
    
    // Items
    Route::apiResource('items', ItemsApiController::class);
    Route::post('/items/{item}/toggle-status', [ItemsApiController::class, 'toggleStatus']);
    Route::post('/items/{item}/add-component', [ItemsApiController::class, 'addComponent']);
    Route::put('/items/components/{component}', [ItemsApiController::class, 'updateComponent']);
    Route::delete('/items/components/{component}', [ItemsApiController::class, 'removeComponent']);
    
    // Payments
    Route::apiResource('payments', PaymentsApiController::class);
    Route::post('/payments/receive/{invoice}', [PaymentsApiController::class, 'receivePayment']);
    Route::post('/payments/store-received/{invoice}', [PaymentsApiController::class, 'storeReceivedPayment']);
    Route::get('/payments/general/create', [PaymentsApiController::class, 'createGeneral']);
    Route::post('/payments/general', [PaymentsApiController::class, 'storeGeneral']);
    
    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrdersApiController::class);
    Route::post('/purchase-orders/{purchaseOrder}/update-status', [PurchaseOrdersApiController::class, 'updateStatus']);
    Route::post('/purchase-orders/{purchaseOrder}/print', [PurchaseOrdersApiController::class, 'print']);
    
    // Chart of Accounts
    // Note: Specific routes must come BEFORE parameterized routes to avoid conflicts
    Route::get('/accounts/chart-of-accounts', [ChartOfAccountsApiController::class, 'index']);
    Route::get('/accounts/parent-accounts', [ChartOfAccountsApiController::class, 'getParentAccounts']);
    Route::get('/accounts/parent-accounts-by-type', [ChartOfAccountsApiController::class, 'getParentAccountsByType']);
    Route::get('/accounts/balance-summary', [ChartOfAccountsApiController::class, 'balanceSummary']);
    Route::post('/accounts', [ChartOfAccountsApiController::class, 'store']);
    Route::get('/accounts/{account}', [ChartOfAccountsApiController::class, 'show']);
    Route::put('/accounts/{account}', [ChartOfAccountsApiController::class, 'update']);
    Route::delete('/accounts/{account}', [ChartOfAccountsApiController::class, 'destroy']);
    Route::post('/accounts/{account}/toggle-status', [ChartOfAccountsApiController::class, 'toggleStatus']);
    
    // Roles
    Route::apiResource('roles', RolesApiController::class);
    
    // Settings
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::delete('/profile', [ProfileApiController::class, 'destroy']);
    Route::put('/password', [PasswordApiController::class, 'update']);
});
