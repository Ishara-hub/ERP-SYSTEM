<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrialBalanceController extends Controller
{
    /**
     * Display Trial Balance Report
     */
    public function index(Request $request)
    {
        $dateAsOf = $request->get('as_of', date('Y-m-d'));

        // Get all active accounts
        $accounts = Account::where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        // Get all journal entries up to the specified date
        $journals = Journal::where('date', '<=', $dateAsOf)->get();

        $accountBalances = [];
        foreach ($journals as $journal) {
            // Debit side
            if (!isset($accountBalances[$journal->debit_account_id])) {
                $accountBalances[$journal->debit_account_id] = ['debit' => 0, 'credit' => 0];
            }
            $accountBalances[$journal->debit_account_id]['debit'] += $journal->amount;

            // Credit side
            if (!isset($accountBalances[$journal->credit_account_id])) {
                $accountBalances[$journal->credit_account_id] = ['debit' => 0, 'credit' => 0];
            }
            $accountBalances[$journal->credit_account_id]['credit'] += $journal->amount;
        }

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit = ($accountBalances[$account->id]['debit'] ?? 0) + ($account->opening_balance ?? 0);
            $credit = $accountBalances[$account->id]['credit'] ?? 0;

            if ($debit == 0 && $credit == 0) continue;

            $normalBalance = $this->getNormalBalance($account->account_type);
            
            $rowDebit = 0;
            $rowCredit = 0;

            if ($debit > $credit) {
                $rowDebit = $debit - $credit;
            } else {
                $rowCredit = $credit - $debit;
            }

            $trialBalance[] = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'debit' => $rowDebit,
                'credit' => $rowCredit,
            ];

            $totalDebit += $rowDebit;
            $totalCredit += $rowCredit;
        }

        return view('accounts.reports.trial-balance', [
            'trialBalance' => $trialBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'dateAsOf' => $dateAsOf,
        ]);
    }

    private function getNormalBalance(string $accountType): string
    {
        return match($accountType) {
            Account::ASSET, Account::EXPENSE => 'debit',
            Account::LIABILITY, Account::EQUITY, Account::INCOME => 'credit',
            default => 'debit'
        };
    }
}

