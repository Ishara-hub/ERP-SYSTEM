<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ChartOfAccountsController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PaymentDashboardController;
use App\Http\Controllers\Web\EnterBillController;
use App\Http\Controllers\Web\PayBillController;
use App\Http\Controllers\Web\BankReconciliationController;
use App\Http\Controllers\Web\JournalEntryController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\ReportsController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\POS\POSController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\Accounts\GeneralLedgerController;
use App\Http\Controllers\Accounts\ChartOfAccountsDataController;
use App\Http\Controllers\Accounts\SubAccountDetailsController;
use App\Http\Controllers\Accounts\BalanceSheetController;
use App\Http\Controllers\Accounts\IncomeStatementController;

// Test route
Route::get('/test', function () {
    return 'Test route working!';
});

// Test account creation
Route::get('/test-create-account', function () {
    try {
        $account = App\Models\Account::create([
            'account_code' => 'TEST' . time(),
            'account_name' => 'Test Account',
            'account_type' => 'Asset',
            'description' => 'Test account',
            'is_active' => true
        ]);
        return response()->json(['success' => true, 'account_id' => $account->id]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});


// Test accounts route
Route::get('/test-accounts', function () {
    $accounts = App\Models\Account::with('parent')->take(5)->get();
    return response()->json([
        'count' => $accounts->count(),
        'accounts' => $accounts->toArray()
    ]);
});

// Test controller route
Route::get('/test-controller', [ChartOfAccountsController::class, 'index']);

// Test simple controller
Route::get('/test-simple', [App\Http\Controllers\Web\TestController::class, 'index']);

// Test customers
Route::get('/test-customers', function () {
    try {
        $count = App\Models\Customer::count();
        return response()->json(['success' => true, 'customer_count' => $count]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

// Test customer controller
Route::get('/test-customer-controller', function() {
    try {
        $controller = new App\Http\Controllers\Web\CustomerController();
        return $controller->index(new Illuminate\Http\Request());
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Simple test route for customers
Route::get('/test-customers-simple', function() {
    return 'Customers test route working!';
});

// Dashboard and Home Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// User Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
});

// Customer Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'customers.web.index',
        'create' => 'customers.web.create',
        'store' => 'customers.web.store',
        'show' => 'customers.web.show',
        'edit' => 'customers.web.edit',
        'update' => 'customers.web.update',
        'destroy' => 'customers.web.destroy',
    ]);
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.web.toggle-status');
    Route::get('customers/bulk/create', [CustomerController::class, 'bulkCreate'])->name('customers.web.bulk-create');
    Route::post('customers/bulk/store', [CustomerController::class, 'bulkStore'])->name('customers.web.bulk-store');
});

// Item Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    // Specific item routes must come before resource route
    Route::get('items/bulk/create', [ItemController::class, 'bulkCreate'])->name('items.web.bulk-create');
    Route::post('items/bulk/store', [ItemController::class, 'bulkStore'])->name('items.web.bulk-store');
    Route::get('items/csv-import', [ItemController::class, 'csvImport'])->name('items.web.csv-import');
    Route::post('items/csv-import', [ItemController::class, 'csvImportStore'])->name('items.web.csv-import-store');
    Route::post('items/{item}/toggle-status', [ItemController::class, 'toggleStatus'])->name('items.web.toggle-status');
    Route::get('items/child-items', [ItemController::class, 'getChildItems'])->name('items.web.child-items');
    
    // Resource route (must come after specific routes)
    Route::resource('items', ItemController::class)->names([
        'index' => 'items.web.index',
        'create' => 'items.web.create',
        'store' => 'items.web.store',
        'show' => 'items.web.show',
        'edit' => 'items.web.edit',
        'update' => 'items.web.update',
        'destroy' => 'items.web.destroy',
    ]);
});

// Supplier Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'suppliers.web.index',
        'create' => 'suppliers.web.create',
        'store' => 'suppliers.web.store',
        'show' => 'suppliers.web.show',
        'edit' => 'suppliers.web.edit',
        'update' => 'suppliers.web.update',
        'destroy' => 'suppliers.web.destroy',
    ]);
    Route::post('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.web.toggle-status');
    Route::get('suppliers/bulk/create', [SupplierController::class, 'bulkCreate'])->name('suppliers.web.bulk-create');
    Route::post('suppliers/bulk/store', [SupplierController::class, 'bulkStore'])->name('suppliers.web.bulk-store');
});

