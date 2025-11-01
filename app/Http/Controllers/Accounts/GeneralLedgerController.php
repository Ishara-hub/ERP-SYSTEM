<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Models\GeneralJournal;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    /**
     * Display the General Ledger
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $accountId = $request->get('account_id');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $ledgerEntries = collect();

        // 1. Get entries from journals + transactions (system/automated entries)
        $journalsQuery = Journal::with([
            'debitAccount.parent',
            'creditAccount.parent',
            'transaction'
        ])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id');

        if ($accountId) {
            $journalsQuery->where(function ($q) use ($accountId) {
                $q->where('debit_account_id', $accountId)
                  ->orWhere('credit_account_id', $accountId);
            });
        }

        $journals = $journalsQuery->get();

        foreach ($journals as $journal) {
            $debitAccount = $journal->debitAccount;
            $creditAccount = $journal->creditAccount;
            $transaction = $journal->transaction;

            // Debit entry
            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'source' => 'Journal',
                'account_id' => $journal->debit_account_id,
                'account_code' => $debitAccount->account_code ?? '',
                'account_name' => $debitAccount->account_name ?? '',
                'sub_account_code' => $debitAccount->parent ? ($debitAccount->parent->account_code ?? null) : null,
                'sub_account_name' => $debitAccount->parent ? ($debitAccount->parent->account_name ?? null) : null,
                'debit' => $journal->amount,
                'credit' => 0,
                'description' => $transaction->description ?? 'Journal Entry',
            ]);

            // Credit entry
            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'source' => 'Journal',
                'account_id' => $journal->credit_account_id,
                'account_code' => $creditAccount->account_code ?? '',
                'account_name' => $creditAccount->account_name ?? '',
                'sub_account_code' => $creditAccount->parent ? ($creditAccount->parent->account_code ?? null) : null,
                'sub_account_name' => $creditAccount->parent ? ($creditAccount->parent->account_name ?? null) : null,
                'debit' => 0,
                'credit' => $journal->amount,
                'description' => $transaction->description ?? 'Journal Entry',
            ]);
        }

        // 2. Get entries from general_journals + journal_entries (manual entries)
        $generalJournalsQuery = GeneralJournal::with(['entries.account.parent', 'user'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->orderBy('transaction_date')
            ->orderBy('id');

        $generalJournals = $generalJournalsQuery->get();

        foreach ($generalJournals as $generalJournal) {
            foreach ($generalJournal->entries as $entry) {
                $account = $entry->account;
                
                // Skip if filtering by account and this entry doesn't match
                if ($accountId && $entry->account_id != $accountId) {
                    continue;
                }

                $ledgerEntries->push([
                    'date' => $generalJournal->transaction_date,
                    'reference' => $generalJournal->reference,
                    'source' => 'Manual Entry',
                    'account_id' => $entry->account_id,
                    'account_code' => $account->account_code ?? '',
                    'account_name' => $account->account_name ?? '',
                    'sub_account_code' => $account->parent ? ($account->parent->account_code ?? null) : null,
                    'sub_account_name' => $account->parent ? ($account->parent->account_name ?? null) : null,
                    'debit' => $entry->debit,
                    'credit' => $entry->credit,
                    'description' => $entry->description ?? $generalJournal->description,
                ]);
            }
        }

        // 3. Get entries from payments (if they have account relationships)
        $paymentsQuery = Payment::with(['bankAccount', 'expenseAccount', 'invoice', 'purchaseOrder'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date')
            ->orderBy('id');

        $payments = $paymentsQuery->get();

        foreach ($payments as $payment) {
            // Only process payments that have account relationships
            if ($payment->bankAccount || $payment->expenseAccount) {
                $reference = 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
                
                if ($payment->payment_type === 'paid' && $payment->expenseAccount && $payment->bankAccount) {
                    // Payment out: Debit Expense, Credit Bank
                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_id' => $payment->expense_account_id,
                        'account_code' => $payment->expenseAccount->account_code ?? '',
                        'account_name' => $payment->expenseAccount->account_name ?? '',
                        'sub_account_code' => $payment->expenseAccount->parent ? ($payment->expenseAccount->parent->account_code ?? null) : null,
                        'sub_account_name' => $payment->expenseAccount->parent ? ($payment->expenseAccount->parent->account_name ?? null) : null,
                        'debit' => $payment->amount,
                        'credit' => 0,
                        'description' => 'Payment: ' . ($payment->notes ?? 'Payment'),
                    ]);

                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_id' => $payment->bank_account_id,
                        'account_code' => $payment->bankAccount->account_code ?? '',
                        'account_name' => $payment->bankAccount->account_name ?? '',
                        'sub_account_code' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_code ?? null) : null,
                        'sub_account_name' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_name ?? null) : null,
                        'debit' => 0,
                        'credit' => $payment->amount,
                        'description' => 'Payment: ' . ($payment->notes ?? 'Payment'),
                    ]);
                } elseif ($payment->payment_type === 'received' && $payment->bankAccount) {
                    // Payment in: Debit Bank, Credit Income
                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_id' => $payment->bank_account_id,
                        'account_code' => $payment->bankAccount->account_code ?? '',
                        'account_name' => $payment->bankAccount->account_name ?? '',
                        'sub_account_code' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_code ?? null) : null,
                        'sub_account_name' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_name ?? null) : null,
                        'debit' => $payment->amount,
                        'credit' => 0,
                        'description' => 'Receipt: ' . ($payment->notes ?? 'Receipt'),
                    ]);

                    // Note: We'd need an income_account_id on payments to properly credit income
                    // For now, we'll skip the credit side or use a default account
                }
            }
        }

        // Calculate running balance for each account
        $runningBalances = [];
        $ledgerEntries = $ledgerEntries->sortBy([
            ['account_code', 'asc'],
            ['date', 'asc'],
            ['id', 'asc']
        ]);

        foreach ($ledgerEntries as &$entry) {
            $accountId = $entry['account_id'];
            if (!isset($runningBalances[$accountId])) {
                $account = Account::find($accountId);
                $runningBalances[$accountId] = $account->opening_balance ?? 0;
            }

            // Calculate balance based on account type
            $account = Account::find($accountId);
            $normalBalance = $this->getNormalBalance($account->account_type ?? '');

            if ($normalBalance === 'debit') {
                $runningBalances[$accountId] += $entry['debit'] - $entry['credit'];
            } else {
                $runningBalances[$accountId] += $entry['credit'] - $entry['debit'];
            }

            $entry['balance'] = $runningBalances[$accountId];
        }

        // If filtering by account, group by date; otherwise, sort by account then date
        if ($accountId) {
            $ledgerEntries = $ledgerEntries->sortBy([
                ['date', 'asc'],
                ['account_code', 'asc']
            ]);
        }

        // Get accounts for dropdown filter
        $accounts = Account::where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        // Calculate summary totals
        $totalDebit = $ledgerEntries->sum('debit');
        $totalCredit = $ledgerEntries->sum('credit');
        $balance = $totalDebit - $totalCredit;

        return view('accounts.general-ledger', [
            'ledgerEntries' => $ledgerEntries->values(),
            'accounts' => $accounts,
            'selectedAccountId' => $accountId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $balance,
        ]);
    }

    /**
     * Get normal balance for account type
     */
    private function getNormalBalance(string $accountType): string
    {
        return match($accountType) {
            Account::ASSET, Account::EXPENSE => 'debit',
            Account::LIABILITY, Account::EQUITY, Account::INCOME => 'credit',
            default => 'debit'
        };
    }

    /**
     * Export general ledger to CSV
     */
    public function export(Request $request)
    {
        // Use the same logic as index() to get all entries
        $accountId = $request->get('account_id');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $ledgerEntries = collect();

        // 1. Get entries from journals
        $journalsQuery = Journal::with([
            'debitAccount.parent',
            'creditAccount.parent',
            'transaction'
        ])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id');

        if ($accountId) {
            $journalsQuery->where(function ($q) use ($accountId) {
                $q->where('debit_account_id', $accountId)
                  ->orWhere('credit_account_id', $accountId);
            });
        }

        $journals = $journalsQuery->get();

        foreach ($journals as $journal) {
            $debitAccount = $journal->debitAccount;
            $creditAccount = $journal->creditAccount;
            $transaction = $journal->transaction;

            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'source' => 'Journal',
                'account_code' => $debitAccount->account_code ?? '',
                'account_name' => $debitAccount->account_name ?? '',
                'parent_account' => $debitAccount->parent ? ($debitAccount->parent->account_code . ' - ' . $debitAccount->parent->account_name) : '',
                'debit' => $journal->amount,
                'credit' => 0,
                'description' => $transaction->description ?? 'Journal Entry',
            ]);

            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'source' => 'Journal',
                'account_code' => $creditAccount->account_code ?? '',
                'account_name' => $creditAccount->account_name ?? '',
                'parent_account' => $creditAccount->parent ? ($creditAccount->parent->account_code . ' - ' . $creditAccount->parent->account_name) : '',
                'debit' => 0,
                'credit' => $journal->amount,
                'description' => $transaction->description ?? 'Journal Entry',
            ]);
        }

        // 2. Get entries from general_journals
        $generalJournalsQuery = GeneralJournal::with(['entries.account.parent', 'user'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->orderBy('transaction_date')
            ->orderBy('id');

        $generalJournals = $generalJournalsQuery->get();

        foreach ($generalJournals as $generalJournal) {
            foreach ($generalJournal->entries as $entry) {
                $account = $entry->account;
                
                if ($accountId && $entry->account_id != $accountId) {
                    continue;
                }

                $ledgerEntries->push([
                    'date' => $generalJournal->transaction_date,
                    'reference' => $generalJournal->reference,
                    'source' => 'Manual Entry',
                    'account_code' => $account->account_code ?? '',
                    'account_name' => $account->account_name ?? '',
                    'parent_account' => $account->parent ? ($account->parent->account_code . ' - ' . $account->parent->account_name) : '',
                    'debit' => $entry->debit,
                    'credit' => $entry->credit,
                    'description' => $entry->description ?? $generalJournal->description,
                ]);
            }
        }

        // 3. Get entries from payments
        $paymentsQuery = Payment::with(['bankAccount', 'expenseAccount', 'invoice', 'purchaseOrder'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date')
            ->orderBy('id');

        $payments = $paymentsQuery->get();

        foreach ($payments as $payment) {
            if ($payment->bankAccount || $payment->expenseAccount) {
                $reference = 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
                
                if ($payment->payment_type === 'paid' && $payment->expenseAccount && $payment->bankAccount) {
                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_code' => $payment->expenseAccount->account_code ?? '',
                        'account_name' => $payment->expenseAccount->account_name ?? '',
                        'parent_account' => $payment->expenseAccount->parent ? ($payment->expenseAccount->parent->account_code . ' - ' . $payment->expenseAccount->parent->account_name) : '',
                        'debit' => $payment->amount,
                        'credit' => 0,
                        'description' => 'Payment: ' . ($payment->notes ?? 'Payment'),
                    ]);

                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_code' => $payment->bankAccount->account_code ?? '',
                        'account_name' => $payment->bankAccount->account_name ?? '',
                        'parent_account' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_code . ' - ' . $payment->bankAccount->parent->account_name) : '',
                        'debit' => 0,
                        'credit' => $payment->amount,
                        'description' => 'Payment: ' . ($payment->notes ?? 'Payment'),
                    ]);
                } elseif ($payment->payment_type === 'received' && $payment->bankAccount) {
                    $ledgerEntries->push([
                        'date' => $payment->payment_date,
                        'reference' => $reference,
                        'source' => 'Payment',
                        'account_code' => $payment->bankAccount->account_code ?? '',
                        'account_name' => $payment->bankAccount->account_name ?? '',
                        'parent_account' => $payment->bankAccount->parent ? ($payment->bankAccount->parent->account_code . ' - ' . $payment->bankAccount->parent->account_name) : '',
                        'debit' => $payment->amount,
                        'credit' => 0,
                        'description' => 'Receipt: ' . ($payment->notes ?? 'Receipt'),
                    ]);
                }
            }
        }

        // Sort entries
        $ledgerEntries = $ledgerEntries->sortBy([
            ['account_code', 'asc'],
            ['date', 'asc'],
        ]);

        $filename = 'general_ledger_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($ledgerEntries) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Reference',
                'Source',
                'Account Code',
                'Account Name',
                'Parent Account',
                'Debit',
                'Credit',
                'Description',
            ]);

            foreach ($ledgerEntries as $entry) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($entry['date'])->format('Y-m-d'),
                    $entry['reference'],
                    $entry['source'],
                    $entry['account_code'],
                    $entry['account_name'],
                    $entry['parent_account'] ?? '',
                    number_format($entry['debit'], 2),
                    number_format($entry['credit'], 2),
                    $entry['description'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
