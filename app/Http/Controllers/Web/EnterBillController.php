<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterBillController extends Controller
{
    /**
     * Display a listing of bills.
     */
    public function index(Request $request)
    {
        $query = Bill::with(['supplier', 'liabilityAccount', 'items.expenseAccount']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('bill_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($request) {
                      $supplierQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Supplier filter
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('bill_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('bill_date', '<=', $request->date_to);
        }

        $bills = $query->orderBy('bill_date', 'desc')->paginate(20);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();

        return view('bills.enter-bill.index', [
            'bills' => $bills,
            'suppliers' => $suppliers,
            'liabilityAccounts' => $liabilityAccounts,
            'filters' => $request->only(['search', 'status', 'supplier_id', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new bill.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();
        $expenseAccounts = Account::where('account_type', 'Expense')->orderBy('account_name')->get();
        $accounts = Account::whereIn('account_type', ['Liability', 'Expense'])->orderBy('account_name')->get();
        $items = \App\Models\Item::where('is_active', true)->orderBy('item_name')->get();

        return view('bills.enter-bill.create', [
            'suppliers' => $suppliers,
            'liabilityAccounts' => $liabilityAccounts,
            'expenseAccounts' => $expenseAccounts,
            'accounts' => $accounts,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created bill.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'memo' => 'nullable|string',
            'terms' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.expense_account_id' => 'required|exists:accounts,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.memo' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create the bill
            $bill = Bill::create([
                'supplier_id' => $request->supplier_id,
                'liability_account_id' => $request->liability_account_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'memo' => $request->memo,
                'terms' => $request->terms,
                'reference' => $request->reference,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            // Create bill items
            foreach ($request->items as $itemData) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'expense_account_id' => $itemData['expense_account_id'],
                    'description' => $itemData['description'],
                    'amount' => $itemData['amount'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'memo' => $itemData['memo'] ?? null,
                ]);
            }

            // Reload the bill with items to calculate totals
            $bill->load('items');
            
            // Manually calculate and update totals (using tax_amount from BillItem model)
            $subtotal = $bill->items->sum('amount');
            $taxAmount = $bill->items->sum('tax_amount');
            $totalAmount = $bill->items->sum('total_amount');
            
            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create journal entries
            $this->createJournalEntries($bill);

            DB::commit();

            return redirect()->route('bills.enter-bill.show', $bill)
                ->with('success', 'Bill created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $enter_bill)
    {
        // Load all relationships
        $enter_bill->load(['supplier', 'liabilityAccount', 'items.expenseAccount', 'payments', 'createdBy']);

        return view('bills.enter-bill.show', [
            'bill' => $enter_bill
        ]);
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function edit(Bill $enter_bill)
    {
        if ($enter_bill->status !== 'draft') {
            return redirect()->route('bills.enter-bill.show', $enter_bill)
                ->with('error', 'Only draft bills can be edited.');
        }

        $enter_bill->load(['items']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();
        $expenseAccounts = Account::where('account_type', 'Expense')->orderBy('account_name')->get();

        return view('bills.enter-bill.edit', [
            'bill' => $enter_bill,
            'suppliers' => $suppliers,
            'liabilityAccounts' => $liabilityAccounts,
            'expenseAccounts' => $expenseAccounts,
        ]);
    }

    /**
     * Update the specified bill.
     */
    public function update(Request $request, Bill $enter_bill)
    {
        if ($enter_bill->status !== 'draft') {
            return redirect()->route('bills.enter-bill.show', $enter_bill)
                ->with('error', 'Only draft bills can be edited.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'memo' => 'nullable|string',
            'terms' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.expense_account_id' => 'required|exists:accounts,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.memo' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update the bill
            $enter_bill->update([
                'supplier_id' => $request->supplier_id,
                'liability_account_id' => $request->liability_account_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'memo' => $request->memo,
                'terms' => $request->terms,
                'reference' => $request->reference,
            ]);

            // Delete existing items
            $enter_bill->items()->delete();

            // Create new bill items
            foreach ($request->items as $itemData) {
                BillItem::create([
                    'bill_id' => $enter_bill->id,
                    'expense_account_id' => $itemData['expense_account_id'],
                    'description' => $itemData['description'],
                    'amount' => $itemData['amount'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'memo' => $itemData['memo'] ?? null,
                ]);
            }

            // Update journal entries
            $this->updateJournalEntries($enter_bill);

            DB::commit();

            return redirect()->route('bills.enter-bill.show', $enter_bill)
                ->with('success', 'Bill updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified bill.
     */
    public function destroy(Bill $enter_bill)
    {
        if ($enter_bill->status !== 'draft') {
            return redirect()->route('bills.enter-bill.index')
                ->with('error', 'Only draft bills can be deleted.');
        }

        DB::beginTransaction();
        try {
            // Delete journal entries
            Journal::where('reference_type', 'bill')
                  ->where('reference_id', $enter_bill->id)
                  ->delete();

            // Delete bill items
            $enter_bill->items()->delete();

            // Delete the bill
            $enter_bill->delete();

            DB::commit();

            return redirect()->route('bills.enter-bill.index')
                ->with('success', 'Bill deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Create journal entries for the bill.
     */
    private function createJournalEntries(Bill $bill)
    {
        $bill->load('items.expenseAccount');

        // Get the liability account ID
        $liabilityAccountId = $bill->liability_account_id;
        
        // For each bill item, create a journal entry
        foreach ($bill->items as $item) {
            // Create a transaction record first
            $transaction = \App\Models\Transaction::create([
                'account_id' => $liabilityAccountId,
                'type' => 'credit',
                'amount' => $item->amount,
                'description' => "Bill {$bill->bill_number} - {$item->description}",
                'transaction_date' => $bill->bill_date,
            ]);
            
            // Create journal entry (debit expense, credit liability)
            Journal::create([
                'transaction_id' => $transaction->id,
                'debit_account_id' => $item->expense_account_id,
                'credit_account_id' => $liabilityAccountId,
                'amount' => $item->amount,
                'date' => $bill->bill_date,
            ]);
        }
    }

    /**
     * Update journal entries for the bill.
     */
    private function updateJournalEntries(Bill $bill)
    {
        // Delete existing journal entries
        Journal::where('reference_type', 'bill')
              ->where('reference_id', $bill->id)
              ->delete();

        // Create new journal entries
        $this->createJournalEntries($bill);
    }
}