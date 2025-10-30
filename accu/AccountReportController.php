<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use App\Models\ChartOfAccount;
use App\Models\SubAccount;
use App\Models\JournalEntry;
use App\Models\GeneralJournal;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountReportController extends Controller
{
    public function chartOfAccountsData(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $branches = Branch::orderBy('branch_name')->get();

        $categories = AccountCategory::with([
            'accounts' => function($q) {
                $q->where('is_active', 1)->orderBy('account_code');
            },
            'accounts.subAccounts' => function($q) {
                $q->where('is_active', 1)->orderBy('sub_account_code');
            }
        ])
        ->whereHas('accounts', function($q) {
            $q->where('is_active', 1);
        })
        ->orderBy('name')
        ->get();

        $groupedData = [];

        foreach ($categories as $category) {
            $groupedData[$category->id] = [
                'category_name' => $category->name ?? 'Unknown Category',
                'accounts' => [],
                'total_debit' => 0,
                'total_credit' => 0,
                'total_balance' => 0
            ];

            foreach ($category->accounts as $account) {
                if (!$account->is_active) continue;

                $groupedData[$category->id]['accounts'][$account->id] = [
                    'account_code' => $account->account_code ?? '',
                    'account_name' => $account->account_name ?? '',
                    'sub_accounts' => [],
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'total_balance' => 0,
                    'has_sub_accounts' => false
                ];

                // Main account's own entries (not including sub-accounts)
                $mainEntries = \App\Models\JournalEntry::where('account_id', $account->id)
                    ->whereNull('sub_account_id')
                    ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                        $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                        if ($branchId) $q->where('branch_id', $branchId);
                    })->get();
                $accountDebit = $mainEntries->sum('debit');
                $accountCredit = $mainEntries->sum('credit');
                $accountBalance = $accountDebit - $accountCredit;

                $groupedData[$category->id]['accounts'][$account->id]['debit'] = $accountDebit;
                $groupedData[$category->id]['accounts'][$account->id]['credit'] = $accountCredit;
                $groupedData[$category->id]['accounts'][$account->id]['balance'] = $accountBalance;

                $groupedData[$category->id]['accounts'][$account->id]['total_debit'] = $accountDebit;
                $groupedData[$category->id]['accounts'][$account->id]['total_credit'] = $accountCredit;
                $groupedData[$category->id]['accounts'][$account->id]['total_balance'] = $accountBalance;

                // Always include all sub-accounts, even if their balance is zero
                if ($account->subAccounts && $account->subAccounts->count() > 0) {
                    $groupedData[$category->id]['accounts'][$account->id]['has_sub_accounts'] = true;
                    foreach ($account->subAccounts as $subAccount) {
                        $subEntries = \App\Models\JournalEntry::where('sub_account_id', $subAccount->id)
                            ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                                $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                                if ($branchId) $q->where('branch_id', $branchId);
                            })->get();
                        $subDebit = $subEntries->sum('debit');
                        $subCredit = $subEntries->sum('credit');
                        $subBalance = $subDebit - $subCredit;
                        $groupedData[$category->id]['accounts'][$account->id]['sub_accounts'][] = [
                            'sub_account_id' => $subAccount->id,
                            'sub_account_code' => $subAccount->sub_account_code ?? '',
                            'sub_account_name' => $subAccount->sub_account_name ?? '',
                            'debit' => $subDebit,
                            'credit' => $subCredit,
                            'balance' => $subBalance
                        ];
                        // Add sub-account totals to account's total
                        $groupedData[$category->id]['accounts'][$account->id]['total_debit'] += $subDebit;
                        $groupedData[$category->id]['accounts'][$account->id]['total_credit'] += $subCredit;
                        $groupedData[$category->id]['accounts'][$account->id]['total_balance'] += $subBalance;
                    }
                }

                // Add to category totals
                $groupedData[$category->id]['total_debit'] += $groupedData[$category->id]['accounts'][$account->id]['total_debit'];
                $groupedData[$category->id]['total_credit'] += $groupedData[$category->id]['accounts'][$account->id]['total_credit'];
                $groupedData[$category->id]['total_balance'] += $groupedData[$category->id]['accounts'][$account->id]['total_balance'];
            }
        }

        $branchName = "All Branches";
        if ($branchId) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->branch_name : "All Branches";
        }

        return view('accounts.reports.chart_of_accounts_data', compact('groupedData', 'branches', 'branchName', 'dateFrom', 'dateTo', 'branchId'));
    }

    public function balanceSheet(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $branches = Branch::orderBy('branch_name')->get();

        // Get balance sheet accounts (Assets, Liability, Equity)
        $balanceSheetAccounts = ChartOfAccount::with([
            'category',
            'subAccounts' => function($q) {
                $q->where('is_active', 1)->orderBy('sub_account_code');
            }
        ])
        ->whereHas('category', function($q) {
            $q->whereIn('name', ['Assets', 'Liability', 'Equity']);
        })
        ->where('is_active', 1)
        ->orderBy('account_code')
        ->get();

        // Get income and expense accounts for P&L calculation
        $incomeExpenseAccounts = ChartOfAccount::with(['subAccounts' => function($q) {
            $q->where('is_active', 1)->orderBy('sub_account_code');
        }])
        ->whereHas('category', function($q) {
            $q->whereIn('name', ['Income', 'Expenses']);
        })
        ->where('is_active', 1)
        ->orderBy('account_code')
        ->get();

        $groupedData = [];
        $categoryTotals = [
            'Assets' => 0,
            'Liability' => 0,
            'Equity' => 0
        ];

        // Process balance sheet accounts
        foreach ($balanceSheetAccounts as $account) {
            if (!$account->category) continue;
            $accountType = $account->category->name;
            $mainAccount = $account->account_name ?? 'Unknown Account';

            if (!isset($groupedData[$accountType])) {
                $groupedData[$accountType] = [];
            }

            if (!isset($groupedData[$accountType][$mainAccount])) {
                $groupedData[$accountType][$mainAccount] = [
                    'account_code' => $account->account_code ?? '',
                    'sub_accounts' => [],
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => 0,
                    'total_balance' => 0
                ];
            }

            // Main account's own entries (not including sub-accounts)
            $mainEntries = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereNull('sub_account_id')
                ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                    $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                    if ($branchId) $q->where('branch_id', $branchId);
                })->get();
            $mainDebit = $mainEntries->sum('debit');
            $mainCredit = $mainEntries->sum('credit');
            
            // Calculate balance based on account type
            if ($accountType === 'Assets') {
                // Assets: Debit - Credit (normal debit balance)
                $mainBalance = $mainDebit - $mainCredit;
            } elseif ($accountType === 'Liability' || $accountType === 'Equity') {
                // Liability & Equity: Credit - Debit (normal credit balance, show as positive)
                $mainBalance = $mainCredit - $mainDebit;
            } else {
                // Default: Debit - Credit
                $mainBalance = $mainDebit - $mainCredit;
            }

            $groupedData[$accountType][$mainAccount]['debit'] = $mainDebit;
            $groupedData[$accountType][$mainAccount]['credit'] = $mainCredit;
            $groupedData[$accountType][$mainAccount]['balance'] = $mainBalance;
            $groupedData[$accountType][$mainAccount]['total_balance'] = $mainBalance;

            // Sub-accounts
            if ($account->subAccounts && $account->subAccounts->count() > 0) {
                foreach ($account->subAccounts as $subAccount) {
                    $subEntries = \App\Models\JournalEntry::where('sub_account_id', $subAccount->id)
                        ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                            $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                            if ($branchId) $q->where('branch_id', $branchId);
                        })->get();
                    $subDebit = $subEntries->sum('debit');
                    $subCredit = $subEntries->sum('credit');
                    
                    // Calculate sub-account balance based on account type
                    if ($accountType === 'Assets') {
                        // Assets: Debit - Credit (normal debit balance)
                        $subBalance = $subDebit - $subCredit;
                    } elseif ($accountType === 'Liability' || $accountType === 'Equity') {
                        // Liability & Equity: Credit - Debit (normal credit balance, show as positive)
                        $subBalance = $subCredit - $subDebit;
                    } else {
                        // Default: Debit - Credit
                        $subBalance = $subDebit - $subCredit;
                    }
                    
                    $groupedData[$accountType][$mainAccount]['sub_accounts'][] = [
                        'sub_account_code' => $subAccount->sub_account_code ?? '',
                        'sub_account_name' => $subAccount->sub_account_name ?? '',
                        'debit' => $subDebit,
                        'credit' => $subCredit,
                        'balance' => $subBalance
                    ];
                    $groupedData[$accountType][$mainAccount]['total_balance'] += $subBalance;
                }
            }

            // Add to category totals
            $categoryTotals[$accountType] += $groupedData[$accountType][$mainAccount]['total_balance'];
        }

        // Calculate Profit & Loss (Income - Expenses)
        // Income has credit balances, Expenses have debit balances
        // Net Profit = Total Income (Credit) - Total Expenses (Debit)
        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($incomeExpenseAccounts as $account) {
            $mainEntries = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereNull('sub_account_id')
                ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                    $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                    if ($branchId) $q->where('branch_id', $branchId);
                })->get();
            
            if ($account->category->name === 'Income') {
                // Income: Credit increases, Debit decreases
                // Balance = Credit - Debit (positive for income)
                $mainCredit = $mainEntries->sum('credit');
                $mainDebit = $mainEntries->sum('debit');
                $mainAmount = $mainCredit - $mainDebit;
                $totalIncome += $mainAmount;
            } else { // Expenses
                // Expenses: Debit increases, Credit decreases  
                // Balance = Debit - Credit (positive for expenses)
                $mainDebit = $mainEntries->sum('debit');
                $mainCredit = $mainEntries->sum('credit');
                $mainAmount = $mainDebit - $mainCredit;
                $totalExpenses += $mainAmount;
            }

            // Add sub-account amounts
            if ($account->subAccounts && $account->subAccounts->count() > 0) {
                foreach ($account->subAccounts as $subAccount) {
                    $subEntries = \App\Models\JournalEntry::where('sub_account_id', $subAccount->id)
                        ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                            $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                            if ($branchId) $q->where('branch_id', $branchId);
                        })->get();
                    
                    if ($account->category->name === 'Income') {
                        // Income: Credit increases, Debit decreases
                        // Balance = Credit - Debit (positive for income)
                        $subCredit = $subEntries->sum('credit');
                        $subDebit = $subEntries->sum('debit');
                        $subAmount = $subCredit - $subDebit;
                        $totalIncome += $subAmount;
                    } else { // Expenses
                        // Expenses: Debit increases, Credit decreases
                        // Balance = Debit - Credit (positive for expenses)
                        $subDebit = $subEntries->sum('debit');
                        $subCredit = $subEntries->sum('credit');
                        $subAmount = $subDebit - $subCredit;
                        $totalExpenses += $subAmount;
                    }
                }
            }
        }

        $netProfit = $totalIncome - $totalExpenses;

        // Add Profit & Loss Retained to Equity
        if (!isset($groupedData['Equity'])) {
            $groupedData['Equity'] = [];
        }

        // For Equity, we show credit balances as positive
        // If netProfit is positive (income > expenses), it's a credit to equity
        // If netProfit is negative (expenses > income), it's a debit to equity
        $plBalance = $netProfit; // This will be positive for profit, negative for loss

        $groupedData['Equity']['Profit & Loss Retained'] = [
            'account_code' => 'PL001',
            'sub_accounts' => [],
            'debit' => $netProfit < 0 ? abs($netProfit) : 0, // Debit if loss
            'credit' => $netProfit > 0 ? $netProfit : 0,      // Credit if profit
            'balance' => $plBalance,
            'total_balance' => $plBalance
        ];

        // Update Equity total to include P&L
        $categoryTotals['Equity'] += $plBalance;

        // Calculate balance sheet equation
        $totalAssets = $categoryTotals['Assets'];
        $totalLiability = $categoryTotals['Liability'];
        $totalEquity = $categoryTotals['Equity'];
        $balanceSheetEquation = $totalAssets - ($totalLiability + $totalEquity);

        $branchName = "All Branches";
        if ($branchId) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->branch_name : "All Branches";
        }

        return view('accounts.reports.balance_sheet', compact(
            'groupedData', 
            'branches', 
            'branchName', 
            'dateFrom', 
            'dateTo', 
            'branchId',
            'categoryTotals',
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'balanceSheetEquation'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $branches = Branch::orderBy('branch_name')->get();

        // Revenue (Income) accounts
        $revenue = ChartOfAccount::with(['subAccounts' => function($q) {
            $q->where('is_active', 1)->orderBy('sub_account_code');
        }])
        ->whereHas('category', function($q) {
            $q->where('name', 'Income');
        })
        ->where('is_active', 1)
        ->orderBy('account_code')
        ->get();

        // Expense accounts
        $expenses = ChartOfAccount::with(['subAccounts' => function($q) {
            $q->where('is_active', 1)->orderBy('sub_account_code');
        }])
        ->whereHas('category', function($q) {
            $q->where('name', 'Expenses');
        })
        ->where('is_active', 1)
        ->orderBy('account_code')
        ->get();

        // Calculate revenue and expenses
        $revenueData = [];
        $totalRevenue = 0;
        foreach ($revenue as $account) {
            $mainEntries = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereNull('sub_account_id')
                ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                    $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                    if ($branchId) $q->where('branch_id', $branchId);
                })->get();
            $mainCredit = $mainEntries->sum('credit');
            $mainDebit = $mainEntries->sum('debit');
            $mainAmount = $mainCredit - $mainDebit;
            $revenueData[] = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'amount' => $mainAmount,
                'sub_accounts' => []
            ];
            $totalRevenue += $mainAmount;
            if ($account->subAccounts && $account->subAccounts->count() > 0) {
                foreach ($account->subAccounts as $subAccount) {
                    $subEntries = \App\Models\JournalEntry::where('sub_account_id', $subAccount->id)
                        ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                            $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                            if ($branchId) $q->where('branch_id', $branchId);
                        })->get();
                    $subCredit = $subEntries->sum('credit');
                    $subDebit = $subEntries->sum('debit');
                    $subAmount = $subCredit - $subDebit;
                    $revenueData[count($revenueData)-1]['sub_accounts'][] = [
                        'sub_account_code' => $subAccount->sub_account_code,
                        'sub_account_name' => $subAccount->sub_account_name,
                        'amount' => $subAmount
                    ];
                    $totalRevenue += $subAmount;
                }
            }
        }

        $expensesData = [];
        $totalExpenses = 0;
        foreach ($expenses as $account) {
            $mainEntries = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereNull('sub_account_id')
                ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                    $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                    if ($branchId) $q->where('branch_id', $branchId);
                })->get();
            $mainDebit = $mainEntries->sum('debit');
            $mainCredit = $mainEntries->sum('credit');
            $mainAmount = $mainDebit - $mainCredit;
            $expensesData[] = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'amount' => $mainAmount,
                'sub_accounts' => []
            ];
            $totalExpenses += $mainAmount;
            if ($account->subAccounts && $account->subAccounts->count() > 0) {
                foreach ($account->subAccounts as $subAccount) {
                    $subEntries = \App\Models\JournalEntry::where('sub_account_id', $subAccount->id)
                        ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                            $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                            if ($branchId) $q->where('branch_id', $branchId);
                        })->get();
                    $subDebit = $subEntries->sum('debit');
                    $subCredit = $subEntries->sum('credit');
                    $subAmount = $subDebit - $subCredit;
                    $expensesData[count($expensesData)-1]['sub_accounts'][] = [
                        'sub_account_code' => $subAccount->sub_account_code,
                        'sub_account_name' => $subAccount->sub_account_name,
                        'amount' => $subAmount
                    ];
                    $totalExpenses += $subAmount;
                }
            }
        }

        $branchName = "All Branches";
        if ($branchId) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->branch_name : "All Branches";
        }

        return view('accounts.reports.income_statement', compact('revenueData', 'expensesData', 'totalRevenue', 'totalExpenses', 'branches', 'branchName', 'dateFrom', 'dateTo', 'branchId'));
    }

    /**
     * Display sub-account details with transactions
     */
    public function subAccountDetails(Request $request)
    {
        $subAccountId = $request->input('id');
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $branchId = $request->input('branch_id');

        $branches = Branch::orderBy('branch_name')->get();
        $subAccount = SubAccount::with(['parentAccount.category'])->findOrFail($subAccountId);

        // Fetch all journal entries for this sub-account, filtered by date and branch
        $transactions = \App\Models\JournalEntry::where('sub_account_id', $subAccountId)
            ->whereHas('journal', function($q) use ($dateFrom, $dateTo, $branchId) {
                $q->whereBetween('transaction_date', [$dateFrom, $dateTo]);
                if ($branchId) $q->where('branch_id', $branchId);
            })
            ->with(['journal.branch'])
            ->orderByDesc('id')
            ->get();

        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $netBalance = $totalDebit - $totalCredit;

        $branchName = "All Branches";
        if ($branchId) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->branch_name : "All Branches";
        }

        return view('accounts.reports.sub_account_details', compact(
            'subAccount',
            'transactions',
            'totalDebit',
            'totalCredit',
            'netBalance',
            'branches',
            'branchName',
            'dateFrom',
            'dateTo',
            'branchId'
        ));
    }
} 