// Purchase Order Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('purchase-orders', PurchaseOrderController::class)->names([
        'index' => 'purchase-orders.web.index',
        'create' => 'purchase-orders.web.create',
        'store' => 'purchase-orders.web.store',
        'show' => 'purchase-orders.web.show',
        'edit' => 'purchase-orders.web.edit',
        'update' => 'purchase-orders.web.update',
        'destroy' => 'purchase-orders.web.destroy',
    ]);
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.web.print');
    Route::post('purchase-orders/{purchaseOrder}/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.web.update-status');
    Route::get('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.web.receive');
    Route::post('purchase-orders/{purchaseOrder}/receive-inventory', [PurchaseOrderController::class, 'receiveInventory'])->name('purchase-orders.web.receive-inventory');
});

// Payment Dashboard Routes (Protected by authentication) - Must come before resource routes
Route::middleware('auth')->group(function () {
    Route::get('payments/dashboard', [PaymentDashboardController::class, 'index'])->name('payments.web.dashboard');
    Route::get('payments/reconciliation', [PaymentDashboardController::class, 'reconciliation'])->name('payments.web.reconciliation');
    Route::get('payments/expenses', [PaymentDashboardController::class, 'expenses'])->name('payments.web.expenses');
    Route::get('payments/reports', [PaymentDashboardController::class, 'reports'])->name('payments.web.reports');
});

// Bill Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    // Enter Bill Routes - specific routes first
    Route::get('bills/enter-bill/get-po-items', [EnterBillController::class, 'getPOItems'])->name('bills.enter-bill.get-po-items');
    // Then resource routes
    Route::resource('bills/enter-bill', EnterBillController::class)->names([
        'index' => 'bills.enter-bill.index',
        'create' => 'bills.enter-bill.create',
        'store' => 'bills.enter-bill.store',
        'show' => 'bills.enter-bill.show',
        'edit' => 'bills.enter-bill.edit',
        'update' => 'bills.enter-bill.update',
        'destroy' => 'bills.enter-bill.destroy',
    ]);

    // Pay Bill Routes
    Route::resource('bills/pay-bill', PayBillController::class)->names([
        'index' => 'bills.pay-bill.index',
        'create' => 'bills.pay-bill.create',
        'store' => 'bills.pay-bill.store',
        'show' => 'bills.pay-bill.show',
    ]);
    
    // Additional Pay Bill Routes
    Route::get('bills/pay-bill/{payment}/voucher', [PayBillController::class, 'printVoucher'])->name('bills.pay-bill.voucher');
    Route::get('bills/pay-bill/unpaid-bills', [PayBillController::class, 'getUnpaidBills'])->name('bills.pay-bill.unpaid-bills');
});

// Payment Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('payments', PaymentController::class)->names([
        'index' => 'payments.web.index',
        'create' => 'payments.web.create',
        'store' => 'payments.web.store',
        'show' => 'payments.web.show',
        'edit' => 'payments.web.edit',
        'update' => 'payments.web.update',
        'destroy' => 'payments.web.destroy',
    ]);
    Route::get('purchase-orders/{purchaseOrder}/payments/create', [PaymentController::class, 'createForPurchaseOrder'])->name('purchase-orders.web.payments.create');
    
    // Customer Payment (Receive Payment)
    Route::get('customer-payment', [\App\Http\Controllers\Payments\CustomerPaymentController::class, 'index'])->name('customer-payment.index');
    Route::post('customer-payment/invoices', [\App\Http\Controllers\Payments\CustomerPaymentController::class, 'getCustomerInvoices'])->name('customer-payment.invoices');
    Route::post('customer-payment', [\App\Http\Controllers\Payments\CustomerPaymentController::class, 'store'])->name('customer-payment.store');

    // Record Deposit
    Route::get('record-deposit', [\App\Http\Controllers\Payments\RecordDepositController::class, 'index'])->name('record-deposit.index');
    Route::post('record-deposit', [\App\Http\Controllers\Payments\RecordDepositController::class, 'store'])->name('record-deposit.store');
});

// Invoice Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('invoices', InvoiceController::class)->names([
        'index' => 'invoices.web.index',
        'create' => 'invoices.web.create',
        'store' => 'invoices.web.store',
        'show' => 'invoices.web.show',
        'edit' => 'invoices.web.edit',
        'update' => 'invoices.web.update',
        'destroy' => 'invoices.web.destroy',
    ]);
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.web.print');
    Route::post('invoices/{invoice}/email', [InvoiceController::class, 'email'])->name('invoices.web.email');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.web.mark-paid');
});

