<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'lineItems']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($request) {
                      $customerQuery->where('name', 'like', '%' . $request->search . '%')
                                   ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['invoice_no', 'date', 'total_amount', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $invoices = $query->paginate(15);

        return Inertia::render('invoices/index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email', 'address']);
        $items = Item::where('is_active', true)->orderBy('item_name')->get(['id', 'item_name', 'sales_price', 'item_type']);
        
        return Inertia::render('invoices/create', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'ship_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:255',
            'terms' => 'nullable|string|max:255',
            'rep' => 'nullable|string|max:255',
            'via' => 'nullable|string|max:255',
            'fob' => 'nullable|string|max:255',
            'customer_message' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'template' => 'nullable|string|max:255',
            'is_online_payment_enabled' => 'boolean',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'nullable|exists:items,id',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'ship_date' => $request->ship_date,
                'po_number' => $request->po_number,
                'terms' => $request->terms,
                'rep' => $request->rep,
                'via' => $request->via,
                'fob' => $request->fob,
                'customer_message' => $request->customer_message,
                'memo' => $request->memo,
                'billing_address' => $request->billing_address,
                'shipping_address' => $request->shipping_address,
                'template' => $request->template ?? 'default',
                'is_online_payment_enabled' => $request->is_online_payment_enabled ?? false,
            ]);

            // Create line items
            foreach ($request->line_items as $lineItemData) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'lineItems.item', 'payments']);
        
        return Inertia::render('invoices/show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'lineItems.item']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email', 'address']);
        $items = Item::where('is_active', true)->orderBy('item_name')->get(['id', 'item_name', 'sales_price', 'item_type']);
        
        return Inertia::render('invoices/edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'items' => $items,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'ship_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:255',
            'terms' => 'nullable|string|max:255',
            'rep' => 'nullable|string|max:255',
            'via' => 'nullable|string|max:255',
            'fob' => 'nullable|string|max:255',
            'customer_message' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'template' => 'nullable|string|max:255',
            'is_online_payment_enabled' => 'boolean',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'nullable|exists:items,id',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Update invoice
            $invoice->update([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'ship_date' => $request->ship_date,
                'po_number' => $request->po_number,
                'terms' => $request->terms,
                'rep' => $request->rep,
                'via' => $request->via,
                'fob' => $request->fob,
                'customer_message' => $request->customer_message,
                'memo' => $request->memo,
                'billing_address' => $request->billing_address,
                'shipping_address' => $request->shipping_address,
                'template' => $request->template ?? 'default',
                'is_online_payment_enabled' => $request->is_online_payment_enabled ?? false,
            ]);

            // Delete existing line items
            $invoice->lineItems()->delete();

            // Create new line items
            foreach ($request->line_items as $lineItemData) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return redirect()->route('invoices.index')
                ->with('error', 'Cannot delete invoice with existing payments.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'lineItems.item']);
        
        return Inertia::render('invoices/print', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Email invoice
     */
    public function email(Invoice $invoice)
    {
        // TODO: Implement email functionality
        return back()->with('success', 'Invoice email functionality will be implemented soon.');
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'payments_applied' => $invoice->total_amount,
            'balance_due' => 0,
        ]);

        return back()->with('success', 'Invoice marked as paid.');
    }
}