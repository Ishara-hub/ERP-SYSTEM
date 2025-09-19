<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Api\ApiController;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentsApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['invoice.customer']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('payment_number', 'like', '%' . $request->search . '%')
                      ->orWhere('reference', 'like', '%' . $request->search . '%')
                      ->orWhereHas('invoice.customer', function ($customerQuery) use ($request) {
                          $customerQuery->where('name', 'like', '%' . $request->search . '%');
                      });
                });
            }

            // Status filter
            if ($request->has('status') && $request->status && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Payment method filter
            if ($request->has('payment_method') && $request->payment_method && $request->payment_method !== 'all') {
                $query->where('payment_method', $request->payment_method);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['payment_number', 'payment_date', 'amount', 'status', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $payments = $query->paginate(15);

            $data = [
                'payments' => $payments,
                'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
            ];

            return $this->success($data, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payments: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed,failed,cancelled',
                'bank_name' => 'nullable|string|max:255',
                'check_number' => 'nullable|string|max:255',
                'transaction_id' => 'nullable|string|max:255',
                'fee_amount' => 'nullable|numeric|min:0',
                'received_by' => 'nullable|string|max:255',
            ]);

            // Validate payment amount doesn't exceed invoice balance
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            if ($validated['amount'] > $invoice->balance_due) {
                return $this->error('Payment amount cannot exceed invoice balance of ' . number_format($invoice->balance_due, 2), null, 422);
            }

            DB::beginTransaction();
            
            $payment = Payment::create($validated);

            DB::commit();

            $payment->load(['invoice.customer']);

            return $this->success($payment, 'Payment recorded successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        try {
            $payment->load(['invoice.customer', 'invoice.lineItems']);
            return $this->success($payment, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve payment: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed,failed,cancelled',
                'bank_name' => 'nullable|string|max:255',
                'check_number' => 'nullable|string|max:255',
                'transaction_id' => 'nullable|string|max:255',
                'fee_amount' => 'nullable|numeric|min:0',
                'received_by' => 'nullable|string|max:255',
            ]);

            // Validate payment amount doesn't exceed invoice balance (excluding current payment)
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $otherPayments = $invoice->payments()->where('id', '!=', $payment->id)->where('status', 'completed')->sum('amount');
            $availableBalance = $invoice->total_amount - $otherPayments;
            
            if ($validated['amount'] > $availableBalance) {
                return $this->error('Payment amount cannot exceed available balance of ' . number_format($availableBalance, 2), null, 422);
            }

            DB::beginTransaction();
            
            $payment->update($validated);

            DB::commit();

            $payment->load(['invoice.customer']);

            return $this->success($payment, 'Payment updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to update payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            $payment->delete();
            return $this->success(null, 'Payment deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Receive payment for a specific invoice
     */
    public function receivePayment(Invoice $invoice)
    {
        try {
            $invoice->load('customer');
            return $this->success($invoice, 'Invoice data for payment received successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve invoice data: ' . $e->getMessage());
        }
    }

    /**
     * Store received payment
     */
    public function storeReceivedPayment(Request $request, Invoice $invoice)
    {
        try {
            $validated = $request->validate([
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'bank_name' => 'nullable|string|max:255',
                'check_number' => 'nullable|string|max:255',
                'transaction_id' => 'nullable|string|max:255',
                'fee_amount' => 'nullable|numeric|min:0',
                'received_by' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'status' => 'completed',
                'bank_name' => $validated['bank_name'],
                'check_number' => $validated['check_number'],
                'transaction_id' => $validated['transaction_id'],
                'fee_amount' => $validated['fee_amount'] ?? 0,
                'received_by' => $validated['received_by'],
            ]);

            DB::commit();

            $payment->load(['invoice.customer']);

            return $this->success($payment, 'Payment received successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to receive payment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a general payment
     */
    public function createGeneral()
    {
        try {
            // Get bank accounts (you might want to create a BankAccount model)
            $bankAccounts = [
                (object)['id' => 1, 'name' => 'Petty Cash Imprest', 'account_number' => '7055200', 'balance' => 0.00],
                (object)['id' => 2, 'name' => 'Main Checking', 'account_number' => '1234567', 'balance' => 5000.00],
                (object)['id' => 3, 'name' => 'Savings Account', 'account_number' => '7654321', 'balance' => 10000.00],
            ];

            // Get chart of accounts - with fallback if table doesn't exist
            $accounts = [];
            try {
                $accounts = \App\Models\Account::where('type', '!=', 'header')
                    ->orderBy('name')
                    ->get(['id', 'name', 'type']);
            } catch (\Exception $e) {
                // Fallback if accounts table doesn't exist
                $accounts = [
                    (object)['id' => 1, 'name' => 'Office Supplies', 'type' => 'expense'],
                    (object)['id' => 2, 'name' => 'Utilities', 'type' => 'expense'],
                    (object)['id' => 3, 'name' => 'Rent', 'type' => 'expense'],
                ];
            }

            // Get customers - with fallback if table doesn't exist
            $customers = [];
            try {
                $customers = \App\Models\Customer::orderBy('name')
                    ->get(['id', 'name', 'email', 'address']);
            } catch (\Exception $e) {
                // Fallback if customers table doesn't exist
                $customers = [
                    (object)['id' => 1, 'name' => 'Sample Customer', 'email' => 'customer@example.com', 'address' => '123 Main St'],
                ];
            }

            $data = [
                'bankAccounts' => $bankAccounts,
                'accounts' => $accounts,
                'customers' => $customers,
            ];

            return $this->success($data, 'General payment form data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve general payment form data: ' . $e->getMessage());
        }
    }

    /**
     * Store a general payment
     */
    public function storeGeneral(Request $request)
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|string',
                'payee' => 'required|string|max:255',
                'address' => 'nullable|string',
                'memo' => 'nullable|string|max:255',
                'check_number' => 'nullable|string|max:255',
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'expenses' => 'array',
                'expenses.*.account_id' => 'required|string',
                'expenses.*.amount' => 'required|numeric|min:0',
                'expenses.*.memo' => 'nullable|string',
                'expenses.*.customer_job' => 'nullable|string',
                'expenses.*.billable' => 'boolean',
                'items' => 'array',
                'items.*.account_id' => 'required|string',
                'items.*.amount' => 'required|numeric|min:0',
                'items.*.memo' => 'nullable|string',
                'items.*.customer_job' => 'nullable|string',
                'items.*.billable' => 'boolean',
                'print_later' => 'boolean',
                'pay_online' => 'boolean',
            ]);

            // Validate that we have at least one expense or item
            if (empty($validated['expenses']) && empty($validated['items'])) {
                return $this->error('Please add at least one expense or item.', null, 422);
            }

            // Validate that the total amount matches the sum of expenses and items
            $totalExpenses = collect($validated['expenses'])->sum('amount');
            $totalItems = collect($validated['items'])->sum('amount');
            $calculatedTotal = $totalExpenses + $totalItems;
            
            if (abs($validated['amount'] - $calculatedTotal) > 0.01) {
                return $this->error('Amount must match the sum of expenses and items.', null, 422);
            }

            DB::beginTransaction();
            
            // Create the general payment record
            $payment = Payment::create([
                'invoice_id' => null, // General payment not tied to specific invoice
                'payment_date' => $validated['date'],
                'payment_method' => 'check',
                'amount' => $validated['amount'],
                'reference' => $validated['check_number'],
                'notes' => $validated['memo'],
                'status' => 'completed',
                'bank_name' => $validated['bank_account_id'],
                'check_number' => $validated['check_number'],
                'transaction_id' => null,
                'fee_amount' => 0,
                'received_by' => $validated['payee'],
                'payment_type' => 'general',
                'payee' => $validated['payee'],
                'address' => $validated['address'],
                'print_later' => $validated['print_later'] ?? false,
                'pay_online' => $validated['pay_online'] ?? false,
            ]);

            // Store expenses as journal entries or line items
            foreach ($validated['expenses'] as $expense) {
                if (!empty($expense['account_id']) && $expense['amount'] > 0) {
                    // Create journal entry for expense
                    \App\Models\Journal::create([
                        'date' => $validated['date'],
                        'description' => $expense['memo'] ?: 'General Payment - ' . $validated['payee'],
                        'account_id' => $expense['account_id'],
                        'debit' => $expense['amount'],
                        'credit' => 0,
                        'reference' => $validated['check_number'],
                        'payment_id' => $payment->id,
                    ]);
                }
            }

            // Store items as journal entries or line items
            foreach ($validated['items'] as $item) {
                if (!empty($item['account_id']) && $item['amount'] > 0) {
                    // Create journal entry for item
                    \App\Models\Journal::create([
                        'date' => $validated['date'],
                        'description' => $item['memo'] ?: 'General Payment - ' . $validated['payee'],
                        'account_id' => $item['account_id'],
                        'debit' => $item['amount'],
                        'credit' => 0,
                        'reference' => $validated['check_number'],
                        'payment_id' => $payment->id,
                    ]);
                }
            }

            // Create the bank account credit entry
            \App\Models\Journal::create([
                'date' => $validated['date'],
                'description' => 'General Payment - ' . $validated['payee'],
                'account_id' => $validated['bank_account_id'],
                'debit' => 0,
                'credit' => $validated['amount'],
                'reference' => $validated['check_number'],
                'payment_id' => $payment->id,
            ]);

            DB::commit();

            return $this->success($payment, 'General payment recorded successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->serverError('Failed to record payment: ' . $e->getMessage());
        }
    }
}
