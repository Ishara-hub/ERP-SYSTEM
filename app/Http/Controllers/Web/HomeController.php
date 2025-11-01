<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Payment;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        try {
            // Overall Statistics
            $stats = [
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
                'total_items' => Item::count(),
                'total_invoices' => Invoice::count(),
                'total_purchase_orders' => PurchaseOrder::count(),
                'total_sales_orders' => SalesOrder::count(),
                'active_customers' => Customer::where('is_active', true)->count(),
                'active_suppliers' => Supplier::where('is_active', true)->count(),
                'active_items' => Item::where('is_active', true)->count(),
            ];

            // Financial Overview
            $financial = [
                'total_sales' => Invoice::sum('total_amount'),
                'total_purchases' => PurchaseOrder::sum('total_amount'),
                'total_received' => Payment::where('payment_type', 'received')->sum('amount'),
                'total_paid' => Payment::where('payment_type', 'paid')->sum('amount'),
                'pending_invoices' => Invoice::where('status', 'unpaid')->count(),
                'pending_amount' => Invoice::where('status', 'unpaid')->sum('total_amount'),
            ];

            // Recent Activity
            $recent_invoices = Invoice::with('customer')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'customer_name' => $invoice->customer->name ?? 'N/A',
                        'total_amount' => number_format($invoice->total_amount, 2),
                        'status' => $invoice->status,
                        'date' => $invoice->date->format('M d, Y'),
                    ];
                });

            $recent_purchase_orders = PurchaseOrder::with('supplier')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number ?? "#{$po->id}",
                        'supplier_name' => $po->supplier->name ?? 'N/A',
                        'total_amount' => number_format($po->total_amount ?? 0, 2),
                        'status' => $po->status,
                        'date' => $po->order_date->format('M d, Y'),
                    ];
                });

            // Performance Metrics (Last 30 Days)
            $last30Days = Carbon::now()->subDays(30);
            
            $sales_data = Invoice::where('date', '>=', $last30Days)
                ->selectRaw('DATE(date) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d'),
                        'amount' => (float) $item->total,
                    ];
                });

            $purchase_data = PurchaseOrder::where('order_date', '>=', $last30Days)
                ->selectRaw('DATE(order_date) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d'),
                        'amount' => (float) ($item->total ?? 0),
                    ];
                });

            // Status Breakdown
            $invoice_status = [
                'paid' => Invoice::where('status', 'paid')->count(),
                'unpaid' => Invoice::where('status', 'unpaid')->count(),
                'partial' => Invoice::where('status', 'partial')->count(),
            ];

            return view('home', compact(
                'stats',
                'financial',
                'recent_invoices',
                'recent_purchase_orders',
                'sales_data',
                'purchase_data',
                'invoice_status'
            ));
        } catch (\Exception $e) {
            // Fallback data if database queries fail
            $stats = [
                'total_customers' => 0,
                'total_suppliers' => 0,
                'total_items' => 0,
                'total_invoices' => 0,
                'total_purchase_orders' => 0,
                'total_sales_orders' => 0,
                'active_customers' => 0,
                'active_suppliers' => 0,
                'active_items' => 0,
            ];

            $financial = [
                'total_sales' => 0,
                'total_purchases' => 0,
                'total_received' => 0,
                'total_paid' => 0,
                'pending_invoices' => 0,
                'pending_amount' => 0,
            ];

            $recent_invoices = collect();
            $recent_purchase_orders = collect();
            $sales_data = collect();
            $purchase_data = collect();
            $invoice_status = ['paid' => 0, 'unpaid' => 0, 'partial' => 0];

            return view('home', compact(
                'stats',
                'financial',
                'recent_invoices',
                'recent_purchase_orders',
                'sales_data',
                'purchase_data',
                'invoice_status'
            ));
        }
    }
}

