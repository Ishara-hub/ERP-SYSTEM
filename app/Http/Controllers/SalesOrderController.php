<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salesOrders = SalesOrder::with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sales-orders.index', compact('salesOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $items = Item::orderBy('item_name')->get();
        
        return view('sales-orders.create', compact('customers', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'line_items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $salesOrder = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'status' => 'pending',
                'payment_terms' => $request->payment_terms,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => auth()->user()->name ?? 'System',
            ]);

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->line_items as $lineItem) {
                $amount = $lineItem['quantity'] * $lineItem['unit_price'];
                $taxAmount = $amount * ($lineItem['tax_rate'] ?? 0) / 100;
                $discountAmount = $amount * ($lineItem['discount_rate'] ?? 0) / 100;

                $salesOrder->lineItems()->create([
                    'item_id' => $lineItem['item_id'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'amount' => $amount,
                    'tax_rate' => $lineItem['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $lineItem['discount_rate'] ?? 0,
                    'discount_amount' => $discountAmount,
                ]);

                $subtotal += $amount;
                $totalTax += $taxAmount;
                $totalDiscount += $discountAmount;
            }

            $totalAmount = $subtotal + $totalTax - $totalDiscount;

            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create sales order: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'lineItems.item']);
        
        return view('sales-orders.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        $customers = Customer::orderBy('name')->get();
        $items = Item::orderBy('item_name')->get();
        $salesOrder->load('lineItems.item');
        
        return view('sales-orders.edit', compact('salesOrder', 'customers', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'line_items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $salesOrder->update([
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'status' => $request->status,
                'payment_terms' => $request->payment_terms,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
            ]);

            // Delete existing line items
            $salesOrder->lineItems()->delete();

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->line_items as $lineItem) {
                $amount = $lineItem['quantity'] * $lineItem['unit_price'];
                $taxAmount = $amount * ($lineItem['tax_rate'] ?? 0) / 100;
                $discountAmount = $amount * ($lineItem['discount_rate'] ?? 0) / 100;

                $salesOrder->lineItems()->create([
                    'item_id' => $lineItem['item_id'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'amount' => $amount,
                    'tax_rate' => $lineItem['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $lineItem['discount_rate'] ?? 0,
                    'discount_amount' => $discountAmount,
                ]);

                $subtotal += $amount;
                $totalTax += $taxAmount;
                $totalDiscount += $discountAmount;
            }

            $totalAmount = $subtotal + $totalTax - $totalDiscount;

            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update sales order: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales order deleted successfully.');
    }
}
