<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
    /**
     * Display Balance Sheet
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $dateTo = $request->get('date_to', date('Y-m-d'));
        $dateFrom = $request->get('date_from', date('Y-01-01')); // Optional: for P&L calculation

        // Get all accounts for balance sheet types (Assets, Liabilities, Equity)
        $accounts = Account::whereIn('account_type', [
            Account::ASSET,
            Account::LIABILITY,
            Account::EQUITY
        ])
            ->where('is_active', true)
            ->with(['parent', 'children'])
            ->orderBy('account_type')
            ->orderBy('sort_order')
            ->orderBy('account_code')
            ->get();

        // Get all journal entries up to the balance sheet date
        $journals = Journal::with(['debitAccount', 'creditAccount', 'transaction'])
            ->where('date', '<=', $dateTo)
            ->get();

        // Calculate transaction totals for each account
        $accountTransactions = [];
        foreach ($journals as $journal) {
            // Debit side
            if ($journal->debitAccount) {
                $accountId = $journal->debit_account_id;
                if (!isset($accountTransactions[$accountId])) {
                    $accountTransactions[$accountId] = ['debit' => 0, 'credit' => 0];
                }
                $accountTransactions[$accountId]['debit'] += $journal->amount;
            }

            // Credit side
            if ($journal->creditAccount) {
                $accountId = $journal->credit_account_id;
                if (!isset($accountTransactions[$accountId])) {
                    $accountTransactions[$accountId] = ['debit' => 0, 'credit' => 0];
                }
                $accountTransactions[$accountId]['credit'] += $journal->amount;
            }
        }

        // Group accounts by type
        $groupedData = [];
        $categoryTotals = [
            'Assets' => 0,
            'Liability' => 0,
            'Equity' => 0,
        ];

        foreach ([Account::ASSET, Account::LIABILITY, Account::EQUITY] as $typeKey) {
            $typeName = $typeKey === Account::ASSET ? 'Assets' : ($typeKey === Account::LIABILITY ? 'Liability' : 'Equity');
            
            $parentAccounts = $accounts->where('account_type', $typeKey)
                ->whereNull('parent_id')
                ->sortBy(['sort_order', 'account_code']);

            if ($parentAccounts->isEmpty()) {
                continue;
            }

            $typeData = [];

            foreach ($parentAccounts as $parentAccount) {
                $debit = ($accountTransactions[$parentAccount->id]['debit'] ?? 0) + ($parentAccount->opening_balance ?? 0);
                $credit = $accountTransactions[$parentAccount->id]['credit'] ?? 0;

                // Calculate balance based on account type
                // Assets: debit balance (positive), Liabilities/Equity: credit balance (positive)
                if ($typeKey === Account::ASSET || $typeKey === Account::EXPENSE) {
                    $balance = $debit - $credit;
                } else {
                    $balance = $credit - $debit;
                }

                // Get sub-accounts
                $subAccounts = $accounts->where('parent_id', $parentAccount->id)
                    ->sortBy(['sort_order', 'account_code']);

                $subAccountsData = [];
                $parentTotalBalance = $balance;

                foreach ($subAccounts as $subAccount) {
                    $subDebit = ($accountTransactions[$subAccount->id]['debit'] ?? 0) + ($subAccount->opening_balance ?? 0);
                    $subCredit = $accountTransactions[$subAccount->id]['credit'] ?? 0;

                    if ($typeKey === Account::ASSET || $typeKey === Account::EXPENSE) {
                        $subBalance = $subDebit - $subCredit;
                    } else {
                        $subBalance = $subCredit - $subDebit;
                    }

                    $subAccountsData[] = [
                        'sub_account_code' => $subAccount->account_code,
                        'sub_account_name' => $subAccount->account_name,
                        'balance' => $subBalance,
                    ];

                    $parentTotalBalance += $subBalance;
                }

                $typeData[$parentAccount->account_name] = [
                    'account_code' => $parentAccount->account_code,
                    'sub_accounts' => $subAccountsData,
                    'balance' => $subAccounts->isEmpty() ? $balance : null,
                    'total_balance' => $parentTotalBalance,
                ];

                $categoryTotals[$typeName] += $parentTotalBalance;
            }

            if (!empty($typeData)) {
                $groupedData[$typeName] = $typeData;
            }
        }

        // Calculate Net Profit/Loss from Income Statement accounts
        $incomeAccounts = Account::where('account_type', Account::INCOME)
            ->where('is_active', true)
            ->get();

        $expenseAccounts = Account::where('account_type', Account::EXPENSE)
            ->where('is_active', true)
            ->get();

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($incomeAccounts as $account) {
            $debit = ($accountTransactions[$account->id]['debit'] ?? 0) + ($account->opening_balance ?? 0);
            $credit = $accountTransactions[$account->id]['credit'] ?? 0;
            // Income is credit balance (positive)
            $totalIncome += max(0, $credit - $debit);
        }

        foreach ($expenseAccounts as $account) {
            $debit = ($accountTransactions[$account->id]['debit'] ?? 0) + ($account->opening_balance ?? 0);
            $credit = $accountTransactions[$account->id]['credit'] ?? 0;
            // Expenses are debit balance (positive)
            $totalExpenses += max(0, $debit - $credit);
        }

        $netProfit = $totalIncome - $totalExpenses;

        // Add net profit/loss to Equity
        $categoryTotals['Equity'] += $netProfit;

        // Balance Sheet Equation: Assets = Liabilities + Equity
        $balanceSheetEquation = abs($categoryTotals['Assets'] - ($categoryTotals['Liability'] + $categoryTotals['Equity']));

        return view('accounts.balance-sheet', [
            'groupedData' => $groupedData,
            'categoryTotals' => $categoryTotals,
            'netProfit' => $netProfit,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balanceSheetEquation' => $balanceSheetEquation,
            'dateTo' => $dateTo,
            'dateFrom' => $dateFrom,
        ]);
    }
}
