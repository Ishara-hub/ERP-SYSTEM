<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Api\ApiController;
use App\Models\Item;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends ApiController
{
    /**
     * Display inventory items with filters and summary data.
     */
    public function index(Request $request)
    {
        try {
            $query = Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                ->with(['cogsAccount', 'assetAccount']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                      ->orWhere('item_number', 'like', "%{$search}%");
                });
            }

            if ($request->boolean('low_stock')) {
                $query->whereColumn('on_hand', '<=', 'reorder_point');
            }

            if ($request->boolean('out_of_stock')) {
                $query->where('on_hand', '<=', 0);
            }

            $items = $query->orderBy('item_name')->paginate(20)->withQueryString();

            $summary = [
                'total_items' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])->count(),
                'total_value' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])->sum('total_value'),
                'low_stock_count' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                    ->whereColumn('on_hand', '<=', 'reorder_point')->count(),
                'out_of_stock_count' => Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                    ->where('on_hand', '<=', 0)->count(),
            ];

            $data = [
                'items' => $items,
                'summary' => $summary,
                'filters' => $request->only(['search', 'low_stock', 'out_of_stock']),
            ];

            return $this->success($data, 'Inventory items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve inventory items: ' . $e->getMessage());
        }
    }

    /**
     * Show inventory movements history.
     */
    public function movements(Request $request)
    {
        try {
            $query = StockMovement::with('item');

            if ($request->filled('item_id')) {
                $query->where('item_id', $request->item_id);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('transaction_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('transaction_date', '<=', $request->date_to);
            }

            $movements = $query->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(50)
                ->withQueryString();

            $items = Item::whereIn('item_type', [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])
                ->orderBy('item_name')
                ->get();

            $movementTypes = ['purchase', 'sale', 'adjustment'];

            $data = [
                'movements' => $movements,
                'items' => $items,
                'movement_types' => $movementTypes,
                'filters' => $request->only(['item_id', 'type', 'date_from', 'date_to']),
            ];

            return $this->success($data, 'Inventory movements retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve inventory movements: ' . $e->getMessage());
        }
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

            $currentOnHand = $item->on_hand;
            $newOnHand = $currentOnHand + $request->quantity;

            if ($newOnHand < 0) {
                return $this->error('Insufficient stock for this adjustment. Current stock: ' . $currentOnHand, null, 422);
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

            $item->refresh();

            return $this->success($item, 'Inventory adjusted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to adjust inventory: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed view of an item's inventory.
     */
    public function show(Item $item)
    {
        try {
            $item->load(['cogsAccount', 'assetAccount', 'preferredVendor']);

            $movements = StockMovement::where('item_id', $item->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $data = [
                'item' => $item,
                'movements' => $movements,
            ];

            return $this->success($data, 'Inventory item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Placeholder for export functionality.
     */
    public function export()
    {
        return $this->error('Export functionality coming soon.', null, 501);
    }
}