// POS Dashboard Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('pos', [POSController::class, 'index'])->name('pos.dashboard');
    Route::post('pos/customer-pricing', [POSController::class, 'getCustomerPricing'])->name('pos.customer-pricing');
    Route::post('pos/customer-invoices', [POSController::class, 'getCustomerInvoices'])->name('pos.customer-invoices');
    Route::post('pos/create-invoice', [POSController::class, 'createInvoice'])->name('pos.create-invoice');
});

// Sales Orders Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('sales-orders', SalesOrderController::class);
});

// Quotations Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::resource('quotations', QuotationController::class);
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
});

// Chart of Accounts Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    // AJAX routes must come before resource routes
    Route::get('accounts/parent-accounts-by-type', [ChartOfAccountsController::class, 'getParentAccountsByType'])->name('accounts.parent-by-type');
    Route::get('accounts/parent-accounts', [ChartOfAccountsController::class, 'getParentAccounts'])->name('accounts.parent-accounts');
    Route::get('accounts/balance-summary', [ChartOfAccountsController::class, 'balanceSummary'])->name('accounts.balance-summary');
    Route::get('accounts/generate-code', [ChartOfAccountsController::class, 'generateAccountCodeAjax'])->name('accounts.generate-code');

    // Account Type and Sub-Account Creation Routes
    Route::post('accounts/create-account-type', [ChartOfAccountsController::class, 'createAccountType'])->name('accounts.create-account-type');
    Route::post('accounts/create-sub-account', [ChartOfAccountsController::class, 'createSubAccount'])->name('accounts.create-sub-account');

    // General Ledger Routes - MUST come before resource routes to avoid route conflicts
    Route::get('accounts/general-ledger', [GeneralLedgerController::class, 'index'])->name('accounts.general-ledger.index');
    Route::get('accounts/general-ledger/export', [GeneralLedgerController::class, 'export'])->name('accounts.general-ledger.export');

    // Chart of Accounts Data Routes - MUST come before resource routes
    Route::get('accounts/reports/chart-of-accounts-data', [ChartOfAccountsDataController::class, 'index'])->name('accounts.reports.chart-of-accounts-data');
    Route::get('accounts/reports/sub-account-details/{account}', [SubAccountDetailsController::class, 'show'])->name('accounts.reports.sub-account-details');
    
    // Balance Sheet and Income Statement Routes - MUST come before resource routes
    Route::get('accounts/reports/balance-sheet', [BalanceSheetController::class, 'index'])->name('accounts.reports.balance-sheet');
    Route::get('accounts/reports/income-statement', [IncomeStatementController::class, 'index'])->name('accounts.reports.income-statement');
    Route::get('accounts/reports/trial-balance', [\App\Http\Controllers\Accounts\TrialBalanceController::class, 'index'])->name('accounts.reports.trial-balance');
    Route::get('accounts/reports/cash-flow', [\App\Http\Controllers\Accounts\CashFlowStatementController::class, 'index'])->name('accounts.reports.cash-flow');
    Route::get('accounts/reports/ar-aging', [\App\Http\Controllers\Accounts\ARAgingController::class, 'index'])->name('accounts.reports.ar-aging');
    Route::get('accounts/reports/customer-balance', [\App\Http\Controllers\Accounts\CustomerBalanceController::class, 'index'])->name('accounts.reports.customer-balance');
    Route::get('accounts/reports/vendor-balance', [\App\Http\Controllers\Accounts\VendorBalanceController::class, 'index'])->name('accounts.reports.vendor-balance');

    // Write Check Routes - MUST come before resource routes
    Route::get('accounts/write-check', [\App\Http\Controllers\Accounts\WriteCheckController::class, 'index'])->name('accounts.write-check.index');
    Route::post('accounts/write-check', [\App\Http\Controllers\Accounts\WriteCheckController::class, 'store'])->name('accounts.write-check.store');
    Route::get('accounts/write-check/balance', [\App\Http\Controllers\Accounts\WriteCheckController::class, 'getAccountBalance'])->name('accounts.write-check.balance');
    Route::get('accounts/write-check/voucher/{id}', [\App\Http\Controllers\Accounts\WriteCheckController::class, 'printVoucher'])->name('accounts.write-check.voucher');

    // Main Chart of Accounts Resource Routes
    Route::resource('accounts', ChartOfAccountsController::class);
    Route::post('accounts/{account}/toggle-status', [ChartOfAccountsController::class, 'toggleStatus'])->name('accounts.toggle-status');

    // Sub-Accounts Routes
    Route::get('accounts/{account}/sub-accounts', [ChartOfAccountsController::class, 'getSubAccounts'])->name('accounts.sub-accounts');
});

