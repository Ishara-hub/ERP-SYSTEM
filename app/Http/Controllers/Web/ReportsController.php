<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display reports index page
     */
    public function index()
    {
        $totalInvoices = Invoice::count();
        $totalPOs = PurchaseOrder::count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();
        
        return view('reports.index', compact('totalInvoices', 'totalPOs', 'totalCustomers', 'totalSuppliers'));
    }

    /**
     * Sales by Customer Report
     */
    public function salesByCustomer(Request $request)
    {
        $query = Invoice::with('customer')
            ->where('status', '!=', 'draft');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get();

        // Group by customer
        $customerSales = $invoices->groupBy('customer_id')->map(function ($group, $customerId) {
            $customer = $group->first()->customer;
            return [
                'customer_id' => $customerId,
                'customer_name' => $customer->name ?? 'Unknown',
                'invoice_count' => $group->count(),
                'total_sales' => $group->sum('total_amount'),
                'total_paid' => $group->sum('payments_applied'),
                'total_due' => $group->sum('balance_due'),
                'invoices' => $group,
            ];
        })->sortByDesc('total_sales');

        $customers = Customer::orderBy('name')->get();

        return view('reports.sales-by-customer', compact('customerSales', 'customers'));
    }

    /**
     * Sales by Item Report
     */
    public function salesByItem(Request $request)
    {
        $query = InvoiceLineItem::with(['invoice.customer', 'item'])
            ->whereHas('invoice', function($q) {
                $q->where('status', '!=', 'draft');
            });

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereHas('invoice', function($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            });
        }
        if ($request->filled('date_to')) {
            $query->whereHas('invoice', function($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            });
        }

        // Item filter
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $lineItems = $query->get();

        // Group by item
        $itemSales = $lineItems->groupBy('item_id')->map(function ($group, $itemId) {
            $item = $group->first()->item;
            $totalQuantity = $group->sum('quantity');
            $totalRevenue = $group->sum('amount');
            
            return [
                'item_id' => $itemId,
                'item_name' => $item->item_name ?? 'Unknown',
                'item_number' => $item->item_number ?? '',
                'total_quantity_sold' => $totalQuantity,
                'total_revenue' => $totalRevenue,
                'average_price' => $totalQuantity > 0 ? $totalRevenue / $totalQuantity : 0,
                'invoice_count' => $group->pluck('invoice_id')->unique()->count(),
                'line_items' => $group,
            ];
        })->sortByDesc('total_revenue');

        $items = Item::orderBy('item_name')->get();

        return view('reports.sales-by-item', compact('itemSales', 'items'));
    }

    /**
     * Purchase by Supplier Report
     */
    public function purchaseBySupplier(Request $request)
    {
        $query = PurchaseOrder::with('supplier')
            ->where('status', '!=', 'draft');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->get();

        // Group by supplier
        $supplierPurchases = $purchaseOrders->groupBy('supplier_id')->map(function ($group, $supplierId) {
            $supplier = $group->first()->supplier;
            $totalPaid = $group->sum(function($po) {
                return $po->payments()->where('status', 'completed')->sum('amount');
            });
            
            return [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name ?? 'Unknown',
                'po_count' => $group->count(),
                'total_purchases' => $group->sum('total_amount'),
                'total_paid' => $totalPaid,
                'total_due' => $group->sum('total_amount') - $totalPaid,
                'purchase_orders' => $group,
            ];
        })->sortByDesc('total_purchases');

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchase-by-supplier', compact('supplierPurchases', 'suppliers'));
    }

    /**
     * Purchase by Item Report
     */
    public function purchaseByItem(Request $request)
    {
        $query = PurchaseOrderItem::with(['purchaseOrder.supplier', 'item'])
            ->whereHas('purchaseOrder', function($q) {
                $q->where('status', '!=', 'draft');
            });

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereHas('purchaseOrder', function($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            });
        }
        if ($request->filled('date_to')) {
            $query->whereHas('purchaseOrder', function($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_to);
            });
        }

        // Item filter
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $poItems = $query->get();

        // Group by item
        $itemPurchases = $poItems->groupBy('item_id')->map(function ($group, $itemId) {
            $item = $group->first()->item;
            $totalQuantity = $group->sum('quantity');
            $totalCost = $group->sum('amount');
            
            return [
                'item_id' => $itemId,
                'item_name' => $item->item_name ?? 'Unknown',
                'item_number' => $item->item_number ?? '',
                'total_quantity_purchased' => $totalQuantity,
                'total_cost' => $totalCost,
                'average_cost' => $totalQuantity > 0 ? $totalCost / $totalQuantity : 0,
                'po_count' => $group->pluck('purchase_order_id')->unique()->count(),
                'line_items' => $group,
            ];
        })->sortByDesc('total_cost');

        $items = Item::orderBy('item_name')->get();

        return view('reports.purchase-by-item', compact('itemPurchases', 'items'));
    }

    /**
     * Invoice Summary Report
     */
    public function invoiceSummary(Request $request)
    {
        $query = Invoice::with('customer');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('date', 'desc')->get();

        // Summary statistics
        $summary = [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('payments_applied'),
            'total_due' => $invoices->sum('balance_due'),
            'by_status' => $invoices->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                ];
            }),
        ];

        return view('reports.invoice-summary', compact('invoices', 'summary'));
    }

    /**
     * Income Summary Report
     */
    public function incomeSummary(Request $request)
    {
        $query = Invoice::with('customer')
            ->where('status', '!=', 'draft');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $invoices = $query->get();

        // Monthly summary
        $monthlyIncome = $invoices->groupBy(function($invoice) {
            return $invoice->date->format('Y-m');
        })->map(function($group, $month) {
            return [
                'month' => $month,
                'invoice_count' => $group->count(),
                'total_income' => $group->sum('total_amount'),
                'total_paid' => $group->sum('payments_applied'),
            ];
        })->sortKeys();

        // Overall summary
        $summary = [
            'total_income' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('payments_applied'),
            'total_outstanding' => $invoices->sum('balance_due'),
            'invoice_count' => $invoices->count(),
            'average_invoice' => $invoices->count() > 0 ? $invoices->sum('total_amount') / $invoices->count() : 0,
        ];

        return view('reports.income-summary', compact('monthlyIncome', 'summary', 'invoices'));
    }

    /**
     * Item Profitability Report
     */
    public function itemProfitability(Request $request)
    {
        $query = Item::with(['invoiceLineItems.invoice', 'purchaseOrderItems.purchaseOrder']);

        if ($request->filled('item_id')) {
            $query->where('id', $request->item_id);
        }

        $items = $query->get();

        // Calculate profitability for each item
        $itemProfits = $items->map(function ($item) {
            // Get sales data
            $salesItems = InvoiceLineItem::where('item_id', $item->id)
                ->whereHas('invoice', function($q) {
                    $q->where('status', '!=', 'draft');
                })
                ->get();
            
            $totalQuantitySold = $salesItems->sum('quantity');
            $totalSalesRevenue = $salesItems->sum('amount');
            
            // Get purchase data
            $purchaseItems = PurchaseOrderItem::where('item_id', $item->id)
                ->whereHas('purchaseOrder', function($q) {
                    $q->where('status', '!=', 'draft');
                })
                ->get();
            
            $totalQuantityPurchased = $purchaseItems->sum('quantity');
            $totalPurchaseCost = $purchaseItems->sum('amount');
            
            // Calculate profitability
            $averageSalePrice = $totalQuantitySold > 0 ? $totalSalesRevenue / $totalQuantitySold : 0;
            $averagePurchasePrice = $totalQuantityPurchased > 0 ? $totalPurchaseCost / $totalQuantityPurchased : 0;
            $profitMargin = $averageSalePrice - $averagePurchasePrice;
            $profitMarginPercent = $averageSalePrice > 0 ? ($profitMargin / $averageSalePrice) * 100 : 0;
            $totalProfit = ($averageSalePrice - $averagePurchasePrice) * $totalQuantitySold;
            
            return [
                'item' => $item,
                'total_quantity_sold' => $totalQuantitySold,
                'total_quantity_purchased' => $totalQuantityPurchased,
                'total_sales_revenue' => $totalSalesRevenue,
                'total_purchase_cost' => $totalPurchaseCost,
                'average_sale_price' => $averageSalePrice,
                'average_purchase_price' => $averagePurchasePrice,
                'profit_margin' => $profitMargin,
                'profit_margin_percent' => $profitMarginPercent,
                'total_profit' => $totalProfit,
            ];
        })->sortByDesc('total_profit')->filter(function($item) {
            return $item['total_quantity_sold'] > 0; // Only show items with sales
        });

        return view('reports.item-profitability', compact('itemProfits'));
    }

    /**
     * Sales Trend Report
     */
    public function salesTrend(Request $request)
    {
        $query = Invoice::where('status', '!=', 'draft');

        // Default to last 12 months if no date range specified
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->where('date', '>=', now()->subMonths(12));
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }
        }

        $invoices = $query->get();

        // Group by month
        $monthlyData = $invoices->groupBy(function($invoice) {
            return $invoice->date->format('Y-m');
        })->map(function($group, $month) {
            return [
                'month' => $month,
                'month_label' => \Carbon\Carbon::parse($month . '-01')->format('M Y'),
                'invoice_count' => $group->count(),
                'total_sales' => $group->sum('total_amount'),
                'total_paid' => $group->sum('payments_applied'),
            ];
        })->sortKeys();

        return view('reports.sales-trend', compact('monthlyData'));
    }

    /**
     * Export report to PDF
     */
    public function export(Request $request)
    {
        // Implementation for PDF export can be added later using DomPDF
        return back()->with('info', 'Export functionality coming soon.');
    }
}
