<?php

namespace App\Http\Controllers\Api\Bills;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\GeneralJournal;
use App\Models\Item;
use App\Models\Journal;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EnterBillsApiController extends ApiController
{
    /**
     * Display a listing of bills along with supporting data.
     */
    public function index(Request $request)
    {
        try {
            $query = Bill::with(['supplier', 'liabilityAccount', 'items.expenseAccount']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('bill_number', 'like', '%' . $search . '%')
                      ->orWhere('reference', 'like', '%' . $search . '%')
                      ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                          $supplierQuery->where('name', 'like', '%' . $search . '%');
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
                $query->whereDate('bill_date', '>=', Carbon::parse($request->date_from));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('bill_date', '<=', Carbon::parse($request->date_to));
            }

            $bills = $query->orderBy('bill_date', 'desc')->paginate(20)->withQueryString();

            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
            $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();

            $data = [
                'bills' => $bills,
                'suppliers' => $suppliers,
                'liabilityAccounts' => $liabilityAccounts,
                'filters' => $request->only(['search', 'status', 'supplier_id', 'date_from', 'date_to']),
            ];

            return $this->success($data, 'Bills retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve bills: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created bill.
     */
    public function store(Request $request)
    {
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

        $validExpenses = collect($request->expenses ?? [])->filter(function ($exp) {
            return !empty($exp['expense_account_id']) && !empty($exp['description']) && isset($exp['amount']) && $exp['amount'] > 0;
        })->values()->toArray();

        $validItems = collect($request->items ?? [])->filter(function ($item) {
            return !empty($item['item_id']) && isset($item['quantity']) && $item['quantity'] > 0;
        })->values()->toArray();

        if (count($validExpenses) === 0 && count($validItems) === 0) {
            return $this->validationError([
                'line_items' => ['Please add at least one expense or item line.']
            ], 'Validation failed');
        }

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
            return $this->validationError($validator->errors(), 'Validation failed');
        }

        DB::beginTransaction();
        try {
            $bill = Bill::create([
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'liability_account_id' => $request->liability_account_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'memo' => $request->memo,
                'terms' => $request->terms,
                'reference' => $request->reference,
                'created_by' => Auth::id(),
                'status' => 'pending',
            ]);

            foreach ($validExpenses as $itemData) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'expense_account_id' => $itemData['expense_account_id'],
                    'description' => $itemData['description'],
                    'amount' => $itemData['amount'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                ]);
            }

            foreach ($validItems as $itemData) {
                $billItem = BillItem::create([
                    'bill_id' => $bill->id,
                    'item_id' => $itemData['item_id'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                ]);

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

            if ($request->purchase_order_id) {
                $this->updatePOReceivedQuantities($request->purchase_order_id, $validItems);
            }

            $bill->load('items');

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

            $this->createJournalEntries($bill);

            DB::commit();

            $bill->load(['supplier', 'items.expenseAccount', 'items.item']);

            return $this->success($bill, 'Bill created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to create bill: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific bill.
     */
    public function show(Bill $bill)
    {
        try {
            $bill->load(['supplier', 'liabilityAccount', 'items.expenseAccount', 'items.item', 'payments', 'createdBy']);
            return $this->success($bill, 'Bill retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve bill: ' . $e->getMessage());
        }
    }

    /**
     * Update a bill (only allowed for draft status).
     */
    public function update(Request $request, Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return $this->error('Only draft bills can be edited.', null, 403);
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
            $bill->update([
                'supplier_id' => $request->supplier_id,
                'liability_account_id' => $request->liability_account_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'memo' => $request->memo,
                'terms' => $request->terms,
                'reference' => $request->reference,
            ]);

            $bill->items()->delete();

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

            $bill->load('items');

            $bill->update([
                'subtotal' => $bill->items->sum('amount'),
                'tax_amount' => $bill->items->sum('tax_amount'),
                'total_amount' => $bill->items->sum('total_amount'),
                'balance_due' => $bill->items->sum('total_amount'),
            ]);

            $this->updateJournalEntries($bill);

            DB::commit();

            $bill->load(['supplier', 'items.expenseAccount']);

            return $this->success($bill, 'Bill updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to update bill: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified bill.
     */
    public function destroy(Bill $bill)
    {
        if ($bill->status !== 'draft') {
            return $this->error('Only draft bills can be deleted.', null, 403);
        }

        DB::beginTransaction();
        try {
            $reference = $bill->bill_number ?? 'BILL-' . $bill->id;

            $generalJournals = GeneralJournal::where('reference', $reference)->get();
            foreach ($generalJournals as $gj) {
                $gj->entries()->delete();
                $gj->delete();
            }

            $transactions = \App\Models\Transaction::where('description', 'like', "%Bill {$reference}%")->get();
            foreach ($transactions as $transaction) {
                $transaction->journals()->delete();
                $transaction->delete();
            }

            $bill->items()->delete();
            $bill->delete();

            DB::commit();

            return $this->success(null, 'Bill deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to delete bill: ' . $e->getMessage());
        }
    }

    /**
     * Get purchase order items for the given PO.
     */
    public function getPOItems(Request $request)
    {
        if (!$request->filled('po_id')) {
            return $this->validationError(['po_id' => ['PO ID is required']], 'Validation failed');
        }

        $po = PurchaseOrder::with(['items.item'])->find($request->po_id);

        if (!$po) {
            return $this->notFound('Purchase Order not found');
        }

        $items = $po->items->map(function ($poItem) {
            return [
                'id' => $poItem->id,
                'item_id' => $poItem->item_id,
                'description' => $poItem->description,
                'quantity' => $poItem->quantity - $poItem->received_quantity,
                'unit_price' => $poItem->unit_price,
                'tax_rate' => $poItem->tax_rate ?? 0,
                'item_name' => $poItem->item ? $poItem->item->item_name : '',
            ];
        });

        return $this->success(['items' => $items], 'Purchase order items retrieved successfully');
    }

    /**
     * Update purchase order received quantities.
     */
    private function updatePOReceivedQuantities($poId, array $billItems): void
    {
        $po = PurchaseOrder::with('items')->findOrFail($poId);

        foreach ($billItems as $billItem) {
            if (!empty($billItem['item_id'])) {
                $poItem = $po->items->where('item_id', $billItem['item_id'])->first();
                if ($poItem) {
                    $receivedQuantity = $billItem['quantity'] ?? 0;
                    $poItem->increment('received_quantity', $receivedQuantity);
                }
            }
        }

        $po->load('items');
        $fullyReceived = $po->items->every(function ($item) {
            return $item->received_quantity >= $item->quantity;
        });

        $partiallyReceived = $po->items->some(function ($item) {
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
     * Create accounting journal entries for a bill.
     */
    private function createJournalEntries(Bill $bill): void
    {
        $bill->load(['items.expenseAccount', 'items.item', 'supplier']);

        $liabilityAccountId = $bill->liability_account_id;
        $totalAmount = $bill->total_amount ?? $bill->items->sum('total_amount');
        $supplierName = $bill->supplier ? $bill->supplier->name : 'Supplier';
        $reference = $bill->bill_number ?? ('BILL-' . $bill->id);

        $generalJournal = GeneralJournal::create([
            'transaction_date' => $bill->bill_date,
            'reference' => $reference,
            'description' => "Bill for {$supplierName} - {$bill->memo}",
            'created_by' => Auth::id(),
        ]);

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
                JournalEntryLine::create([
                    'journal_id' => $generalJournal->id,
                    'account_id' => $debitAccountId,
                    'debit' => $item->total_amount,
                    'credit' => 0,
                    'description' => $item->description,
                ]);
            }
        }

        JournalEntryLine::create([
            'journal_id' => $generalJournal->id,
            'account_id' => $liabilityAccountId,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => "Accounts Payable for Bill {$reference}",
        ]);

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
                $transaction = \App\Models\Transaction::create([
                    'account_id' => $liabilityAccountId,
                    'type' => 'credit',
                    'amount' => $item->total_amount,
                    'description' => "Bill {$reference} - {$item->description}",
                    'transaction_date' => $bill->bill_date,
                ]);

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
     * Rebuild journal entries for a bill.
     */
    private function updateJournalEntries(Bill $bill): void
    {
        $reference = $bill->bill_number ?? 'BILL-' . $bill->id;

        $generalJournals = GeneralJournal::where('reference', $reference)->get();
        foreach ($generalJournals as $gj) {
            $gj->entries()->delete();
            $gj->delete();
        }

        $transactions = \App\Models\Transaction::where('description', 'like', "%Bill {$reference}%")->get();
        foreach ($transactions as $transaction) {
            $transaction->journals()->delete();
            $transaction->delete();
        }

        $this->createJournalEntries($bill);
    }
}

