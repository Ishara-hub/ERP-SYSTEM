<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Api\ApiController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportsApiController extends ApiController
{
    /**
     * Overview counts for reports landing page.
     */
    public function index()
    {
        try {
            return $this->success([
                'total_invoices' => Invoice::count(),
                'total_purchase_orders' => PurchaseOrder::count(),
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
            ], 'Report overview retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve report overview: ' . $e->getMessage());
        }
    }

    /**
     * Sales by customer report.
     */
    public function salesByCustomer(Request $request)
    {
        try {
            $query = Invoice::with('customer')
                ->where('status', '!=', 'draft');

            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            $invoices = $query->get();

            $customerSales = $invoices->groupBy('customer_id')->map(function ($group, $customerId) {
                $customer = $group->first()->customer;
                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer->name ?? 'Unknown',
                    'invoice_count' => $group->count(),
                    'total_sales' => (float) $group->sum('total_amount'),
                    'total_paid' => (float) $group->sum('payments_applied'),
                    'total_due' => (float) $group->sum('balance_due'),
                    'invoices' => $group,
                ];
            })->sortByDesc('total_sales')->values();

            $customers = Customer::orderBy('name')->get();

            return $this->success([
                'customer_sales' => $customerSales,
                'customers' => $customers,
            ], 'Sales by customer retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve sales by customer: ' . $e->getMessage());
        }
    }

    /**
     * Sales by item report.
     */
    public function salesByItem(Request $request)
    {
        try {
            $query = InvoiceLineItem::with(['invoice.customer', 'item'])
                ->whereHas('invoice', function ($q) {
                    $q->where('status', '!=', 'draft');
                });

            if ($request->filled('date_from')) {
                $query->whereHas('invoice', function ($q) use ($request) {
                    $q->whereDate('date', '>=', $request->date_from);
                });
            }
            if ($request->filled('date_to')) {
                $query->whereHas('invoice', function ($q) use ($request) {
                    $q->whereDate('date', '<=', $request->date_to);
                });
            }
            if ($request->filled('item_id')) {
                $query->where('item_id', $request->item_id);
            }

            $lineItems = $query->get();

            $itemSales = $lineItems->groupBy('item_id')->map(function ($group, $itemId) {
                $item = $group->first()->item;
                $totalQuantity = $group->sum('quantity');
                $totalRevenue = $group->sum('amount');

                return [
                    'item_id' => $itemId,
                    'item_name' => $item->item_name ?? 'Unknown',
                    'item_number' => $item->item_number ?? '',
                    'total_quantity_sold' => (float) $totalQuantity,
                    'total_revenue' => (float) $totalRevenue,
                    'average_price' => $totalQuantity > 0 ? (float) ($totalRevenue / $totalQuantity) : 0,
                    'invoice_count' => $group->pluck('invoice_id')->unique()->count(),
                    'line_items' => $group,
                ];
            })->sortByDesc('total_revenue')->values();

            $items = Item::orderBy('item_name')->get();

            return $this->success([
                'item_sales' => $itemSales,
                'items' => $items,
            ], 'Sales by item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve sales by item: ' . $e->getMessage());
        }
    }

    /**
     * Purchase by supplier report.
     */
    public function purchaseBySupplier(Request $request)
    {
        try {
            $query = PurchaseOrder::with('supplier')
                ->where('status', '!=', 'draft');

            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            $purchaseOrders = $query->get();

            $supplierPurchases = $purchaseOrders->groupBy('supplier_id')->map(function ($group, $supplierId) {
                $supplier = $group->first()->supplier;
                $totalPaid = $group->sum(function ($po) {
                    return $po->payments()->where('status', 'completed')->sum('amount');
                });

                $totalPurchases = $group->sum('total_amount');

                return [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplier->name ?? 'Unknown',
                    'po_count' => $group->count(),
                    'total_purchases' => (float) $totalPurchases,
                    'total_paid' => (float) $totalPaid,
                    'total_due' => (float) ($totalPurchases - $totalPaid),
                    'purchase_orders' => $group,
                ];
            })->sortByDesc('total_purchases')->values();

            $suppliers = Supplier::orderBy('name')->get();

            return $this->success([
                'supplier_purchases' => $supplierPurchases,
                'suppliers' => $suppliers,
            ], 'Purchases by supplier retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchases by supplier: ' . $e->getMessage());
        }
    }

    /**
     * Purchase by item report.
     */
    public function purchaseByItem(Request $request)
    {
        try {
            $query = PurchaseOrderItem::with(['purchaseOrder.supplier', 'item'])
                ->whereHas('purchaseOrder', function ($q) {
                    $q->where('status', '!=', 'draft');
                });

            if ($request->filled('date_from')) {
                $query->whereHas('purchaseOrder', function ($q) use ($request) {
                    $q->whereDate('order_date', '>=', $request->date_from);
                });
            }
            if ($request->filled('date_to')) {
                $query->whereHas('purchaseOrder', function ($q) use ($request) {
                    $q->whereDate('order_date', '<=', $request->date_to);
                });
            }
            if ($request->filled('item_id')) {
                $query->where('item_id', $request->item_id);
            }

            $poItems = $query->get();

            $itemPurchases = $poItems->groupBy('item_id')->map(function ($group, $itemId) {
                $item = $group->first()->item;
                $totalQuantity = $group->sum('quantity');
                $totalCost = $group->sum('amount');

                return [
                    'item_id' => $itemId,
                    'item_name' => $item->item_name ?? 'Unknown',
                    'item_number' => $item->item_number ?? '',
                    'total_quantity_purchased' => (float) $totalQuantity,
                    'total_cost' => (float) $totalCost,
                    'average_cost' => $totalQuantity > 0 ? (float) ($totalCost / $totalQuantity) : 0,
                    'po_count' => $group->pluck('purchase_order_id')->unique()->count(),
                    'line_items' => $group,
                ];
            })->sortByDesc('total_cost')->values();

            $items = Item::orderBy('item_name')->get();

            return $this->success([
                'item_purchases' => $itemPurchases,
                'items' => $items,
            ], 'Purchases by item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchases by item: ' . $e->getMessage());
        }
    }

    /**
     * Invoice summary report.
     */
    public function invoiceSummary(Request $request)
    {
        try {
            $query = Invoice::with('customer');

            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $invoices = $query->orderBy('date', 'desc')->get();

            $summary = [
                'total_invoices' => $invoices->count(),
                'total_amount' => (float) $invoices->sum('total_amount'),
                'total_paid' => (float) $invoices->sum('payments_applied'),
                'total_due' => (float) $invoices->sum('balance_due'),
                'by_status' => $invoices->groupBy('status')->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => (float) $group->sum('total_amount'),
                    ];
                }),
            ];

            return $this->success([
                'invoices' => $invoices,
                'summary' => $summary,
            ], 'Invoice summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve invoice summary: ' . $e->getMessage());
        }
    }

    /**
     * Income summary report.
     */
    public function incomeSummary(Request $request)
    {
        try {
            $query = Invoice::with('customer')
                ->where('status', '!=', 'draft');

            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            $invoices = $query->get();

            $monthlyIncome = $invoices->groupBy(function ($invoice) {
                return $invoice->date->format('Y-m');
            })->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'invoice_count' => $group->count(),
                    'total_income' => (float) $group->sum('total_amount'),
                    'total_paid' => (float) $group->sum('payments_applied'),
                ];
            })->sortKeys()->values();

            $summary = [
                'total_income' => (float) $invoices->sum('total_amount'),
                'total_paid' => (float) $invoices->sum('payments_applied'),
                'total_outstanding' => (float) $invoices->sum('balance_due'),
                'invoice_count' => $invoices->count(),
                'average_invoice' => $invoices->count() > 0 ? (float) ($invoices->sum('total_amount') / $invoices->count()) : 0,
            ];

            return $this->success([
                'monthly_income' => $monthlyIncome,
                'summary' => $summary,
            ], 'Income summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve income summary: ' . $e->getMessage());
        }
    }

    /**
     * Item profitability report.
     */
    public function itemProfitability(Request $request)
    {
        try {
            $query = Item::with(['invoiceLineItems.invoice', 'purchaseOrderItems.purchaseOrder']);

            if ($request->filled('item_id')) {
                $query->where('id', $request->item_id);
            }

            $items = $query->get();

            $itemProfits = $items->map(function ($item) {
                $salesItems = InvoiceLineItem::where('item_id', $item->id)
                    ->whereHas('invoice', function ($q) {
                        $q->where('status', '!=', 'draft');
                    })
                    ->get();

                $totalQuantitySold = $salesItems->sum('quantity');
                $totalSalesRevenue = $salesItems->sum('amount');

                $purchaseItems = PurchaseOrderItem::where('item_id', $item->id)
                    ->whereHas('purchaseOrder', function ($q) {
                        $q->where('status', '!=', 'draft');
                    })
                    ->get();

                $totalQuantityPurchased = $purchaseItems->sum('quantity');
                $totalPurchaseCost = $purchaseItems->sum('amount');

                $averageSalePrice = $totalQuantitySold > 0 ? $totalSalesRevenue / $totalQuantitySold : 0;
                $averagePurchasePrice = $totalQuantityPurchased > 0 ? $totalPurchaseCost / $totalQuantityPurchased : 0;
                $profitMargin = $averageSalePrice - $averagePurchasePrice;
                $profitMarginPercent = $averageSalePrice > 0 ? ($profitMargin / $averageSalePrice) * 100 : 0;
                $totalProfit = ($averageSalePrice - $averagePurchasePrice) * $totalQuantitySold;

                return [
                    'item' => $item,
                    'total_quantity_sold' => (float) $totalQuantitySold,
                    'total_quantity_purchased' => (float) $totalQuantityPurchased,
                    'total_sales_revenue' => (float) $totalSalesRevenue,
                    'total_purchase_cost' => (float) $totalPurchaseCost,
                    'average_sale_price' => (float) $averageSalePrice,
                    'average_purchase_price' => (float) $averagePurchasePrice,
                    'profit_margin' => (float) $profitMargin,
                    'profit_margin_percent' => (float) $profitMarginPercent,
                    'total_profit' => (float) $totalProfit,
                ];
            })->filter(function ($item) {
                return $item['total_quantity_sold'] > 0;
            })->sortByDesc('total_profit')->values();

            return $this->success([
                'item_profits' => $itemProfits,
            ], 'Item profitability retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve item profitability: ' . $e->getMessage());
        }
    }

    /**
     * Sales trend report.
     */
    public function salesTrend(Request $request)
    {
        try {
            $query = Invoice::where('status', '!=', 'draft');

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

            $monthlyData = $invoices->groupBy(function ($invoice) {
                return $invoice->date->format('Y-m');
            })->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'month_label' => \Carbon\Carbon::parse($month . '-01')->format('M Y'),
                    'invoice_count' => $group->count(),
                    'total_sales' => (float) $group->sum('total_amount'),
                    'total_paid' => (float) $group->sum('payments_applied'),
                ];
            })->sortKeys()->values();

            return $this->success([
                'monthly_data' => $monthlyData,
            ], 'Sales trend retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve sales trend: ' . $e->getMessage());
        }
    }

    /**
     * Placeholder export endpoint.
     */
    public function export()
    {
        return $this->error('Export functionality coming soon.', null, 501);
    }
}

