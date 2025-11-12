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
use App\Http\Controllers\Api\Payments\PurchaseOrderPaymentsApiController;
use App\Http\Controllers\Api\PurchaseOrders\PurchaseOrdersApiController;
use App\Http\Controllers\Api\Accounts\ChartOfAccountsApiController;
use App\Http\Controllers\Api\Accounting\JournalEntriesApiController;
use App\Http\Controllers\Api\Banking\BankReconciliationApiController;
use App\Http\Controllers\Api\Bills\EnterBillsApiController;
use App\Http\Controllers\Api\Bills\PayBillsApiController;
use App\Http\Controllers\Api\Roles\RolesApiController;
use App\Http\Controllers\Api\Settings\ProfileApiController;
use App\Http\Controllers\Api\Settings\PasswordApiController;
use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\Home\HomeApiController;
use App\Http\Controllers\Api\Inventory\InventoryApiController;
use App\Http\Controllers\Api\Payments\PaymentDashboardApiController;
use App\Http\Controllers\Api\Reports\ReportsApiController;
use App\Http\Controllers\Api\POS\POSApiController;

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

// Public customer search for invoice creation
Route::get('/customers/search', [CustomersApiController::class, 'search']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/user', [AuthApiController::class, 'user']);
    Route::post('/auth/refresh', [AuthApiController::class, 'refresh']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    Route::get('/home', [HomeApiController::class, 'index']);
    
    // Users
    Route::apiResource('users', UsersApiController::class);
    Route::post('/users/{user}/assign-roles', [UsersApiController::class, 'assignRoles']);
    Route::delete('/users/{user}/remove-roles', [UsersApiController::class, 'removeRoles']);
    
    // Customers
    Route::post('/customers/bulk', [CustomersApiController::class, 'bulkStore']);
    Route::apiResource('customers', CustomersApiController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomersApiController::class, 'toggleStatus']);
    
    // Suppliers
    Route::post('/suppliers/bulk', [SuppliersApiController::class, 'bulkStore']);
    Route::apiResource('suppliers', SuppliersApiController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SuppliersApiController::class, 'toggleStatus']);
    
    // Invoices
    Route::apiResource('invoices', InvoicesApiController::class);
    Route::post('/invoices/{invoice}/mark-paid', [InvoicesApiController::class, 'markAsPaid']);
    Route::post('/invoices/{invoice}/print', [InvoicesApiController::class, 'print']);
    Route::post('/invoices/{invoice}/email', [InvoicesApiController::class, 'email']);
    
    // Items
    Route::get('/items/options', [ItemsApiController::class, 'options']);
    Route::get('/items/children', [ItemsApiController::class, 'getChildItems']);
    Route::post('/items/bulk', [ItemsApiController::class, 'bulkStore']);
    Route::post('/items/csv-import', [ItemsApiController::class, 'csvImport']);
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
    Route::get('/purchase-orders/options', [PurchaseOrdersApiController::class, 'options']);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrdersApiController::class, 'receiveInventory']);
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

    // Bills (Enter Bills)
    Route::get('/bills', [EnterBillsApiController::class, 'index']);
    Route::post('/bills', [EnterBillsApiController::class, 'store']);
    Route::get('/bills/{bill}', [EnterBillsApiController::class, 'show']);
    Route::put('/bills/{bill}', [EnterBillsApiController::class, 'update']);
    Route::delete('/bills/{bill}', [EnterBillsApiController::class, 'destroy']);
    Route::get('/bills/purchase-order-items', [EnterBillsApiController::class, 'getPOItems']);

    // Pay Bills
    Route::get('/pay-bills', [PayBillsApiController::class, 'index']);
    Route::post('/pay-bills', [PayBillsApiController::class, 'store']);
    Route::get('/pay-bills/{payment}', [PayBillsApiController::class, 'show']);
    Route::get('/pay-bills/unpaid', [PayBillsApiController::class, 'getUnpaidBills']);
    Route::get('/pay-bills/form-data', [PayBillsApiController::class, 'formData']);

    // Purchase Order Payments
    Route::get('/purchase-order-payments/form-data', [PurchaseOrderPaymentsApiController::class, 'formData']);
    Route::apiResource('purchase-order-payments', PurchaseOrderPaymentsApiController::class)->parameters([
        'purchase-order-payments' => 'purchaseOrderPayment',
    ]);

    // Bank Reconciliation
    Route::get('/bank-reconciliation', [BankReconciliationApiController::class, 'index']);
    Route::post('/bank-reconciliation/begin', [BankReconciliationApiController::class, 'begin']);
    Route::post('/bank-reconciliation/import', [BankReconciliationApiController::class, 'storeBankTransactions']);
    Route::post('/bank-reconciliation/reconcile', [BankReconciliationApiController::class, 'reconcile']);
    Route::post('/bank-reconciliation/auto-match', [BankReconciliationApiController::class, 'autoMatch']);
    Route::get('/bank-reconciliation/summary', [BankReconciliationApiController::class, 'summary']);

    // Inventory
    Route::get('/inventory', [InventoryApiController::class, 'index']);
    Route::get('/inventory/movements', [InventoryApiController::class, 'movements']);
    Route::post('/inventory/adjust', [InventoryApiController::class, 'adjust']);
    Route::get('/inventory/items/{item}', [InventoryApiController::class, 'show']);
    Route::get('/inventory/export', [InventoryApiController::class, 'export']);

    // Journal Entries
    Route::get('/journal-entries/accounts', [JournalEntriesApiController::class, 'accounts']);
    Route::apiResource('journal-entries', JournalEntriesApiController::class)->only(['index', 'store', 'show']);

    // Payment Dashboard
    Route::get('/payments/dashboard', [PaymentDashboardApiController::class, 'index']);
    Route::get('/payments/dashboard/summary', [PaymentDashboardApiController::class, 'summary']);
    Route::get('/payments/dashboard/reconciliation', [PaymentDashboardApiController::class, 'reconciliation']);
    Route::get('/payments/dashboard/expenses', [PaymentDashboardApiController::class, 'expenses']);
    Route::get('/payments/dashboard/reports', [PaymentDashboardApiController::class, 'reports']);

    // POS
    Route::get('/pos/dashboard', [POSApiController::class, 'dashboard']);
    Route::get('/pos/customer-pricing', [POSApiController::class, 'customerPricing']);
    Route::get('/pos/customer-invoices', [POSApiController::class, 'customerInvoices']);
    Route::post('/pos/invoices', [POSApiController::class, 'createInvoice']);

    // Reports
    Route::get('/reports', [ReportsApiController::class, 'index']);
    Route::get('/reports/sales-by-customer', [ReportsApiController::class, 'salesByCustomer']);
    Route::get('/reports/sales-by-item', [ReportsApiController::class, 'salesByItem']);
    Route::get('/reports/purchase-by-supplier', [ReportsApiController::class, 'purchaseBySupplier']);
    Route::get('/reports/purchase-by-item', [ReportsApiController::class, 'purchaseByItem']);
    Route::get('/reports/invoice-summary', [ReportsApiController::class, 'invoiceSummary']);
    Route::get('/reports/income-summary', [ReportsApiController::class, 'incomeSummary']);
    Route::get('/reports/item-profitability', [ReportsApiController::class, 'itemProfitability']);
    Route::get('/reports/sales-trend', [ReportsApiController::class, 'salesTrend']);
    Route::get('/reports/export', [ReportsApiController::class, 'export']);
});
