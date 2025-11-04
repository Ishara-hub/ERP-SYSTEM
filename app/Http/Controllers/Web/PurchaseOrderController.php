<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request): View
    {
        try {
            $query = PurchaseOrder::with(['supplier', 'items']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                          $supplierQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('company_name', 'like', "%{$search}%");
                      });
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Supplier filter
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->get('supplier_id'));
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->where('order_date', '>=', $request->get('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->where('order_date', '<=', $request->get('date_to'));
            }

            $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(20);
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
            $statuses = [
                'draft' => 'Draft',
                'sent' => 'Sent',
                'confirmed' => 'Confirmed',
                'partial' => 'Partially Received',
                'received' => 'Fully Received',
                'cancelled' => 'Cancelled'
            ];

            return view('purchase-orders.index', compact('purchaseOrders', 'suppliers', 'statuses'));
        } catch (\Exception $e) {
            \Log::error('Error in PurchaseOrderController@index: ' . $e->getMessage());
            return view('purchase-orders.index', ['purchaseOrders' => collect()]);
        }
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('item_name')->get();
        
        return view('purchase-orders.create', compact('suppliers', 'items'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'terms' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.unit_of_measure' => 'nullable|string|max:20',
            'items.*.notes' => 'nullable|string',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'terms' => $request->terms,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Create purchase order items
            foreach ($request->items as $itemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $itemData['item_id'],
                    'description' => $itemData['description'] ?? '',
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'unit_of_measure' => $itemData['unit_of_measure'] ?? '',
                    'notes' => $itemData['notes'] ?? '',
                ]);
            }

            // Recalculate totals
            $purchaseOrder->load('items');
            $purchaseOrder->calculateTotals();
            $purchaseOrder->save();

            DB::commit();

            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating purchase order: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create purchase order.');
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit(PurchaseOrder $purchaseOrder): View
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $purchaseOrder->load(['items']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('item_name')->get();
        
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'terms' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.unit_of_measure' => 'nullable|string|max:20',
            'items.*.notes' => 'nullable|string',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'terms' => $request->terms,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
            ]);

            // Delete existing items and create new ones
            $purchaseOrder->items()->delete();
            foreach ($request->items as $itemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $itemData['item_id'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'unit_of_measure' => $itemData['unit_of_measure'],
                    'notes' => $itemData['notes'],
                ]);
            }

            // Recalculate totals
            $purchaseOrder->load('items');
            $purchaseOrder->calculateTotals();
            $purchaseOrder->save();

            DB::commit();

            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating purchase order: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update purchase order.');
        }
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.web.index')
                ->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.web.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:draft,sent,confirmed,partial,received,cancelled'
        ]);

        $purchaseOrder->update(['status' => $request->status]);

        $statusLabels = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'confirmed' => 'Confirmed',
            'partial' => 'Partially Received',
            'received' => 'Fully Received',
            'cancelled' => 'Cancelled'
        ];

        return redirect()->route('purchase-orders.web.show', $purchaseOrder)
            ->with('success', "Purchase order status updated to {$statusLabels[$request->status]}.");
    }

    /**
     * Show receive inventory form
     */
    public function receive(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        
        return view('purchase-orders.receive', compact('purchaseOrder'));
    }

    /**
     * Receive inventory for purchase order
     */
    public function receiveInventory(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])) {
            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('error', 'Purchase order must be sent, confirmed, or partially received to receive inventory.');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.received_quantity' => 'required|numeric|min:0',
            'receive_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $allFullyReceived = true;
            $anyReceived = false;
            $totalReceivedValue = 0;

            foreach ($request->received_items as $receivedItem) {
                $poItem = PurchaseOrderItem::find($receivedItem['item_id']);
                if ($poItem && $poItem->purchase_order_id === $purchaseOrder->id) {
                    $receivedQuantity = $receivedItem['received_quantity'];
                    
                    if ($receivedQuantity <= 0) {
                        continue; // Skip items with 0 quantity
                    }
                    
                    $newReceivedQuantity = $poItem->received_quantity + $receivedQuantity;
                    
                    if ($newReceivedQuantity > $poItem->quantity) {
                        return back()->with('error', "Received quantity cannot exceed ordered quantity for item: {$poItem->item->item_name}");
                    }

                    // Update purchase order item
                    $poItem->update(['received_quantity' => $newReceivedQuantity]);

                    // Update item inventory
                    $item = $poItem->item;
                    if ($item && in_array($item->item_type, [Item::INVENTORY_PART, Item::INVENTORY_ASSEMBLY])) {
                        $newOnHand = $item->on_hand + $receivedQuantity;
                        $newTotalValue = $item->total_value + ($receivedQuantity * $poItem->unit_price);
                        
                        $item->update([
                            'on_hand' => $newOnHand,
                            'total_value' => $newTotalValue,
                            'as_of_date' => $request->receive_date
                        ]);

                        // Create stock movement record
                        \App\Models\StockMovement::create([
                            'item_id' => $item->id,
                            'type' => 'in',
                            'quantity' => $receivedQuantity,
                            'reference_type' => 'purchase',
                            'reference_id' => $purchaseOrder->id,
                        ]);
                    }

                    $totalReceivedValue += $receivedQuantity * $poItem->unit_price;

                    if ($newReceivedQuantity < $poItem->quantity) {
                        $allFullyReceived = false;
                    }
                    if ($newReceivedQuantity > 0) {
                        $anyReceived = true;
                    }
                }
            }

            // Update purchase order status
            if ($allFullyReceived) {
                $purchaseOrder->update([
                    'status' => 'received',
                    'actual_delivery_date' => $request->receive_date
                ]);
            } elseif ($anyReceived) {
                $purchaseOrder->update(['status' => 'partial']);
            }

            // Log the receipt
            \Log::info("Inventory received for PO {$purchaseOrder->po_number}", [
                'total_value' => $totalReceivedValue,
                'receive_date' => $request->receive_date,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('purchase-orders.web.show', $purchaseOrder)
                ->with('success', 'Inventory received successfully and added to stock.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error receiving inventory: ' . $e->getMessage());
            return back()->with('error', 'Failed to receive inventory: ' . $e->getMessage());
        }
    }

    /**
     * Print purchase order
     */
    public function print(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        return view('purchase-orders.print', compact('purchaseOrder'));
    }
}
