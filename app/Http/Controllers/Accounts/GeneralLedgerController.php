<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
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

        // Build query for general ledger entries
        $query = Journal::with([
            'debitAccount.parent',
            'creditAccount.parent',
            'transaction'
        ])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id');

        // Filter by account if specified
        if ($accountId) {
            $query->where(function ($q) use ($accountId) {
                $q->where('debit_account_id', $accountId)
                  ->orWhere('credit_account_id', $accountId);
            });
        }

        $journals = $query->get();

        // Build general ledger entries (both debit and credit sides)
        $ledgerEntries = collect();

        foreach ($journals as $journal) {
            // Get sub-account info if exists
            $debitAccount = $journal->debitAccount;
            $creditAccount = $journal->creditAccount;
            $transaction = $journal->transaction;

            // Debit entry
            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'transaction_id' => $journal->transaction_id,
                'journal_id' => $journal->id,
                'account_id' => $journal->debit_account_id,
                'account_code' => $debitAccount->account_code ?? '',
                'account_name' => $debitAccount->account_name ?? '',
                'sub_account_code' => $debitAccount->parent ? ($debitAccount->parent->account_code ?? null) : null,
                'sub_account_name' => $debitAccount->parent ? ($debitAccount->parent->account_name ?? null) : null,
                'debit' => $journal->amount,
                'credit' => 0,
                'description' => $transaction->description ?? 'Journal Entry',
                'type' => 'debit',
            ]);

            // Credit entry
            $ledgerEntries->push([
                'date' => $journal->date,
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'transaction_id' => $journal->transaction_id,
                'journal_id' => $journal->id,
                'account_id' => $journal->credit_account_id,
                'account_code' => $creditAccount->account_code ?? '',
                'account_name' => $creditAccount->account_name ?? '',
                'sub_account_code' => $creditAccount->parent ? ($creditAccount->parent->account_code ?? null) : null,
                'sub_account_name' => $creditAccount->parent ? ($creditAccount->parent->account_name ?? null) : null,
                'debit' => 0,
                'credit' => $journal->amount,
                'description' => $transaction->description ?? 'Journal Entry',
                'type' => 'credit',
            ]);
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
        // Similar query as index but for export
        $accountId = $request->get('account_id');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $query = Journal::with([
            'debitAccount.parent',
            'creditAccount.parent',
            'transaction'
        ])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id');

        if ($accountId) {
            $query->where(function ($q) use ($accountId) {
                $q->where('debit_account_id', $accountId)
                  ->orWhere('credit_account_id', $accountId);
            });
        }

        $journals = $query->get();

        $filename = 'general_ledger_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($journals) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Reference',
                'Account Code',
                'Account Name',
                'Parent Account',
                'Debit',
                'Credit',
                'Description',
            ]);

            foreach ($journals as $journal) {
                $debitAccount = $journal->debitAccount;
                $creditAccount = $journal->creditAccount;
                
                // Debit row
                fputcsv($file, [
                    $journal->date->format('Y-m-d'),
                    'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                    $debitAccount->account_code ?? '',
                    $debitAccount->account_name ?? '',
                    $debitAccount->parent ? ($debitAccount->parent->account_code . ' - ' . $debitAccount->parent->account_name) : '',
                    number_format($journal->amount, 2),
                    '0.00',
                    $journal->transaction->description ?? 'Journal Entry',
                ]);

                // Credit row
                fputcsv($file, [
                    $journal->date->format('Y-m-d'),
                    'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                    $creditAccount->account_code ?? '',
                    $creditAccount->account_name ?? '',
                    $creditAccount->parent ? ($creditAccount->parent->account_code . ' - ' . $creditAccount->parent->account_name) : '',
                    '0.00',
                    number_format($journal->amount, 2),
                    $journal->transaction->description ?? 'Journal Entry',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
