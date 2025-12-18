<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerPaymentController extends Controller
{
    /**
     * Show the customer payment form
     */
    public function index()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        
        // Get A/R accounts
        $arAccounts = Account::where('account_type', 'Accounts Receivable')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        // Get deposit accounts (Bank accounts that are sub-accounts)
        $depositAccounts = Account::where('account_type', 'Bank')
            ->whereNotNull('parent_id')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        return view('payments.customer-payment', compact('customers', 'arAccounts', 'depositAccounts'));
    }

    /**
     * Get unpaid invoices for a customer
     */
    public function getCustomerInvoices(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        
        if (!$customerId) {
            return response()->json(['invoices' => [], 'balance' => 0]);
        }
        
        $invoices = Invoice::where('customer_id', $customerId)
            ->where('balance_due', '>', 0)
            ->orderBy('date')
            ->get(['id', 'invoice_no', 'date', 'total_amount', 'balance_due']);
        
        $totalBalance = $invoices->sum('balance_due');
        
        return response()->json([
            'invoices' => $invoices,
            'balance' => $totalBalance
        ]);
    }

    /**
     * Store the customer payment
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'ar_account_id' => 'nullable|exists:accounts,id',
            'deposit_to_account_id' => 'nullable|exists:accounts,id',
            'memo' => 'nullable|string',
            'invoices' => 'required|array|min:1',
            'invoices.*.invoice_id' => 'required|exists:invoices,id',
            'invoices.*.payment_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Validate total payment matches sum of invoice payments
        $totalInvoicePayments = collect($request->invoices)->sum('payment_amount');
        if (abs($request->payment_amount - $totalInvoicePayments) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount must equal sum of invoice payments'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($request->customer_id);
            $arAccount = Account::findOrFail($request->ar_account_id);
            $depositToAccount = Account::findOrFail($request->deposit_to_account_id);

            // Double-Entry Accounting
            // Entry: Debit Deposit To (Bank/Undeposited), Credit Accounts Receivable
            $mainTransaction = Transaction::create([
                'account_id' => $depositToAccount->id,
                'type' => 'debit',
                'amount' => $request->payment_amount,
                'description' => "Payment received from {$customer->name}" . ($request->reference_number ? " (Ref: {$request->reference_number})" : ""),
                'transaction_date' => $request->payment_date,
            ]);

            Journal::create([
                'transaction_id' => $mainTransaction->id,
                'debit_account_id' => $depositToAccount->id,
                'credit_account_id' => $arAccount->id,
                'amount' => $request->payment_amount,
                'date' => $request->payment_date,
            ]);

            // Update account balances
            $depositToAccount->increment('current_balance', $request->payment_amount);
            $arAccount->decrement('current_balance', $request->payment_amount);

            $payments = [];
            
            foreach ($request->invoices as $invoicePayment) {
                if ($invoicePayment['payment_amount'] <= 0) {
                    continue;
                }
                
                $invoice = Invoice::findOrFail($invoicePayment['invoice_id']);
                
                // Validate payment doesn't exceed balance
                if ($invoicePayment['payment_amount'] > $invoice->balance_due) {
                    throw new \Exception("Payment amount exceeds balance due for invoice {$invoice->invoice_no}");
                }
                
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'amount' => $invoicePayment['payment_amount'],
                    'reference' => $request->reference_number,
                    'notes' => $request->memo,
                    'status' => 'completed',
                    'bank_account_id' => $request->deposit_to_account_id,
                    'transaction_id' => $mainTransaction->id, // Link to accounting transaction
                ]);
                
                // Update invoice status if fully paid
                $invoice->refresh();
                if ($invoice->balance_due <= 0) {
                    $invoice->update(['status' => 'paid']);
                }
                
                $payments[] = $payment;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payments' => $payments
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Customer Payment Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }
}

