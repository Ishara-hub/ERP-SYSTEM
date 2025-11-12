<?php

namespace App\Http\Controllers\Api\PurchaseOrders;

use App\Http\Controllers\Api\ApiController;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;

class PurchaseOrdersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = PurchaseOrder::with(['supplier', 'items']);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSorts = ['po_number', 'order_date', 'total_amount', 'status', 'created_at'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'created_at';
            }
            $query->orderBy($sortBy, $sortDirection);

            $purchaseOrders = $query->paginate((int) $request->get('per_page', 15))->withQueryString();

            return $this->success([
                'purchase_orders' => $purchaseOrders,
                'filters' => $request->only(['search', 'status', 'supplier_id', 'date_from', 'date_to', 'sort_by', 'sort_direction']),
                'stats' => [
                    'total' => PurchaseOrder::count(),
                    'draft' => PurchaseOrder::where('status', 'draft')->count(),
                    'open' => PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])->count(),
                    'received' => PurchaseOrder::where('status', 'received')->count(),
                ],
            ], 'Purchase orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase orders: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date',
                'shipping_address' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'terms' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'shipping_amount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'created_by' => 'nullable|string|max:255',
                'line_items' => 'required|array|min:1',
                'line_items.*.item_id' => 'nullable|exists:items,id',
                'line_items.*.description' => 'required|string|max:255',
                'line_items.*.quantity' => 'required|numeric|min:0.01',
                'line_items.*.unit_price' => 'required|numeric|min:0',
                'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'line_items.*.unit_of_measure' => 'nullable|string|max:255',
                'line_items.*.notes' => 'nullable|string',
            ]);

            DB::beginTransaction();
            
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'terms' => $validated['terms'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'shipping_amount' => $validated['shipping_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'created_by' => $validated['created_by'],
                'status' => 'draft',
            ]);

            foreach ($validated['line_items'] as $lineItemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                    'unit_of_measure' => $lineItemData['unit_of_measure'] ?? null,
                    'notes' => $lineItemData['notes'] ?? null,
                ]);
            }

            $purchaseOrder->calculateTotals();
            $purchaseOrder->save();

            DB::commit();

            return $this->success($purchaseOrder->fresh(['supplier', 'items.item']), 'Purchase order created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to create purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->load(['supplier', 'items.item']);
            return $this->success($purchaseOrder, 'Purchase order retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date',
                'shipping_address' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'terms' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'shipping_amount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'line_items' => 'required|array|min:1',
                'line_items.*.item_id' => 'nullable|exists:items,id',
                'line_items.*.description' => 'required|string|max:255',
                'line_items.*.quantity' => 'required|numeric|min:0.01',
                'line_items.*.unit_price' => 'required|numeric|min:0',
                'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'line_items.*.unit_of_measure' => 'nullable|string|max:255',
                'line_items.*.notes' => 'nullable|string',
            ]);

            DB::beginTransaction();
            
            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'terms' => $validated['terms'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'shipping_amount' => $validated['shipping_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);

            // Delete existing line items
            $purchaseOrder->items()->delete();

            // Create new line items
            foreach ($validated['line_items'] as $lineItemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                    'unit_of_measure' => $lineItemData['unit_of_measure'] ?? null,
                    'notes' => $lineItemData['notes'] ?? null,
                ]);
            }

            // Calculate totals
            $purchaseOrder->calculateTotals();
            $purchaseOrder->save();

            DB::commit();

            $purchaseOrder->load(['supplier', 'items.item']);

            return $this->success($purchaseOrder, 'Purchase order updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to update purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            // Only allow deletion of draft purchase orders
            if ($purchaseOrder->status !== 'draft') {
                return $this->error('Only draft purchase orders can be deleted.', null, 403);
            }

            $purchaseOrder->delete();

            return $this->success(null, 'Purchase order deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:draft,sent,confirmed,partial,received,cancelled',
                'approved_by' => 'nullable|string|max:255',
            ]);

            $purchaseOrder->update([
                'status' => $validated['status'],
                'approved_by' => $validated['approved_by'],
                'approved_at' => $validated['status'] === 'confirmed' ? now() : null,
            ]);

            return $this->success($purchaseOrder, 'Purchase order status updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update purchase order status: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve options for purchase order forms.
     */
    public function options()
    {
        try {
            return $this->success([
                'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'items' => Item::where('is_active', true)->orderBy('item_name')->get(['id', 'item_name', 'sales_price', 'item_type']),
                'statuses' => [
                    'draft',
                    'sent',
                    'confirmed',
                    'partial',
                    'received',
                    'cancelled',
                ],
            ], 'Purchase order options retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase order options: ' . $e->getMessage());
        }
    }

    /**
     * Receive inventory for a purchase order.
     */
    public function receiveInventory(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'], true)) {
            return $this->error('Purchase order must be sent, confirmed, or partially received to receive inventory.', null, 403);
        }

        $validated = $request->validate([
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

            foreach ($validated['received_items'] as $receivedItem) {
                $poItem = PurchaseOrderItem::find($receivedItem['item_id']);
                if (!$poItem || $poItem->purchase_order_id !== $purchaseOrder->id) {
                    continue;
                }

                $receivedQuantity = $receivedItem['received_quantity'];
                if ($receivedQuantity <= 0) {
                    continue;
                }

                $newReceivedQuantity = $poItem->received_quantity + $receivedQuantity;
                if ($newReceivedQuantity > $poItem->quantity) {
                    return $this->validationError([
                        "received_items.{$poItem->id}.received_quantity" => ['Received quantity cannot exceed ordered quantity.'],
                    ]);
                }

                $poItem->update(['received_quantity' => $newReceivedQuantity]);

                $item = $poItem->item;
                if ($item && $item->isInventoryItem()) {
                    InventoryService::recordPurchase(
                        $item,
                        $receivedQuantity,
                        $poItem->unit_price,
                        'purchase_order',
                        $purchaseOrder->id,
                        $validated['receive_date'],
                        "PO {$purchaseOrder->po_number} receipt"
                    );
                }

                if ($newReceivedQuantity < $poItem->quantity) {
                    $allFullyReceived = false;
                }
                if ($newReceivedQuantity > 0) {
                    $anyReceived = true;
                }
            }

            if ($allFullyReceived) {
                $purchaseOrder->update([
                    'status' => 'received',
                    'actual_delivery_date' => $validated['receive_date'],
                ]);
            } elseif ($anyReceived) {
                $purchaseOrder->update(['status' => 'partial']);
            }

            DB::commit();

            return $this->success($purchaseOrder->fresh(['items.item']), 'Inventory received successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to receive inventory: ' . $e->getMessage());
        }
    }

    /**
     * Print purchase order
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->load(['supplier', 'items.item']);
            return $this->success($purchaseOrder, 'Purchase order print data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase order print data: ' . $e->getMessage());
        }
    }
}
