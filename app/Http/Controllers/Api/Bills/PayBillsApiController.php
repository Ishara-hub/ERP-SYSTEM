<?php

namespace App\Http\Controllers\Api\Bills;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\GeneralJournal;
use App\Models\Journal;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayBillsApiController extends ApiController
{
    /**
     * Display bill payments with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['bill.supplier', 'paymentCategory'])
                ->whereNotNull('bill_id');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', '%' . $search . '%')
                      ->orWhereHas('bill', function ($billQuery) use ($search) {
                          $billQuery->where('bill_number', 'like', '%' . $search . '%');
                      });
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }

            $payments = $query->orderBy('payment_date', 'desc')->paginate(20)->withQueryString();

            return $this->success([
                'payments' => $payments,
                'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to']),
            ], 'Bill payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve bill payments: ' . $e->getMessage());
        }
    }

    /**
     * Store a new bill payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bank_account_id' => 'required|exists:accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'check_number' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'selected_bills' => 'required|array|min:1',
            'selected_bills.*' => 'required|exists:bills,id',
            'payment_amount' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $billPayments = [];

            foreach ($request->selected_bills as $billId) {
                $bill = Bill::with('items')->findOrFail($billId);
                $amount = (float) ($request->payment_amount[$billId] ?? 0);

                if ($amount <= 0) {
                    return $this->validationError([
                        "payment_amount.{$billId}" => ["Payment amount for bill {$bill->bill_number} must be greater than zero."]
                    ], 'Validation failed');
                }

                if ($amount > $bill->balance_due) {
                    return $this->validationError([
                        "payment_amount.{$billId}" => ["Payment amount for bill {$bill->bill_number} cannot exceed balance due of $" . number_format($bill->balance_due, 2)]
                    ], 'Validation failed');
                }

                $totalAmount += $amount;
                $billPayments[] = [
                    'bill' => $bill,
                    'amount' => $amount,
                ];
            }

            $payment = Payment::create([
                'supplier_id' => $request->supplier_id,
                'bill_id' => null, // placeholder, individual bills handled below
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $totalAmount,
                'check_number' => $request->check_number,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'completed',
                'payment_type' => 'paid',
                'payment_category_id' => 1,
                'bank_account_id' => $request->bank_account_id,
                'received_by' => optional(auth()->user())->name,
            ]);

            foreach ($billPayments as $billPayment) {
                $bill = $billPayment['bill'];
                $amount = $billPayment['amount'];

                $bill->increment('paid_amount', $amount);
                $bill->refresh();
                $bill->load('items');
                $bill->calculateTotals();
                $bill->save();
            }

            $this->createPaymentJournalEntries($payment, $totalAmount, $request->bank_account_id, $request->liability_account_id);

            if (in_array($request->payment_method, ['check', 'bank_transfer'])) {
                $this->createBankTransaction($payment);
            }

            DB::commit();

            $payment->load(['bill.supplier', 'paymentCategory', 'bankAccount']);

            return $this->success($payment, 'Bill payment recorded successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific bill payment.
     */
    public function show(Payment $payment)
    {
        try {
            $payment->load(['bill.supplier', 'paymentCategory', 'bankAccount']);
            return $this->success($payment, 'Bill payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve bill payment: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve unpaid bills for a supplier and liability account.
     */
    public function getUnpaidBills(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $bills = Bill::with(['supplier', 'liabilityAccount'])
                ->where('supplier_id', $request->supplier_id)
                ->where('liability_account_id', $request->liability_account_id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('bill_date', 'asc')
                ->get();

            return $this->success($bills, 'Unpaid bills retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve unpaid bills: ' . $e->getMessage());
        }
    }

    /**
     * Provide supporting lists for bill payment form.
     */
    public function formData()
    {
        try {
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
            $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();
            $bankAccounts = Account::where(function ($query) {
                    $query->where('account_type', 'Asset')
                          ->where(function ($q) {
                              $q->where('account_name', 'like', '%bank%')
                                ->orWhere('account_name', 'like', '%cash%');
                          });
                })
                ->orderBy('account_name')
                ->get();

            return $this->success([
                'suppliers' => $suppliers,
                'liability_accounts' => $liabilityAccounts,
                'bank_accounts' => $bankAccounts,
            ], 'Form data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve form data: ' . $e->getMessage());
        }
    }

    /**
     * Create journal entries for bill payment.
     */
    private function createPaymentJournalEntries(Payment $payment, float $amount, int $bankAccountId, int $liabilityAccountId): void
    {
        $reference = $payment->payment_number ?? ('PAY-' . $payment->id);
        $supplierName = $payment->supplier ? $payment->supplier->name : 'Supplier';

        $generalJournal = GeneralJournal::create([
            'transaction_date' => $payment->payment_date,
            'reference' => $reference,
            'description' => "Payment to {$supplierName}" . ($payment->notes ? " - {$payment->notes}" : ''),
            'created_by' => Auth::id(),
        ]);

        JournalEntryLine::create([
            'journal_id' => $generalJournal->id,
            'account_id' => $liabilityAccountId,
            'debit' => $amount,
            'credit' => 0,
            'description' => "Payment {$reference}",
        ]);

        JournalEntryLine::create([
            'journal_id' => $generalJournal->id,
            'account_id' => $bankAccountId,
            'debit' => 0,
            'credit' => $amount,
            'description' => "Payment {$reference}",
        ]);

        $transaction = \App\Models\Transaction::create([
            'account_id' => $liabilityAccountId,
            'type' => 'debit',
            'amount' => $amount,
            'description' => "Payment {$reference}",
            'transaction_date' => $payment->payment_date,
        ]);

        Journal::create([
            'transaction_id' => $transaction->id,
            'debit_account_id' => $liabilityAccountId,
            'credit_account_id' => $bankAccountId,
            'amount' => $amount,
            'date' => $payment->payment_date,
        ]);
    }

    /**
     * Create a bank transaction for the payment.
     */
    private function createBankTransaction(Payment $payment): void
    {
        $supplierName = $payment->supplier ? $payment->supplier->name : 'Supplier';

        BankTransaction::create([
            'bank_account_id' => $payment->bank_account_id,
            'transaction_date' => $payment->payment_date,
            'type' => BankTransaction::TYPE_WITHDRAWAL,
            'amount' => $payment->amount,
            'description' => "Payment to {$supplierName}" . ($payment->notes ? " - {$payment->notes}" : ''),
            'check_number' => $payment->check_number,
            'reference_number' => $payment->reference,
            'status' => BankTransaction::STATUS_PENDING,
            'payment_id' => $payment->id,
        ]);
    }
}

