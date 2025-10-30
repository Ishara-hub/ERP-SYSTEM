<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;

class SubAccountDetailsController extends Controller
{
    /**
     * Display detailed transactions for an account (main or sub-account)
     */
    public function show(Request $request, Account $account)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        // Load relationships
        $account->load(['parent', 'children']);

        // Get all journal entries for this account in the selected period
        $debitJournals = Journal::with(['transaction', 'creditAccount'])
            ->where('debit_account_id', $account->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $creditJournals = Journal::with(['transaction', 'debitAccount'])
            ->where('credit_account_id', $account->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Combine and sort all transactions
        $transactions = collect();

        // Add debit transactions
        foreach ($debitJournals as $journal) {
            $transactions->push([
                'id' => $journal->id,
                'date' => $journal->date,
                'description' => $journal->transaction->description ?? 'Journal Entry',
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'debit' => $journal->amount,
                'credit' => 0,
                'balance' => 0, // Will be calculated after sorting
                'counter_account' => $journal->creditAccount->account_code . ' - ' . $journal->creditAccount->account_name,
            ]);
        }

        // Add credit transactions
        foreach ($creditJournals as $journal) {
            $transactions->push([
                'id' => $journal->id,
                'date' => $journal->date,
                'description' => $journal->transaction->description ?? 'Journal Entry',
                'reference' => 'JRN-' . str_pad($journal->id, 6, '0', STR_PAD_LEFT),
                'debit' => 0,
                'credit' => $journal->amount,
                'balance' => 0, // Will be calculated after sorting
                'counter_account' => $journal->debitAccount->account_code . ' - ' . $journal->debitAccount->account_name,
            ]);
        }

        // Sort by date and journal ID
        $transactions = $transactions->sortBy([
            ['date', 'asc'],
            ['id', 'asc']
        ]);

        // Calculate running balance
        $runningBalance = $account->opening_balance ?? 0;
        $transactions = $transactions->map(function ($transaction) use (&$runningBalance, $account) {
            $runningBalance += ($transaction['debit'] - $transaction['credit']);
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        // Calculate totals
        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $netBalance = $totalDebit - $totalCredit;

        return view('accounts.sub-account-details', [
            'account' => $account,
            'transactions' => $transactions->values(),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'netBalance' => $netBalance,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
