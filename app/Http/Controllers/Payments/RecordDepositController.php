<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Payment;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordDepositController extends Controller
{
    /**
     * Show the record deposit form
     */
    public function index()
    {
        // Get bank accounts (account_type = 'Bank' and is a sub-account)
        $bankAccounts = Account::where('account_type', 'Bank')
            ->whereNotNull('parent_id')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();

        // Get payments that are not yet deposited (not linked to a BankTransaction)
        $undepositedPayments = Payment::whereNull('transaction_id')
            ->where('status', 'completed')
            ->with(['invoice.customer'])
            ->orderBy('payment_date')
            ->get();

        return view('payments.record-deposit', compact('bankAccounts', 'undepositedPayments'));
    }

    /**
     * Store the deposit
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'deposit_date' => 'required|date',
            'memo' => 'nullable|string|max:255',
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'exists:payments,id',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Calculate actual total from selected payments to verify
            $payments = Payment::whereIn('id', $request->payment_ids)->get();
            $calculatedTotal = $payments->sum('amount');

            if (abs($calculatedTotal - $request->total_amount) > 0.01) {
                throw new \Exception("Total amount mismatch. Calculated: {$calculatedTotal}, Received: {$request->total_amount}");
            }

            // Create a single bank transaction for the entire deposit
            $bankTransaction = BankTransaction::create([
                'bank_account_id' => $request->bank_account_id,
                'transaction_date' => $request->deposit_date,
                'type' => BankTransaction::TYPE_DEPOSIT,
                'amount' => $calculatedTotal,
                'description' => $request->memo ?? 'Bank Deposit',
                'status' => BankTransaction::STATUS_PENDING,
                'reconciled' => false,
            ]);

            // Link payments to this transaction
            Payment::whereIn('id', $request->payment_ids)->update([
                'transaction_id' => $bankTransaction->id,
                'status' => 'completed'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit recorded successfully',
                'transaction_id' => $bankTransaction->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Record Deposit Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record deposit: ' . $e->getMessage()
            ], 500);
        }
    }
}

