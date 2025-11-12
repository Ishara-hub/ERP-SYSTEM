<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Api\ApiController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Carbon\Carbon;

class HomeApiController extends ApiController
{
    /**
     * Return dashboard metrics used on the home page.
     */
    public function index()
    {
        try {
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

            $financial = [
                'total_sales' => Invoice::sum('total_amount'),
                'total_purchases' => PurchaseOrder::sum('total_amount'),
                'total_received' => Payment::where('payment_type', 'received')->sum('amount'),
                'total_paid' => Payment::where('payment_type', 'paid')->sum('amount'),
                'pending_invoices' => Invoice::where('status', 'unpaid')->count(),
                'pending_amount' => Invoice::where('status', 'unpaid')->sum('total_amount'),
            ];

            $recentInvoices = Invoice::with('customer')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'customer_name' => $invoice->customer->name ?? 'N/A',
                        'total_amount' => (float) $invoice->total_amount,
                        'status' => $invoice->status,
                        'date' => optional($invoice->date)->format('M d, Y'),
                    ];
                });

            $recentPurchaseOrders = PurchaseOrder::with('supplier')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number ?? "#{$po->id}",
                        'supplier_name' => $po->supplier->name ?? 'N/A',
                        'total_amount' => (float) ($po->total_amount ?? 0),
                        'status' => $po->status,
                        'date' => optional($po->order_date)->format('M d, Y'),
                    ];
                });

            $last30Days = Carbon::now()->subDays(30);

            $salesData = Invoice::where('date', '>=', $last30Days)
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

            $purchaseData = PurchaseOrder::where('order_date', '>=', $last30Days)
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

            $invoiceStatus = [
                'paid' => Invoice::where('status', 'paid')->count(),
                'unpaid' => Invoice::where('status', 'unpaid')->count(),
                'partial' => Invoice::where('status', 'partial')->count(),
            ];

            $data = [
                'stats' => $stats,
                'financial' => $financial,
                'recent_invoices' => $recentInvoices,
                'recent_purchase_orders' => $recentPurchaseOrders,
                'sales_data' => $salesData,
                'purchase_data' => $purchaseData,
                'invoice_status' => $invoiceStatus,
            ];

            return $this->success($data, 'Home metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve home metrics: ' . $e->getMessage());
        }
    }
}

