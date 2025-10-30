<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        return view('invoices.index', [
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
        
        return view('invoices.create', [
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
            'due_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:255',
            'terms' => 'nullable|string|max:255',
            'rep' => 'nullable|string|max:255',
            'ship_date' => 'nullable|date',
            'via' => 'nullable|string|max:255',
            'fob' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'customer_message' => 'nullable|string',
            'memo' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
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
                'due_date' => $request->due_date,
                'po_number' => $request->po_number,
                'terms' => $request->terms,
                'rep' => $request->rep,
                'ship_date' => $request->ship_date,
                'via' => $request->via,
                'fob' => $request->fob,
                'template' => $request->template ?? 'default',
                'billing_address' => $request->billing_address,
                'shipping_address' => $request->shipping_address,
                'customer_message' => $request->customer_message,
                'memo' => $request->memo,
                'discount_amount' => $request->discount_amount ?? 0,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'is_online_payment_enabled' => $request->is_online_payment_enabled ?? false,
            ]);

            $totalCostOfGoodsSold = 0;

            // Create line items and handle inventory
            foreach ($request->line_items as $lineItemData) {
                $lineItem = InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $lineItemData['item_id'] ?? null,
                    'description' => $lineItemData['description'],
                    'quantity' => $lineItemData['quantity'],
                    'unit_price' => $lineItemData['unit_price'],
                    'tax_rate' => $lineItemData['tax_rate'] ?? 0,
                ]);

                // Handle inventory deduction and COGS calculation
                if ($lineItem->item_id) {
                    $item = Item::find($lineItem->item_id);
                    
                    if ($item && $item->isInventoryItem()) {
                        // Check if sufficient inventory
                        if ($item->on_hand < $lineItem->quantity) {
                            throw new \Exception("Insufficient inventory for item: {$item->item_name}. Available: {$item->on_hand}, Required: {$lineItem->quantity}");
                        }

                        // Deduct quantity from inventory
                        $item->on_hand -= $lineItem->quantity;
                        $item->save();

                        // Calculate cost for COGS
                        $lineItemCost = $item->cost * $lineItem->quantity;
                        $totalCostOfGoodsSold += $lineItemCost;

                        // Record stock movement
                        StockMovement::create([
                            'item_id' => $item->id,
                            'quantity' => -$lineItem->quantity, // Negative for deduction
                            'type' => 'sale',
                            'source_document' => 'invoice',
                            'source_document_id' => $invoice->id,
                            'transaction_date' => $invoice->date,
                            'description' => "Invoice #{$invoice->invoice_no} - Sale to {$invoice->customer->name}",
                        ]);
                    }
                }
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Get required accounts
            $accountsReceivable = Account::where('account_name', 'Accounts Receivable')->first();
            $salesRevenue = Account::where('account_name', 'Sales Revenue')->first();
            $cogsAccount = Account::where('account_name', 'Cost of Goods Sold')->first();
            $inventoryAccount = Account::where('account_name', 'Inventory')->first();

            if (!$accountsReceivable || !$salesRevenue || !$cogsAccount || !$inventoryAccount) {
                throw new \Exception('Required accounts not found. Please run the ChartOfAccountsSeeder.');
            }

            // Create Journal Entries (Double-Entry Accounting)
            
            // Entry 1: Record the Sale and Receivable
            // Debit Accounts Receivable
            Transaction::create([
                'account_id' => $accountsReceivable->id,
                'type' => 'debit',
                'amount' => $invoice->total_amount,
                'description' => "Invoice #{$invoice->invoice_no} - Sale to {$invoice->customer->name}",
                'transaction_date' => $invoice->date,
            ]);
            
            // Credit Sales Revenue
            Transaction::create([
                'account_id' => $salesRevenue->id,
                'type' => 'credit',
                'amount' => $invoice->total_amount,
                'description' => "Invoice #{$invoice->invoice_no} - Sales Revenue from {$invoice->customer->name}",
                'transaction_date' => $invoice->date,
            ]);

            // Entry 2: Record Cost of Goods Sold and Inventory Reduction
            if ($totalCostOfGoodsSold > 0) {
                // Debit Cost of Goods Sold
                Transaction::create([
                    'account_id' => $cogsAccount->id,
                    'type' => 'debit',
                    'amount' => $totalCostOfGoodsSold,
                    'description' => "Invoice #{$invoice->invoice_no} - Cost of Goods Sold",
                    'transaction_date' => $invoice->date,
                ]);
                
                // Credit Inventory
                Transaction::create([
                    'account_id' => $inventoryAccount->id,
                    'type' => 'credit',
                    'amount' => $totalCostOfGoodsSold,
                    'description' => "Invoice #{$invoice->invoice_no} - Inventory reduction",
                    'transaction_date' => $invoice->date,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.web.show', $invoice)
                ->with('success', 'Invoice created successfully with proper accounting entries.');
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
        
        return view('invoices.show', [
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
        
        return view('invoices.edit', [
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
        
        return view('invoices.print', [
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