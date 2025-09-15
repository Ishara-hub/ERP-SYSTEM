<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Test route to verify Inertia is working
Route::get('/test-payment', function () {
    return Inertia::render('payments/general-payment', [
        'bankAccounts' => [
            (object)['id' => 1, 'name' => 'Test Account', 'account_number' => '123456', 'balance' => 1000.00],
        ],
        'accounts' => [
            (object)['id' => 1, 'name' => 'Test Account', 'type' => 'expense'],
        ],
        'customers' => [
            (object)['id' => 1, 'name' => 'Test Customer', 'email' => 'test@example.com', 'address' => '123 Test St'],
        ],
    ]);
})->name('test.payment');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
            // User Management Routes (Admin only)
            Route::middleware(['role:admin'])->group(function () {
                Route::resource('users', App\Http\Controllers\UserController::class);
                Route::resource('roles', App\Http\Controllers\RoleController::class);
            });

            // Chart of Accounts Routes (Temporarily without role middleware for testing)
            Route::get('accounts/chart-of-accounts', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'index'])->name('accounts.chart-of-accounts.index');
            Route::get('accounts/create', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'create'])->name('accounts.create');
            Route::post('accounts', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'store'])->name('accounts.store');
            Route::get('accounts/{account}', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'show'])->name('accounts.show');
            Route::get('accounts/{account}/edit', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'edit'])->name('accounts.edit');
            Route::put('accounts/{account}', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'update'])->name('accounts.update');
            Route::delete('accounts/{account}', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'destroy'])->name('accounts.destroy');
            Route::patch('accounts/{account}/toggle-status', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'toggleStatus'])->name('accounts.toggle-status');
            Route::get('accounts/balance-summary', [App\Http\Controllers\Accounts\ChartOfAccountsController::class, 'balanceSummary'])->name('accounts.balance-summary');

            // Items Management Routes
            Route::resource('items', App\Http\Controllers\Items\ItemController::class);
            Route::patch('items/{item}/toggle-status', [App\Http\Controllers\Items\ItemController::class, 'toggleStatus'])->name('items.toggle-status');
            Route::post('items/{item}/components', [App\Http\Controllers\Items\ItemController::class, 'addComponent'])->name('items.add-component');
            Route::put('items/components/{component}', [App\Http\Controllers\Items\ItemController::class, 'updateComponent'])->name('items.update-component');
            Route::delete('items/components/{component}', [App\Http\Controllers\Items\ItemController::class, 'removeComponent'])->name('items.remove-component');

            // Customer Management Routes
            Route::resource('customers', App\Http\Controllers\Customers\CustomerController::class);
            Route::patch('customers/{customer}/toggle-status', [App\Http\Controllers\Customers\CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

            // Invoice Management Routes
            Route::resource('invoices', App\Http\Controllers\Invoices\InvoiceController::class);
            Route::get('invoices/{invoice}/print', [App\Http\Controllers\Invoices\InvoiceController::class, 'print'])->name('invoices.print');
            Route::post('invoices/{invoice}/email', [App\Http\Controllers\Invoices\InvoiceController::class, 'email'])->name('invoices.email');
            Route::patch('invoices/{invoice}/mark-paid', [App\Http\Controllers\Invoices\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');

            // Payment Management Routes
            Route::resource('payments', App\Http\Controllers\Payments\PaymentController::class);
            Route::get('invoices/{invoice}/receive-payment', [App\Http\Controllers\Payments\PaymentController::class, 'receivePayment'])->name('invoices.receive-payment');
            Route::post('invoices/{invoice}/receive-payment', [App\Http\Controllers\Payments\PaymentController::class, 'storeReceivedPayment'])->name('invoices.store-payment');
            Route::get('payments/general', [App\Http\Controllers\Payments\PaymentController::class, 'createGeneral'])->name('payments.general');
            Route::post('payments/general', [App\Http\Controllers\Payments\PaymentController::class, 'storeGeneral'])->name('payments.general.store');

            // Supplier Management Routes
            Route::resource('suppliers', App\Http\Controllers\Suppliers\SupplierController::class);
            Route::patch('suppliers/{supplier}/toggle-status', [App\Http\Controllers\Suppliers\SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

            // Purchase Order Management Routes
            Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrders\PurchaseOrderController::class);
            Route::patch('purchase-orders/{purchaseOrder}/update-status', [App\Http\Controllers\PurchaseOrders\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
            Route::get('purchase-orders/{purchaseOrder}/print', [App\Http\Controllers\PurchaseOrders\PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
        });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
