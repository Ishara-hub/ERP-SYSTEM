<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\Journal;
use App\Models\GeneralJournal;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $items = Item::where('is_active', true)->orderBy('item_name')->get();
        
        // Get pending purchase orders for PO receiving
        $purchaseOrders = PurchaseOrder::where('status', 'pending')
            ->orWhere('status', 'partial')
            ->with('supplier')
            ->orderBy('order_date', 'desc')
            ->get();

        return view('bills.enter-bill.create', [
            'suppliers' => $suppliers,
            'liabilityAccounts' => $liabilityAccounts,
            'expenseAccounts' => $expenseAccounts,
            'accounts' => $accounts,
            'items' => $items,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Store a newly created bill.
     */
    public function store(Request $request)
    {
        // Basic validation first
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'memo' => 'nullable|string',
            'terms' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
        ]);

        // Filter out empty rows
        $validExpenses = collect($request->expenses ?? [])->filter(function ($exp) {
            return !empty($exp['expense_account_id']) && !empty($exp['description']) && isset($exp['amount']) && $exp['amount'] > 0;
        })->values()->toArray();
        
        $validItems = collect($request->items ?? [])->filter(function ($item) {
            return !empty($item['item_id']) && isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->toArray();
        
        // Validate that at least one valid line exists
        if (count($validExpenses) == 0 && count($validItems) == 0) {
            return back()->withErrors(['error' => 'Please add at least one expense or item line.']);
        }
        
        // Now validate the data structure for valid entries
        $validator = Validator::make(
            ['expenses' => $validExpenses, 'items' => $validItems],
            [
                'expenses.*.expense_account_id' => 'required|exists:accounts,id',
                'expenses.*.description' => 'required|string|max:255',
                'expenses.*.amount' => 'required|numeric|min:0.01',
                'expenses.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.description' => 'nullable|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]
        );
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            // Create the bill
            $bill = Bill::create([
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'liability_account_id' => $request->liability_account_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'memo' => $request->memo,
                'terms' => $request->terms,
                'reference' => $request->reference,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            // Create expense bill items
            foreach ($validExpenses as $itemData) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'expense_account_id' => $itemData['expense_account_id'],
                    'description' => $itemData['description'],
                    'amount' => $itemData['amount'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                ]);
            }

            // Create item bill items
            foreach ($validItems as $itemData) {
                $billItem = BillItem::create([
                    'bill_id' => $bill->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'] ?? null,
                    'unit_price' => $itemData['unit_price'] ?? null,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                ]);
                
                // Record inventory purchase if item exists and is inventory type
                if ($billItem->item_id && $itemData['quantity'] > 0) {
                    $item = Item::find($billItem->item_id);
                    if ($item) {
                        $reference = $bill->bill_number ?? 'BILL-' . $bill->id;
                        $supplierName = $bill->supplier->name ?? 'Supplier';
                        $description = "Bill {$reference} - Purchase from {$supplierName}";
                        
                        InventoryService::recordPurchase(
                            $item,
                            $itemData['quantity'],
                            $itemData['unit_price'] ?? $item->cost,
                            'bill',
                            $bill->id,
                            $bill->bill_date,
                            $description
                        );
                    }
                }
            }

            // If PO is selected, update PO items received quantities
            if ($request->purchase_order_id) {
                $this->updatePOReceivedQuantities($request->purchase_order_id, $validItems);
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
     * Update purchase order received quantities.
     */
    private function updatePOReceivedQuantities($poId, $billItems)
    {
        $po = PurchaseOrder::with('items')->findOrFail($poId);
        
        foreach ($billItems as $billItem) {
            // Find matching PO item by item_id if provided
            if (!empty($billItem['item_id'])) {
                $poItem = $po->items->where('item_id', $billItem['item_id'])->first();
                if ($poItem) {
                    $receivedQuantity = $billItem['quantity'] ?? 0;
                    $poItem->increment('received_quantity', $receivedQuantity);
                }
            }
        }
        
        // Update PO status based on received quantities
        $po->load('items');
        $fullyReceived = $po->items->every(function($item) {
            return $item->received_quantity >= $item->quantity;
        });
        
        $partiallyReceived = $po->items->some(function($item) {
            return $item->received_quantity > 0;
        });
        
        if ($fullyReceived) {
            $po->status = 'received';
        } elseif ($partiallyReceived) {
            $po->status = 'partial';
        }
        
        $po->save();
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
            // Get the bill's general journal reference
            $reference = $enter_bill->bill_number ?? 'BILL-' . $enter_bill->id;
            
            // Find and delete existing general journal entries for this bill
            $generalJournals = GeneralJournal::where('reference', $reference)->get();
            foreach ($generalJournals as $gj) {
                $gj->entries()->delete();
                $gj->delete();
            }
            
            // Delete old journal entries (backward compatibility)
            // Find transactions related to this bill and delete their journals
            $transactions = \App\Models\Transaction::where('description', 'like', "%Bill {$reference}%")->get();
            foreach ($transactions as $transaction) {
                $transaction->journals()->delete();
                $transaction->delete();
            }

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
        $bill->load(['items.expenseAccount', 'items.item']);

        // Get the liability account ID
        $liabilityAccountId = $bill->liability_account_id;
        
        // Calculate total for the bill
        $totalAmount = $bill->total_amount ?? $bill->items->sum('total_amount');
        
        // Get supplier name or default
        $supplierName = $bill->supplier ? $bill->supplier->name : 'Supplier';
        $reference = $bill->bill_number ?? ('BILL-' . $bill->id);
        
        // Create a general journal entry for the entire bill
        $generalJournal = GeneralJournal::create([
            'transaction_date' => $bill->bill_date,
            'reference' => $reference,
            'description' => "Bill for {$supplierName} - {$bill->memo}",
            'created_by' => Auth::id(),
        ]);
        
        // For each bill item, create journal entry lines
        foreach ($bill->items as $item) {
            // Determine debit account based on item type
            $debitAccountId = null;
            
            if ($item->expense_account_id) {
                // This is an expense line
                $debitAccountId = $item->expense_account_id;
            } elseif ($item->item_id && $item->item) {
                // This is an item line - use asset account for inventory or COGS for purchased items
                if ($item->item->asset_account_id) {
                    $debitAccountId = $item->item->asset_account_id;
                } elseif ($item->item->cogs_account_id) {
                    $debitAccountId = $item->item->cogs_account_id;
                }
            }
            
            // Only create journal line if we have a valid debit account
            if ($debitAccountId && $item->total_amount > 0) {
                JournalEntryLine::create([
                    'journal_id' => $generalJournal->id,
                    'account_id' => $debitAccountId,
                    'debit' => $item->total_amount,
                    'credit' => 0,
                    'description' => $item->description,
                ]);
            }
        }
        
        // Create the credit entry for the liability account (total of all items)
        JournalEntryLine::create([
            'journal_id' => $generalJournal->id,
            'account_id' => $liabilityAccountId,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => "Accounts Payable for Bill {$reference}",
        ]);
        
        // Also create the old journal entries for backward compatibility
        foreach ($bill->items as $item) {
            $debitAccountId = null;
            
            if ($item->expense_account_id) {
                $debitAccountId = $item->expense_account_id;
            } elseif ($item->item_id && $item->item) {
                if ($item->item->asset_account_id) {
                    $debitAccountId = $item->item->asset_account_id;
                } elseif ($item->item->cogs_account_id) {
                    $debitAccountId = $item->item->cogs_account_id;
                }
            }
            
            if ($debitAccountId && $item->total_amount > 0) {
                // Create a transaction record first
                $transaction = \App\Models\Transaction::create([
                    'account_id' => $liabilityAccountId,
                    'type' => 'credit',
                    'amount' => $item->total_amount,
                    'description' => "Bill {$reference} - {$item->description}",
                    'transaction_date' => $bill->bill_date,
                ]);
                
                // Create journal entry (debit expense/COGS, credit liability)
                Journal::create([
                    'transaction_id' => $transaction->id,
                    'debit_account_id' => $debitAccountId,
                    'credit_account_id' => $liabilityAccountId,
                    'amount' => $item->total_amount,
                    'date' => $bill->bill_date,
                ]);
            }
        }
    }

    /**
     * Update journal entries for the bill.
     */
    private function updateJournalEntries(Bill $bill)
    {
        // Get the bill's general journal reference
        $reference = $bill->bill_number ?? 'BILL-' . $bill->id;
        
        // Find and delete existing general journal entries for this bill
        $generalJournals = GeneralJournal::where('reference', $reference)->get();
        foreach ($generalJournals as $gj) {
            $gj->entries()->delete();
            $gj->delete();
        }
        
        // Delete old journal entries (backward compatibility)
        // Find transactions related to this bill and delete their journals
        $transactions = \App\Models\Transaction::where('description', 'like', "%Bill {$reference}%")->get();
        foreach ($transactions as $transaction) {
            $transaction->journals()->delete();
            $transaction->delete();
        }

        // Create new journal entries
        $this->createJournalEntries($bill);
    }
    
    /**
     * Get purchase order items for AJAX request.
     */
    public function getPOItems(Request $request)
    {
        if (!$request->has('po_id')) {
            return response()->json(['error' => 'PO ID is required'], 400);
        }
        
        $po = PurchaseOrder::with(['items.item'])->find($request->po_id);
        
        if (!$po) {
            return response()->json(['error' => 'Purchase Order not found'], 404);
        }
        
        $items = $po->items->map(function($poItem) {
            return [
                'id' => $poItem->id,
                'item_id' => $poItem->item_id,
                'description' => $poItem->description,
                'quantity' => $poItem->quantity - $poItem->received_quantity, // Remaining quantity
                'unit_price' => $poItem->unit_price,
                'tax_rate' => $poItem->tax_rate ?? 0,
                'item_name' => $poItem->item ? $poItem->item->item_name : '',
            ];
        });
        
        return response()->json($items);
    }
}