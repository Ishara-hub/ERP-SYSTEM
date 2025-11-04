<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Display inventory listing.
     */
    public function index(Request $request)
    {
        $query = Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
            ->with('cogsAccount', 'assetAccount');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%");
            });
        }

        // Low stock filter
        if ($request->filled('low_stock') && $request->low_stock) {
            $query->whereColumn('on_hand', '<=', 'reorder_point');
        }

        // Out of stock filter
        if ($request->filled('out_of_stock') && $request->out_of_stock) {
            $query->where('on_hand', '<=', 0);
        }

        $items = $query->orderBy('item_name')->paginate(20);
        
        // Calculate summary statistics
        $summary = [
            'total_items' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])->count(),
            'total_value' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])->sum('total_value'),
            'low_stock_count' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                ->whereColumn('on_hand', '<=', 'reorder_point')->count(),
            'out_of_stock_count' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                ->where('on_hand', '<=', 0)->count(),
        ];

        return view('inventory.index', compact('items', 'summary'));
    }

    /**
     * Show inventory movements history.
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with('item');

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $movements = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $items = Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
            ->orderBy('item_name')
            ->get();

        $movementTypes = ['purchase', 'sale', 'adjustment'];

        return view('inventory.movements', compact('movements', 'items', 'movementTypes'));
    }

    /**
     * Show adjustment form.
     */
    public function adjustmentForm()
    {
        $items = Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
            ->orderBy('item_name')
            ->get();

        return view('inventory.adjust', compact('items'));
    }

    /**
     * Process inventory adjustment.
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($request->item_id);
            
            // Get current on-hand before adjustment
            $currentOnHand = $item->on_hand;
            $newOnHand = $currentOnHand + $request->quantity;
            
            // Check if adjustment will result in negative stock
            if ($newOnHand < 0) {
                return back()->withErrors(['quantity' => 'Insufficient stock for this adjustment. Current stock: ' . $currentOnHand]);
            }

            $description = $request->reason 
                ? "Adjustment: {$request->reason}"
                : ($request->quantity > 0 ? "Inventory adjustment - Increase" : "Inventory adjustment - Decrease");

            InventoryService::recordAdjustment(
                $item,
                $request->quantity,
                $request->reason,
                $request->date,
                $description
            );

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to adjust inventory: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show detailed view of an item's inventory.
     */
    public function show(Item $item)
    {
        $item->load(['cogsAccount', 'assetAccount', 'preferredVendor']);
        
        $movements = StockMovement::where('item_id', $item->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('inventory.show', compact('item', 'movements'));
    }

    /**
     * Export inventory report.
     */
    public function export(Request $request)
    {
        // Implementation for CSV/Excel export
        // This can be added later if needed
        return back()->with('info', 'Export functionality coming soon.');
    }
}
