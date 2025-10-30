<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsDataController extends Controller
{
    /**
     * Display Chart of Accounts Data (hierarchical view with transaction totals)
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        // Get all active accounts with their parent relationships
        $accounts = Account::where('is_active', true)
            ->with(['parent', 'children'])
            ->orderBy('account_type')
            ->orderBy('sort_order')
            ->orderBy('account_code')
            ->get();

        // Fetch all journal entries for the selected period
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

        // Group accounts by account_type (similar to categories in old system)
        $groupedData = [];
        $accountTypes = [
            Account::ASSET => 'Assets',
            Account::LIABILITY => 'Liabilities',
            Account::EQUITY => 'Equity',
            Account::INCOME => 'Income',
            Account::EXPENSE => 'Expenses',
        ];

        foreach ($accountTypes as $typeKey => $typeName) {
            // Get parent accounts (main accounts) for this type
            $parentAccounts = $accounts->where('account_type', $typeKey)
                ->whereNull('parent_id')
                ->sortBy(['sort_order', 'account_code']);

            if ($parentAccounts->isEmpty()) {
                continue;
            }

            $categoryData = [
                'category_name' => $typeName,
                'accounts' => [],
                'total_debit' => 0,
                'total_credit' => 0,
                'total_balance' => 0,
            ];

            foreach ($parentAccounts as $parentAccount) {
                $debit = $accountTransactions[$parentAccount->id]['debit'] ?? 0;
                $credit = $accountTransactions[$parentAccount->id]['credit'] ?? 0;
                $balance = $debit - $credit;

                // Get sub-accounts for this parent
                $subAccounts = $accounts->where('parent_id', $parentAccount->id)
                    ->sortBy(['sort_order', 'account_code']);

                $subAccountsData = [];
                $parentTotalDebit = $debit;
                $parentTotalCredit = $credit;
                $parentTotalBalance = $balance;

                foreach ($subAccounts as $subAccount) {
                    $subDebit = $accountTransactions[$subAccount->id]['debit'] ?? 0;
                    $subCredit = $accountTransactions[$subAccount->id]['credit'] ?? 0;
                    $subBalance = $subDebit - $subCredit;

                    $subAccountsData[] = [
                        'sub_account_id' => $subAccount->id,
                        'sub_account_code' => $subAccount->account_code,
                        'sub_account_name' => $subAccount->account_name,
                        'debit' => $subDebit,
                        'credit' => $subCredit,
                        'balance' => $subBalance,
                    ];

                    $parentTotalDebit += $subDebit;
                    $parentTotalCredit += $subCredit;
                    $parentTotalBalance += $subBalance;
                }

                // If main account has sub-accounts, show totals; otherwise show individual values
                $accountData = [
                    'account_id' => $parentAccount->id,
                    'account_code' => $parentAccount->account_code,
                    'account_name' => $parentAccount->account_name,
                    'sub_accounts' => $subAccountsData,
                    'has_sub_accounts' => $subAccounts->isNotEmpty(),
                    'debit' => $subAccounts->isEmpty() ? $debit : 0,
                    'credit' => $subAccounts->isEmpty() ? $credit : 0,
                    'balance' => $subAccounts->isEmpty() ? $balance : 0,
                    'total_debit' => $parentTotalDebit,
                    'total_credit' => $parentTotalCredit,
                    'total_balance' => $parentTotalBalance,
                ];

                $categoryData['accounts'][$parentAccount->id] = $accountData;
                $categoryData['total_debit'] += $parentTotalDebit;
                $categoryData['total_credit'] += $parentTotalCredit;
                $categoryData['total_balance'] += $parentTotalBalance;
            }

            if (!empty($categoryData['accounts'])) {
                $groupedData[$typeKey] = $categoryData;
            }
        }

        return view('accounts.chart-of-accounts-data', [
            'groupedData' => $groupedData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
