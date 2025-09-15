<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        return Inertia::render('payments/index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to', 'sort_by', 'sort_direction'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;
        
        if ($invoiceId) {
            $invoice = Invoice::with('customer')->find($invoiceId);
        }

        $invoices = Invoice::where('status', '!=', 'paid')
            ->with('customer')
            ->orderBy('invoice_no')
            ->get(['id', 'invoice_no', 'total_amount', 'balance_due', 'customer_id']);

        return Inertia::render('payments/create', [
            'invoice' => $invoice,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
        $invoice = Invoice::findOrFail($request->invoice_id);
        if ($request->amount > $invoice->balance_due) {
            return back()->withErrors(['amount' => 'Payment amount cannot exceed invoice balance of ' . number_format($invoice->balance_due, 2)]);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create($request->all());

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer', 'invoice.lineItems']);
        
        return Inertia::render('payments/show', [
            'payment' => $payment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $payment->load(['invoice.customer']);
        
        $invoices = Invoice::where('status', '!=', 'paid')
            ->orWhere('id', $payment->invoice_id)
            ->with('customer')
            ->orderBy('invoice_no')
            ->get(['id', 'invoice_no', 'total_amount', 'balance_due', 'customer_id']);

        return Inertia::render('payments/edit', [
            'payment' => $payment,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
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
        $invoice = Invoice::findOrFail($request->invoice_id);
        $otherPayments = $invoice->payments()->where('id', '!=', $payment->id)->where('status', 'completed')->sum('amount');
        $availableBalance = $invoice->total_amount - $otherPayments;
        
        if ($request->amount > $availableBalance) {
            return back()->withErrors(['amount' => 'Payment amount cannot exceed available balance of ' . number_format($availableBalance, 2)]);
        }

        DB::beginTransaction();
        try {
            $payment->update($request->all());

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Receive payment for a specific invoice
     */
    public function receivePayment(Request $request, Invoice $invoice)
    {
        $invoice->load('customer');
        
        return Inertia::render('payments/receive', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Store received payment
     */
    public function storeReceivedPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
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
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'completed',
                'bank_name' => $request->bank_name,
                'check_number' => $request->check_number,
                'transaction_id' => $request->transaction_id,
                'fee_amount' => $request->fee_amount ?? 0,
                'received_by' => $request->received_by,
            ]);

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment received successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to receive payment: ' . $e->getMessage()]);
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

            return Inertia::render('payments/general-payment', [
                'bankAccounts' => $bankAccounts,
                'accounts' => $accounts,
                'customers' => $customers,
            ]);
        } catch (\Exception $e) {
            // Return minimal data if there are any issues
            return Inertia::render('payments/general-payment', [
                'bankAccounts' => [
                    (object)['id' => 1, 'name' => 'Petty Cash Imprest', 'account_number' => '7055200', 'balance' => 0.00],
                ],
                'accounts' => [
                    (object)['id' => 1, 'name' => 'Office Supplies', 'type' => 'expense'],
                ],
                'customers' => [
                    (object)['id' => 1, 'name' => 'Sample Customer', 'email' => 'customer@example.com', 'address' => '123 Main St'],
                ],
            ]);
        }
    }

    /**
     * Store a general payment
     */
    public function storeGeneral(Request $request)
    {
        $request->validate([
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
        if (empty($request->expenses) && empty($request->items)) {
            return back()->withErrors(['expenses' => 'Please add at least one expense or item.']);
        }

        // Validate that the total amount matches the sum of expenses and items
        $totalExpenses = collect($request->expenses)->sum('amount');
        $totalItems = collect($request->items)->sum('amount');
        $calculatedTotal = $totalExpenses + $totalItems;
        
        if (abs($request->amount - $calculatedTotal) > 0.01) {
            return back()->withErrors(['amount' => 'Amount must match the sum of expenses and items.']);
        }

        DB::beginTransaction();
        try {
            // Create the general payment record
            $payment = Payment::create([
                'invoice_id' => null, // General payment not tied to specific invoice
                'payment_date' => $request->date,
                'payment_method' => 'check',
                'amount' => $request->amount,
                'reference' => $request->check_number,
                'notes' => $request->memo,
                'status' => 'completed',
                'bank_name' => $request->bank_account_id,
                'check_number' => $request->check_number,
                'transaction_id' => null,
                'fee_amount' => 0,
                'received_by' => $request->payee,
                'payment_type' => 'general',
                'payee' => $request->payee,
                'address' => $request->address,
                'print_later' => $request->print_later ?? false,
                'pay_online' => $request->pay_online ?? false,
            ]);

            // Store expenses as journal entries or line items
            foreach ($request->expenses as $expense) {
                if (!empty($expense['account_id']) && $expense['amount'] > 0) {
                    // Create journal entry for expense
                    \App\Models\Journal::create([
                        'date' => $request->date,
                        'description' => $expense['memo'] ?: 'General Payment - ' . $request->payee,
                        'account_id' => $expense['account_id'],
                        'debit' => $expense['amount'],
                        'credit' => 0,
                        'reference' => $request->check_number,
                        'payment_id' => $payment->id,
                    ]);
                }
            }

            // Store items as journal entries or line items
            foreach ($request->items as $item) {
                if (!empty($item['account_id']) && $item['amount'] > 0) {
                    // Create journal entry for item
                    \App\Models\Journal::create([
                        'date' => $request->date,
                        'description' => $item['memo'] ?: 'General Payment - ' . $request->payee,
                        'account_id' => $item['account_id'],
                        'debit' => $item['amount'],
                        'credit' => 0,
                        'reference' => $request->check_number,
                        'payment_id' => $payment->id,
                    ]);
                }
            }

            // Create the bank account credit entry
            \App\Models\Journal::create([
                'date' => $request->date,
                'description' => 'General Payment - ' . $request->payee,
                'account_id' => $request->bank_account_id,
                'debit' => 0,
                'credit' => $request->amount,
                'reference' => $request->check_number,
                'payment_id' => $payment->id,
            ]);

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'General payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }
}