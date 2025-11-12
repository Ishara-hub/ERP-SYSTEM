<?php

namespace App\Http\Controllers\Api\Banking;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BankReconciliationApiController extends ApiController
{
    /**
     * Display reconciliation data for a bank account.
     */
    public function index(Request $request)
    {
        try {
            $bankAccounts = Account::where('account_type', Account::ASSET)
                ->where(function ($query) {
                    $query->where('account_name', 'like', '%Bank%')
                          ->orWhere('account_name', 'like', '%bank%')
                          ->orWhere('account_name', 'like', '%Cash%');
                })
                ->get();

            $selectedAccountId = $request->get('bank_account_id');

            $bankWithdrawals = collect();
            $bankDeposits = collect();
            $unreconciledPayments = collect();

            if ($selectedAccountId) {
                $bankWithdrawals = BankTransaction::where('bank_account_id', $selectedAccountId)
                    ->where(function ($query) {
                        $query->where('reconciled', false)
                              ->orWhereNull('reconciled');
                    })
                    ->whereIn('type', ['withdrawal', 'fee', 'other'])
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $bankDeposits = BankTransaction::where('bank_account_id', $selectedAccountId)
                    ->where(function ($query) {
                        $query->where('reconciled', false)
                              ->orWhereNull('reconciled');
                    })
                    ->whereIn('type', ['deposit', 'interest'])
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $paymentIdsWithBankTransactions = BankTransaction::whereNotNull('payment_id')
                    ->where('bank_account_id', $selectedAccountId)
                    ->pluck('payment_id')
                    ->toArray();

                $unreconciledPayments = Payment::where('bank_account_id', $selectedAccountId)
                    ->where(function ($query) {
                        $query->where('reconciled', false)
                              ->orWhereNull('reconciled');
                    })
                    ->whereNotIn('id', $paymentIdsWithBankTransactions)
                    ->orderBy('payment_date', 'desc')
                    ->get();
            }

            $data = [
                'bankAccounts' => $bankAccounts,
                'selectedAccountId' => $selectedAccountId,
                'bankWithdrawals' => $bankWithdrawals,
                'bankDeposits' => $bankDeposits,
                'unreconciledPayments' => $unreconciledPayments,
            ];

            return $this->success($data, 'Reconciliation data retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve reconciliation data: ' . $e->getMessage());
        }
    }

    /**
     * Begin reconciliation (stores reconciliation metadata in session for compatibility).
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

        try {
            Session::put('reconciliation_session', true);
            Session::put('reconciliation_statement_date', $request->statement_date);
            Session::put('reconciliation_ending_balance', $request->ending_balance);
            Session::put('reconciliation_beginning_balance', 0);
            Session::put('reconciliation_service_charge', $request->service_charge ?? 0);
            Session::put('reconciliation_interest_earned', $request->interest_earned ?? 0);

            return $this->success([
                'bank_account_id' => $request->bank_account_id,
                'statement_date' => $request->statement_date,
                'ending_balance' => $request->ending_balance,
                'service_charge' => $request->service_charge ?? 0,
                'interest_earned' => $request->interest_earned ?? 0,
            ], 'Reconciliation session started successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to start reconciliation: ' . $e->getMessage());
        }
    }

    /**
     * Store bank transactions (manual entry or CSV import).
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

            if ($request->filled('csv_data')) {
                $transactions = $this->parseCSVData($request->csv_data);
            } else {
                $transactions = $request->transactions;
            }

            $created = 0;
            foreach ($transactions as $transactionData) {
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

            return $this->success(['created' => $created], "Imported {$created} bank transactions successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to import bank transactions: ' . $e->getMessage());
        }
    }

    /**
     * Reconcile transactions.
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

            if (!empty($request->payment_ids)) {
                Payment::whereIn('id', $request->payment_ids)
                    ->update([
                        'reconciled' => true,
                        'reconciled_by' => optional(auth()->user())->name,
                        'reconciled_date' => $request->reconciliation_date,
                    ]);
                $reconciled += count($request->payment_ids);
            }

            DB::commit();

            return $this->success(['reconciled' => $reconciled], "Reconciled {$reconciled} item(s) successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to reconcile: ' . $e->getMessage());
        }
    }

    /**
     * Auto-match bank transactions with payments.
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
                $match = null;
                $confidence = null;

                $exactMatch = $payments->where('amount', $transaction->amount)
                    ->where('payment_date', $transaction->transaction_date)
                    ->first();

                if ($exactMatch) {
                    $match = $exactMatch;
                    $confidence = 'exact';
                } else {
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

            return $this->success(['matched' => $matched], "Auto-matched {$matched} transaction(s) successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to auto-match: ' . $e->getMessage());
        }
    }

    /**
     * Get reconciliation summary for a date range.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
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

            return $this->success($summary, 'Reconciliation summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve reconciliation summary: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV-formatted data into an array of transactions.
     */
    private function parseCSVData(string $csvData): array
    {
        $lines = explode("\n", trim($csvData));
        $transactions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = str_getcsv($line);
            if (count($parts) < 3) {
                continue;
            }

            $transactions[] = [
                'transaction_date' => trim($parts[0]),
                'type' => trim(strtolower($parts[1])),
                'amount' => (float) trim($parts[2]),
                'description' => $parts[3] ?? null,
                'reference_number' => $parts[4] ?? null,
                'check_number' => $parts[5] ?? null,
            ];
        }

        return $transactions;
    }
}

