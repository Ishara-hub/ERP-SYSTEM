<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\Payment;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BankReconciliationController extends Controller
{
    /**
     * Display bank reconciliation page
     */
    public function index(Request $request)
    {
        $bankAccounts = Account::where('account_type', Account::ASSET)
            ->where(function($query) {
                $query->where('account_name', 'like', '%Bank%')
                      ->orWhere('account_name', 'like', '%bank%')
                      ->orWhere('account_name', 'like', '%Cash%');
            })
            ->get();

        $selectedAccountId = $request->get('bank_account_id');
        
        // Get unreconciled bank transactions (separate withdrawals and deposits)
        $bankWithdrawals = collect();
        $bankDeposits = collect();
        $unreconciledPayments = collect();
        
        if ($selectedAccountId) {
            // Get unreconciled withdrawals from bank statement
            $bankWithdrawals = BankTransaction::where('bank_account_id', $selectedAccountId)
                ->where(function($query) {
                    $query->where('reconciled', false)
                          ->orWhereNull('reconciled');
                })
                ->whereIn('type', ['withdrawal', 'fee', 'other'])
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get unreconciled deposits from bank statement
            $bankDeposits = BankTransaction::where('bank_account_id', $selectedAccountId)
                ->where(function($query) {
                    $query->where('reconciled', false)
                          ->orWhereNull('reconciled');
                })
                ->whereIn('type', ['deposit', 'interest'])
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get unreconciled payments from system
            $unreconciledPayments = Payment::where('bank_account_id', $selectedAccountId)
                ->where(function($query) {
                    $query->where('reconciled', false)
                          ->orWhereNull('reconciled');
                })
                ->orderBy('payment_date', 'desc')
                ->get();
        }

        return view('bank-reconciliation.index', compact(
            'bankAccounts',
            'selectedAccountId',
            'bankWithdrawals',
            'bankDeposits',
            'unreconciledPayments'
        ));
    }

    /**
     * Begin reconciliation - store session data
     */
    public function begin(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'statement_date' => 'required|date',
            'ending_balance' => 'required|numeric',
            'service_charge' => 'nullable|numeric',
            'service_charge_date' => 'nullable|date',
            'service_charge_account_id' => 'nullable|exists:accounts,id',
            'interest_earned' => 'nullable|numeric',
            'interest_earned_date' => 'nullable|date',
            'interest_earned_account_id' => 'nullable|exists:accounts,id',
        ]);

        // Store reconciliation data in session
        Session::put('reconciliation_session', true);
        Session::put('reconciliation_statement_date', $request->statement_date);
        Session::put('reconciliation_ending_balance', $request->ending_balance);
        Session::put('reconciliation_beginning_balance', 0); // TODO: Calculate actual beginning balance
        Session::put('reconciliation_service_charge', $request->service_charge ?? 0);
        Session::put('reconciliation_interest_earned', $request->interest_earned ?? 0);

        return redirect()->route('bank-reconciliation.index', ['bank_account_id' => $request->bank_account_id])
            ->with('success', 'Reconciliation session started. Please mark items as cleared.');
    }

    /**
     * Store bank transactions (import from statement)
     */
    public function storeBankTransactions(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'csv_data' => 'required_without:transactions|string',
            'transactions' => 'required_without:csv_data|array',
            'transactions.*.transaction_date' => 'required_with:transactions|date',
            'transactions.*.type' => 'required_with:transactions|in:deposit,withdrawal,fee,interest,other',
            'transactions.*.amount' => 'required_with:transactions|numeric|min:0',
            'transactions.*.description' => 'nullable|string',
            'transactions.*.reference_number' => 'nullable|string',
            'transactions.*.check_number' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Parse CSV if provided
            if ($request->has('csv_data') && !empty($request->csv_data)) {
                $transactions = $this->parseCSVData($request->csv_data);
            } else {
                $transactions = $request->transactions;
            }

            $created = 0;
            foreach ($transactions as $transactionData) {
                // Check if transaction already exists
                $exists = BankTransaction::where('bank_account_id', $request->bank_account_id)
                    ->where('transaction_date', $transactionData['transaction_date'])
                    ->where('amount', $transactionData['amount'])
                    ->where('type', $transactionData['type'])
                    ->exists();

                if (!$exists) {
                    BankTransaction::create([
                        'bank_account_id' => $request->bank_account_id,
                        'transaction_date' => $transactionData['transaction_date'],
                        'type' => $transactionData['type'],
                        'amount' => $transactionData['amount'],
                        'description' => $transactionData['description'] ?? null,
                        'reference_number' => $transactionData['reference_number'] ?? null,
                        'check_number' => $transactionData['check_number'] ?? null,
                        'status' => 'pending',
                    ]);
                    $created++;
                }
            }

            DB::commit();

            return redirect()->route('bank-reconciliation.index', ['bank_account_id' => $request->bank_account_id])
                ->with('success', "Successfully imported {$created} bank transactions.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to import bank transactions: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Parse CSV data into transaction array
     */
    private function parseCSVData($csvData)
    {
        $lines = explode("\n", trim($csvData));
        $transactions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode(',', $line);
            if (count($parts) < 3) continue;

            $transactions[] = [
                'transaction_date' => trim($parts[0]),
                'type' => trim(strtolower($parts[1])),
                'amount' => floatval(trim($parts[2])),
                'description' => isset($parts[3]) ? trim($parts[3]) : null,
                'reference_number' => isset($parts[4]) ? trim($parts[4]) : null,
                'check_number' => isset($parts[5]) ? trim($parts[5]) : null,
            ];
        }

        return $transactions;
    }

    /**
     * Reconcile transactions
     */
    public function reconcile(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'bank_transaction_ids' => 'nullable|array',
            'payment_ids' => 'nullable|array',
            'reconciliation_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $reconciled = 0;

            // Reconcile bank transactions
            if (!empty($request->bank_transaction_ids)) {
                BankTransaction::whereIn('id', $request->bank_transaction_ids)
                    ->update([
                        'reconciled' => true,
                        'reconciled_by' => auth()->id(),
                        'reconciled_at' => now(),
                        'status' => 'reconciled',
                    ]);
                $reconciled += count($request->bank_transaction_ids);
            }

            // Reconcile payments
            if (!empty($request->payment_ids)) {
                Payment::whereIn('id', $request->payment_ids)
                    ->update([
                        'reconciled' => true,
                        'reconciled_by' => auth()->user()->name,
                        'reconciled_date' => $request->reconciliation_date,
                    ]);
                $reconciled += count($request->payment_ids);
            }

            DB::commit();

            return redirect()->route('bank-reconciliation.index', ['bank_account_id' => $request->bank_account_id])
                ->with('success', "Successfully reconciled {$reconciled} item(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reconcile: ' . $e->getMessage());
        }
    }

    /**
     * Auto-match bank transactions with payments
     */
    public function autoMatch(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $bankTransactions = BankTransaction::where('bank_account_id', $request->bank_account_id)
                ->where('reconciled', false)
                ->get();

            $payments = Payment::where('bank_account_id', $request->bank_account_id)
                ->where('reconciled', false)
                ->get();

            $matched = 0;

            foreach ($bankTransactions as $transaction) {
                // Try to find matching payment
                $match = null;
                $confidence = null;

                // Exact match by amount and date
                $exactMatch = $payments->where('amount', $transaction->amount)
                    ->where('payment_date', $transaction->transaction_date)
                    ->first();

                if ($exactMatch) {
                    $match = $exactMatch;
                    $confidence = 'exact';
                } else {
                    // High confidence match by amount (within 7 days)
                    $highMatch = $payments->where('amount', $transaction->amount)
                        ->whereBetween('payment_date', [
                            $transaction->transaction_date->copy()->subDays(7),
                            $transaction->transaction_date->copy()->addDays(7)
                        ])
                        ->first();

                    if ($highMatch) {
                        $match = $highMatch;
                        $confidence = 'high';
                    }
                }

                if ($match) {
                    $transaction->update([
                        'payment_id' => $match->id,
                        'matched_amount' => $match->amount,
                        'match_confidence' => $confidence,
                    ]);
                    $matched++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Auto-matched {$matched} bank transactions.",
                'matched' => $matched,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-match: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get reconciliation summary
     */
    public function summary(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $bankTransactions = BankTransaction::where('bank_account_id', $request->bank_account_id)
            ->whereBetween('transaction_date', [$request->start_date, $request->end_date])
            ->get();

        $payments = Payment::where('bank_account_id', $request->bank_account_id)
            ->whereBetween('payment_date', [$request->start_date, $request->end_date])
            ->get();

        $summary = [
            'bank_deposits' => $bankTransactions->where('type', 'deposit')->sum('amount'),
            'bank_withdrawals' => $bankTransactions->where('type', 'withdrawal')->sum('amount'),
            'system_received' => $payments->where('payment_type', 'received')->sum('amount'),
            'system_paid' => $payments->where('payment_type', 'paid')->sum('amount'),
            'unreconciled_transactions' => $bankTransactions->where('reconciled', false)->count(),
            'unreconciled_payments' => $payments->where('reconciled', false)->count(),
        ];

        return response()->json($summary);
    }
}
