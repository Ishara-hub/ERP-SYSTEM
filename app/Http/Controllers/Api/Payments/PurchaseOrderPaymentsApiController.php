<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\GeneralJournal;
use App\Models\Journal;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPaymentsApiController extends ApiController
{
    /**
     * List purchase order payments with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['purchaseOrder.supplier', 'paymentCategory'])
                ->where('payment_category', 'purchase_order');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('payment_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('purchaseOrder.supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_method') && $request->payment_method !== 'all') {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }

            $payments = $query->orderBy('payment_date', 'desc')
                ->paginate((int) $request->get('per_page', 20))
                ->withQueryString();

            return $this->success([
                'payments' => $payments,
                'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to']),
            ], 'Purchase order payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase order payments: ' . $e->getMessage());
        }
    }

    /**
     * Provide supporting data for payment forms.
     */
    public function formData(Request $request)
    {
        try {
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get(['id', 'account_name']);
            $bankAccounts = Account::where('account_type', 'Asset')
                ->where(function ($q) {
                    $q->where('account_name', 'like', '%bank%')
                        ->orWhere('account_name', 'like', '%cash%');
                })
                ->orderBy('account_name')
                ->get(['id', 'account_name']);

            $purchaseOrderId = $request->input('purchase_order_id');
            $purchaseOrder = null;
            if ($purchaseOrderId) {
                $purchaseOrder = PurchaseOrder::with('supplier', 'items')
                    ->whereIn('status', ['draft', 'sent', 'confirmed', 'partial', 'received'])
                    ->find($purchaseOrderId);
            }

            return $this->success([
                'suppliers' => $suppliers,
                'liability_accounts' => $liabilityAccounts,
                'bank_accounts' => $bankAccounts,
                'purchase_order' => $purchaseOrder,
            ], 'Purchase order payment form data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payment form data: ' . $e->getMessage());
        }
    }

    /**
     * Store a new purchase order payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bank_account_id' => 'required|exists:accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'check_number' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::with('supplier')->findOrFail($request->purchase_order_id);

            if ($request->amount > $purchaseOrder->balance_due) {
                return $this->validationError([
                    'amount' => ['Payment amount cannot exceed purchase order balance of $' . number_format($purchaseOrder->balance_due, 2)],
                ]);
            }

            $payment = Payment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'check_number' => $request->check_number,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => $request->status ?? 'completed',
                'payment_type' => 'paid',
                'payment_category' => 'purchase_order',
                'bank_account_id' => $request->bank_account_id,
                'received_by' => optional(Auth::user())->name,
            ]);

            $purchaseOrder->increment('paid_amount', $request->amount);
            $purchaseOrder->refresh();
            if (method_exists($purchaseOrder, 'calculateTotals')) {
                $purchaseOrder->calculateTotals();
                $purchaseOrder->save();
            }

            $this->createPaymentJournalEntries($payment, $request->amount, $request->bank_account_id, $request->liability_account_id);

            if (in_array($request->payment_method, ['check', 'bank_transfer'], true)) {
                $this->createBankTransaction($payment);
            }

            DB::commit();

            return $this->success($payment->fresh(['purchaseOrder.supplier', 'bankAccount']), 'Purchase order payment recorded successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific purchase order payment.
     */
    public function show(Payment $purchaseOrderPayment)
    {
        if ($purchaseOrderPayment->payment_category !== 'purchase_order') {
            return $this->notFound('Payment not found');
        }

        try {
            $purchaseOrderPayment->load(['purchaseOrder.supplier', 'bankAccount']);
            return $this->success($purchaseOrderPayment, 'Purchase order payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve purchase order payment: ' . $e->getMessage());
        }
    }

    /**
     * Update a purchase order payment.
     */
    public function update(Request $request, Payment $purchaseOrderPayment)
    {
        if ($purchaseOrderPayment->payment_category !== 'purchase_order') {
            return $this->notFound('Payment not found');
        }

        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bank_account_id' => 'required|exists:accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'check_number' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::with('supplier')->findOrFail($request->purchase_order_id);

            $otherPayments = $purchaseOrder->payments()
                ->where('id', '!=', $purchaseOrderPayment->id)
                ->where('payment_category', 'purchase_order')
                ->where('status', 'completed')
                ->sum('amount');

            $availableBalance = ($purchaseOrder->total_amount ?? 0) - $otherPayments;

            if ($request->amount > $availableBalance) {
                return $this->validationError([
                    'amount' => ['Payment amount cannot exceed available balance of $' . number_format($availableBalance, 2)],
                ]);
            }

            $purchaseOrderPayment->update([
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'check_number' => $request->check_number,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => $request->status ?? $purchaseOrderPayment->status,
                'bank_account_id' => $request->bank_account_id,
            ]);

            $purchaseOrder->update(['paid_amount' => $otherPayments + $request->amount]);
            if (method_exists($purchaseOrder, 'calculateTotals')) {
                $purchaseOrder->calculateTotals();
                $purchaseOrder->save();
            }

            $this->createPaymentJournalEntries($purchaseOrderPayment, $request->amount, $request->bank_account_id, $request->liability_account_id);

            DB::commit();

            return $this->success($purchaseOrderPayment->fresh(['purchaseOrder.supplier', 'bankAccount']), 'Purchase order payment updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to update payment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a purchase order payment.
     */
    public function destroy(Payment $purchaseOrderPayment)
    {
        if ($purchaseOrderPayment->payment_category !== 'purchase_order') {
            return $this->notFound('Payment not found');
        }

        try {
            $purchaseOrderPayment->delete();
            return $this->success(null, 'Purchase order payment deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Helper: create journal entries for payment.
     */
    private function createPaymentJournalEntries(Payment $payment, float $amount, int $bankAccountId, int $liabilityAccountId): void
    {
        $reference = $payment->payment_number ?? ('PO-PAY-' . $payment->id);
        $supplierName = optional($payment->supplier)->name ?? 'Supplier';

        $generalJournal = GeneralJournal::create([
            'transaction_date' => $payment->payment_date,
            'reference' => $reference,
            'description' => "Payment to {$supplierName}" . ($payment->notes ? " - {$payment->notes}" : ''),
            'created_by' => optional(Auth::user())->id,
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
     * Helper: create bank transaction record.
     */
    private function createBankTransaction(Payment $payment): void
    {
        $supplierName = optional($payment->supplier)->name ?? 'Supplier';

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