// Bank Reconciliation Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('bank-reconciliation', [BankReconciliationController::class, 'index'])->name('bank-reconciliation.index');
    Route::post('bank-reconciliation/begin', [BankReconciliationController::class, 'begin'])->name('bank-reconciliation.begin');
    Route::post('bank-reconciliation/store-transactions', [BankReconciliationController::class, 'storeBankTransactions'])->name('bank-reconciliation.store-transactions');
    Route::post('bank-reconciliation/reconcile', [BankReconciliationController::class, 'reconcile'])->name('bank-reconciliation.reconcile');
    Route::post('bank-reconciliation/auto-match', [BankReconciliationController::class, 'autoMatch'])->name('bank-reconciliation.auto-match');
    Route::get('bank-reconciliation/summary', [BankReconciliationController::class, 'summary'])->name('bank-reconciliation.summary');
});

// Journal Entry Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.web.index');
    Route::get('journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.web.create');
    Route::post('journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.web.store');
    Route::get('journal-entries/{id}', [JournalEntryController::class, 'show'])->name('journal-entries.web.show');
});

// Inventory Management Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('inventory/adjust', [InventoryController::class, 'adjustmentForm'])->name('inventory.adjustment-form');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show');
});

// Reports Routes (Protected by authentication)
Route::middleware('auth')->group(function () {
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/sales-by-customer', [ReportsController::class, 'salesByCustomer'])->name('reports.sales-by-customer');
    Route::get('reports/sales-by-item', [ReportsController::class, 'salesByItem'])->name('reports.sales-by-item');
    Route::get('reports/sales-trend', [ReportsController::class, 'salesTrend'])->name('reports.sales-trend');
    Route::get('reports/purchase-by-supplier', [ReportsController::class, 'purchaseBySupplier'])->name('reports.purchase-by-supplier');
    Route::get('reports/purchase-by-item', [ReportsController::class, 'purchaseByItem'])->name('reports.purchase-by-item');
    Route::get('reports/invoice-summary', [ReportsController::class, 'invoiceSummary'])->name('reports.invoice-summary');
    Route::get('reports/income-summary', [ReportsController::class, 'incomeSummary'])->name('reports.income-summary');
    Route::get('reports/item-profitability', [ReportsController::class, 'itemProfitability'])->name('reports.item-profitability');
    Route::get('reports/export', [ReportsController::class, 'export'])->name('reports.export');

    // Inventory Reports
    Route::get('reports/inventory/valuation-summary', [\App\Http\Controllers\Web\InventoryReportsController::class, 'valuationSummary'])->name('reports.inventory.valuation-summary');
    Route::get('reports/inventory/valuation-detail', [\App\Http\Controllers\Web\InventoryReportsController::class, 'valuationDetail'])->name('reports.inventory.valuation-detail');
    Route::get('reports/inventory/product-service-list', [\App\Http\Controllers\Web\InventoryReportsController::class, 'productServiceList'])->name('reports.inventory.product-service-list');
    Route::get('reports/inventory/stock-on-hand', [\App\Http\Controllers\Web\InventoryReportsController::class, 'stockOnHand'])->name('reports.inventory.stock-on-hand');
    Route::get('reports/inventory/stock-movement', [\App\Http\Controllers\Web\InventoryReportsController::class, 'stockMovement'])->name('reports.inventory.stock-movement');
    Route::get('reports/inventory/low-stock', [\App\Http\Controllers\Web\InventoryReportsController::class, 'lowStock'])->name('reports.inventory.low-stock');
    Route::get('reports/inventory/sales-by-item', [\App\Http\Controllers\Web\InventoryReportsController::class, 'salesByItem'])->name('reports.inventory.sales-by-item');
    Route::get('reports/inventory/purchases-by-item', [\App\Http\Controllers\Web\InventoryReportsController::class, 'purchasesByItem'])->name('reports.inventory.purchases-by-item');
});

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