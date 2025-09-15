<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_invoices' => Invoice::count(),
            'total_purchase_orders' => PurchaseOrder::count(),
            'total_sales_orders' => SalesOrder::count(),
            'pending_invoices' => Invoice::where('status', 'unpaid')->count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'pending_sales_orders' => SalesOrder::where('status', 'pending')->count(),
        ];

        $recent_employees = Employee::with(['department', 'designation'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_customers = Customer::latest()
            ->limit(5)
            ->get();

        $recent_invoices = Invoice::with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                // Ensure total_amount is properly cast to float
                $invoice->total_amount = (float) $invoice->total_amount;
                return $invoice;
            });

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recent_employees' => $recent_employees,
            'recent_customers' => $recent_customers,
            'recent_invoices' => $recent_invoices,
        ]);
    }
}
