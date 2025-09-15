<?php

namespace App\Http\Controllers\PurchaseOrders;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        return Inertia::render('purchase-orders/index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name', 'email']);
        $items = Item::where('is_active', true)->orderBy('item_name')->get(['id', 'item_name', 'cost', 'item_type']);
        
        return Inertia::render('purchase-orders/create', [
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
        try {
            // Create purchase order
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
                'created_by' => $request->created_by,
                'status' => 'draft',
            ]);

            // Create line items
            foreach ($request->line_items as $lineItemData) {
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

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create purchase order: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        
        return Inertia::render('purchase-orders/show', [
            'purchaseOrder' => $purchaseOrder
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name', 'email']);
        $items = Item::where('is_active', true)->orderBy('item_name')->get(['id', 'item_name', 'cost', 'item_type']);
        
        return Inertia::render('purchase-orders/edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
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
        try {
            // Update purchase order
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

            // Delete existing line items
            $purchaseOrder->items()->delete();

            // Create new line items
            foreach ($request->line_items as $lineItemData) {
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

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update purchase order: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Only allow deletion of draft purchase orders
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,confirmed,partial,received,cancelled',
            'approved_by' => 'nullable|string|max:255',
        ]);

        $purchaseOrder->update([
            'status' => $request->status,
            'approved_by' => $request->approved_by,
            'approved_at' => $request->status === 'confirmed' ? now() : null,
        ]);

        return back()->with('success', 'Purchase order status updated successfully.');
    }

    /**
     * Print purchase order
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        
        return Inertia::render('purchase-orders/print', [
            'purchaseOrder' => $purchaseOrder
        ]);
    }
}