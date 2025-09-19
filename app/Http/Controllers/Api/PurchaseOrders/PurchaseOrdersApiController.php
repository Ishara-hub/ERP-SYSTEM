<?php

namespace App\Http\Controllers\Api\PurchaseOrders;

use App\Http\Controllers\Api\ApiController;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrdersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = PurchaseOrder::with(['supplier', 'items']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('po_number', 'like', '%' . $request->search . '%')
                      ->orWhere('reference', 'like', '%' . $request->search . '%')
                      ->orWhereHas('supplier', function ($supplierQuery) use ($request) {
                          $supplierQuery->where('name', 'like', '%' . $request->search . '%')
                                       ->orWhere('company_name', 'like', '%' . $request->search . '%');
                      });
                });
            }

            // Status filter
            if ($request->has('status') && $request->status && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['po_number', 'order_date', 'total_amount', 'status', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $purchaseOrders = $query->paginate(15);

            $data = [
                'purchaseOrders' => $purchaseOrders,
                'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
            ];

            return $this->success($data, 'Purchase orders retrieved successfully');
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
            
            // Create purchase order
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

            // Create line items
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

            return $this->success($purchaseOrder, 'Purchase order created successfully', 201);
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
