<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\InvoiceLineItem;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportsController extends Controller
{
    /**
     * 1. Inventory Valuation Summary
     */
    public function valuationSummary(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        $items = Item::inventoryItems()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get();

        $valuationData = $items->map(function ($item) use ($dateAsOf) {
            // Calculate stock on hand as of date
            $stockIn = StockMovement::where('item_id', $item->id)
                ->where('transaction_date', '<=', $dateAsOf)
                ->where('quantity', '>', 0)
                ->sum('quantity');
            
            $stockOut = StockMovement::where('item_id', $item->id)
                ->where('transaction_date', '<=', $dateAsOf)
                ->where('quantity', '<', 0)
                ->sum('quantity');

            $qtyOnHand = $stockIn + $stockOut; // stockOut is negative

            // Weighted Average Cost (Simplified: total value / qty)
            // In a real scenario, we'd track cost per batch
            $avgCost = $item->cost; 
            $totalValue = $qtyOnHand * $avgCost;

            return [
                'item_name' => $item->item_name,
                'item_number' => $item->item_number,
                'qty_on_hand' => $qtyOnHand,
                'avg_cost' => $avgCost,
                'inventory_value' => $totalValue,
            ];
        })->filter(fn($i) => $i['qty_on_hand'] != 0);

        return view('reports.inventory.valuation-summary', compact('valuationData', 'dateAsOf'));
    }

    /**
     * 2. Inventory Valuation Detail
     */
    public function valuationDetail(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));
        $itemId = $request->get('item_id');

        $query = StockMovement::with('item')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $movements = $query->orderBy('transaction_date')->orderBy('id')->get();
        $items = Item::inventoryItems()->active()->get();

        return view('reports.inventory.valuation-detail', compact('movements', 'items', 'dateFrom', 'dateTo', 'itemId'));
    }

    /**
     * 3. Product / Service List
     */
    public function productServiceList()
    {
        $items = Item::with('parent')
            ->orderBy('item_type')
            ->orderBy('item_name')
            ->get();

        return view('reports.inventory.product-service-list', compact('items'));
    }

    /**
     * 4. Stock on Hand
     */
    public function stockOnHand(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        $items = Item::inventoryItems()->active()->get();

        $stockData = $items->map(function ($item) use ($dateAsOf) {
            $movements = StockMovement::where('item_id', $item->id)
                ->where('transaction_date', '<=', $dateAsOf)
                ->get();

            $openingStock = 0; // Simplified
            $purchasedQty = $movements->where('type', 'purchase')->sum('quantity');
            $soldQty = abs($movements->where('type', 'sale')->sum('quantity'));
            $adjustments = $movements->where('type', 'adjustment')->sum('quantity');
            
            $currentStock = $openingStock + $purchasedQty - $soldQty + $adjustments;

            return [
                'item_name' => $item->item_name,
                'opening_stock' => $openingStock,
                'purchased_qty' => $purchasedQty,
                'sold_qty' => $soldQty,
                'adjustments' => $adjustments,
                'current_stock' => $currentStock,
                'reorder_level' => $item->reorder_point,
                'status' => $currentStock <= $item->reorder_point ? 'Low Stock' : 'In Stock',
            ];
        });

        return view('reports.inventory.stock-on-hand', compact('stockData', 'dateAsOf'));
    }

    /**
     * 5. Stock Movement Report
     */
    public function stockMovement(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));
        $itemId = $request->get('item_id');

        $query = StockMovement::with('item')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $movements = $query->orderBy('transaction_date', 'desc')->get();
        $items = Item::inventoryItems()->active()->get();

        return view('reports.inventory.stock-movement', compact('movements', 'items', 'dateFrom', 'dateTo', 'itemId'));
    }

    /**
     * 6. Low Stock Report
     */
    public function lowStock()
    {
        $items = Item::inventoryItems()
            ->active()
            ->whereColumn('on_hand', '<=', 'reorder_point')
            ->with('preferredVendor')
            ->get();

        return view('reports.inventory.low-stock', compact('items'));
    }

    /**
     * 7. Sales by Item
     */
    public function salesByItem(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        $sales = InvoiceLineItem::with(['invoice.customer', 'item'])
            ->whereHas('invoice', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('date', [$dateFrom, $dateTo])
                  ->where('status', '!=', 'draft');
            })
            ->get()
            ->map(function ($line) {
                $cost = optional($line->item)->cost ?? 0;
                $salesAmount = $line->amount;
                $totalCost = $line->quantity * $cost;
                
                return [
                    'item_name' => optional($line->item)->item_name ?? $line->description,
                    'invoice_no' => $line->invoice->invoice_no,
                    'date' => $line->invoice->date,
                    'customer' => optional($line->invoice->customer)->name,
                    'qty_sold' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'sales_amount' => $salesAmount,
                    'cost' => $cost,
                    'gross_profit' => $salesAmount - $totalCost,
                ];
            });

        return view('reports.inventory.sales-by-item', compact('sales', 'dateFrom', 'dateTo'));
    }

    /**
     * 8. Purchases by Item
     */
    public function purchasesByItem(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        $purchases = BillItem::with(['bill.supplier', 'item'])
            ->whereHas('bill', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('bill_date', [$dateFrom, $dateTo]);
            })
            ->get()
            ->map(function ($line) {
                return [
                    'item_name' => optional($line->item)->item_name ?? $line->description,
                    'bill_no' => $line->bill->bill_number,
                    'date' => $line->bill->bill_date,
                    'vendor' => optional($line->bill->supplier)->name,
                    'qty_purchased' => $line->quantity,
                    'unit_cost' => $line->unit_price,
                    'purchase_amount' => $line->amount,
                    'tax' => $line->tax_amount,
                    'total_amount' => $line->amount + $line->tax_amount,
                ];
            });

        return view('reports.inventory.purchases-by-item', compact('purchases', 'dateFrom', 'dateTo'));
    }
}

