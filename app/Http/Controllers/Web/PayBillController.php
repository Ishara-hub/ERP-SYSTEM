<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayBillController extends Controller
{
    /**
     * Display a listing of bill payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['bill.supplier', 'paymentCategory'])
                        ->whereNotNull('bill_id');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('bill', function ($billQuery) use ($request) {
                      $billQuery->where('bill_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Payment method filter
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        return view('bills.pay-bill.index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status', 'payment_method', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new bill payment.
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $liabilityAccounts = Account::where('account_type', 'Liability')->orderBy('account_name')->get();
        $bankAccounts = Account::where('account_type', 'Asset')
                              ->where('account_name', 'like', '%bank%')
                              ->orWhere('account_name', 'like', '%cash%')
                              ->orderBy('account_name')
                              ->get();

        $unpaidBills = collect();
        $selectedSupplier = null;
        $selectedLiabilityAccount = null;

        // If supplier and liability account are selected, get unpaid bills
        if ($request->has('supplier_id') && $request->has('liability_account_id')) {
            $unpaidBills = Bill::with(['supplier', 'liabilityAccount'])
                ->where('supplier_id', $request->supplier_id)
                ->where('liability_account_id', $request->liability_account_id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('bill_date', 'asc')
                ->get();

            $selectedSupplier = Supplier::find($request->supplier_id);
            $selectedLiabilityAccount = Account::find($request->liability_account_id);
        }

        return view('bills.pay-bill.create', [
            'suppliers' => $suppliers,
            'liabilityAccounts' => $liabilityAccounts,
            'bankAccounts' => $bankAccounts,
            'unpaidBills' => $unpaidBills,
            'selectedSupplier' => $selectedSupplier,
            'selectedLiabilityAccount' => $selectedLiabilityAccount,
            'filters' => $request->only(['supplier_id', 'liability_account_id'])
        ]);
    }

    /**
     * Store a newly created bill payment.
     */
    public function store(Request $request)
    {
        // Log the request data for debugging
        \Log::info('PayBill store request', [
            'all_data' => $request->all(),
            'selected_bills' => $request->selected_bills,
            'payment_amount' => $request->payment_amount,
            'has_selected_bills' => $request->has('selected_bills'),
            'has_payment_amount' => $request->has('payment_amount'),
        ]);
        
        // Also log to Laravel log file
        logger('PayBill Request Data', [
            'all' => $request->all(),
        ]);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
            'bank_account_id' => 'required|exists:accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
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

            // Validate bill amounts
            foreach ($request->selected_bills as $billId) {
                $bill = Bill::findOrFail($billId);
                $amount = $request->payment_amount[$billId] ?? 0;
                
                if ($amount <= 0) {
                    return back()->withErrors([
                        'bills' => "Payment amount for bill {$bill->bill_number} must be greater than zero"
                    ]);
                }

                if ($amount > $bill->balance_due) {
                    return back()->withErrors([
                        'bills' => "Payment amount for bill {$bill->bill_number} cannot exceed balance due of $" . number_format($bill->balance_due, 2)
                    ]);
                }

                $totalAmount += $amount;
                $billPayments[] = [
                    'bill' => $bill,
                    'amount' => $amount
                ];
            }

            // Create payment record
            $payment = Payment::create([
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $totalAmount,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'completed',
                'payment_type' => 'bill_payment',
                'payment_category_id' => 1, // Vendor Payments
                'bank_account_id' => $request->bank_account_id,
                'received_by' => auth()->user()->name,
            ]);

            // Process each bill payment
            foreach ($billPayments as $billPayment) {
                $bill = $billPayment['bill'];
                $amount = $billPayment['amount'];

                // Update bill payment amount
                $bill->increment('paid_amount', $amount);
                $bill->refresh();
                $bill->calculateTotals();
                $bill->save();

                // Create journal entries for this bill payment
                $this->createPaymentJournalEntries($payment, $bill, $amount, $request->bank_account_id, $request->liability_account_id);
            }

            DB::commit();

            return redirect()->route('bills.pay-bill.show', $payment)
                ->with('success', 'Bill payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified bill payment.
     */
    public function show(Payment $pay_bill)
    {
        $pay_bill->load(['bill.supplier', 'paymentCategory', 'bankAccount']);

        return view('bills.pay-bill.show', [
            'payment' => $pay_bill
        ]);
    }

    /**
     * Print payment voucher.
     */
    public function printVoucher(Payment $payment)
    {
        $payment->load(['bill.supplier', 'paymentCategory', 'bankAccount']);

        return view('bills.pay-bill.voucher', [
            'payment' => $payment
        ]);
    }

    /**
     * Get unpaid bills for selected supplier and liability account.
     */
    public function getUnpaidBills(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'liability_account_id' => 'required|exists:accounts,id',
        ]);

        $bills = Bill::with(['supplier', 'liabilityAccount'])
            ->where('supplier_id', $request->supplier_id)
            ->where('liability_account_id', $request->liability_account_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('bill_date', 'asc')
            ->get();

        return response()->json($bills);
    }

    /**
     * Create journal entries for bill payment.
     */
    private function createPaymentJournalEntries(Payment $payment, Bill $bill, $amount, $bankAccountId, $liabilityAccountId)
    {
        // Create a transaction record first
        $transaction = \App\Models\Transaction::create([
            'account_id' => $liabilityAccountId,
            'type' => 'debit',
            'amount' => $amount,
            'description' => "Payment for bill {$bill->bill_number} - {$bill->supplier->name}",
            'transaction_date' => $payment->payment_date,
        ]);

        // Create journal entry (debit liability, credit bank)
        Journal::create([
            'transaction_id' => $transaction->id,
            'debit_account_id' => $liabilityAccountId,
            'credit_account_id' => $bankAccountId,
            'amount' => $amount,
            'date' => $payment->payment_date,
        ]);
    }
}