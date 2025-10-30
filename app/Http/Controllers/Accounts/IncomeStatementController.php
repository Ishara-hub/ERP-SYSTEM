<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;

class IncomeStatementController extends Controller
{
    /**
     * Display Income Statement
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        // Get all Income and Expense accounts
        $incomeAccounts = Account::where('account_type', Account::INCOME)
            ->where('is_active', true)
            ->with(['children'])
            ->orderBy('sort_order')
            ->orderBy('account_code')
            ->get();

        $expenseAccounts = Account::where('account_type', Account::EXPENSE)
            ->where('is_active', true)
            ->with(['children'])
            ->orderBy('sort_order')
            ->orderBy('account_code')
            ->get();

        // Get all journal entries for the period
        $journals = Journal::with(['debitAccount', 'creditAccount', 'transaction'])
            ->whereBetween('date', [$dateFrom, $dateTo])
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

        // Process Revenue (Income) accounts
        $revenueData = [];
        foreach ($incomeAccounts as $account) {
            if ($account->parent_id === null) {
                $debit = $accountTransactions[$account->id]['debit'] ?? 0;
                $credit = $accountTransactions[$account->id]['credit'] ?? 0;
                // Income: credit balance (positive)
                $amount = max(0, $credit - $debit);

                // Get sub-accounts
                $subAccounts = $account->children;
                $subAccountsData = [];
                $totalAmount = $amount;

                foreach ($subAccounts as $subAccount) {
                    $subDebit = $accountTransactions[$subAccount->id]['debit'] ?? 0;
                    $subCredit = $accountTransactions[$subAccount->id]['credit'] ?? 0;
                    $subAmount = max(0, $subCredit - $subDebit);

                    $subAccountsData[] = [
                        'sub_account_code' => $subAccount->account_code,
                        'sub_account_name' => $subAccount->account_name,
                        'amount' => $subAmount,
                    ];

                    $totalAmount += $subAmount;
                }

                if ($amount > 0 || !empty($subAccountsData)) {
                    $revenueData[] = [
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'amount' => $amount,
                        'sub_accounts' => $subAccountsData,
                    ];
                }
            }
        }

        // Process Expenses
        $expensesData = [];
        foreach ($expenseAccounts as $account) {
            if ($account->parent_id === null) {
                $debit = $accountTransactions[$account->id]['debit'] ?? 0;
                $credit = $accountTransactions[$account->id]['credit'] ?? 0;
                // Expenses: debit balance (positive)
                $amount = max(0, $debit - $credit);

                // Get sub-accounts
                $subAccounts = $account->children;
                $subAccountsData = [];
                $totalAmount = $amount;

                foreach ($subAccounts as $subAccount) {
                    $subDebit = $accountTransactions[$subAccount->id]['debit'] ?? 0;
                    $subCredit = $accountTransactions[$subAccount->id]['credit'] ?? 0;
                    $subAmount = max(0, $subDebit - $subCredit);

                    $subAccountsData[] = [
                        'sub_account_code' => $subAccount->account_code,
                        'sub_account_name' => $subAccount->account_name,
                        'amount' => $subAmount,
                    ];

                    $totalAmount += $subAmount;
                }

                if ($amount > 0 || !empty($subAccountsData)) {
                    $expensesData[] = [
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'amount' => $amount,
                        'sub_accounts' => $subAccountsData,
                    ];
                }
            }
        }

        return view('accounts.income-statement', [
            'revenueData' => $revenueData,
            'expensesData' => $expensesData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
