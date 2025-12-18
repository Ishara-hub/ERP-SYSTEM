<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashFlowStatementController extends Controller
{
    /**
     * Display Statement of Cash Flows
     * Using Indirect Method
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo = $request->get('date_to', date('Y-m-d'));

        // 1. Operating Activities
        // Start with Net Income (from Income Statement accounts)
        $incomeAccounts = Account::where('account_type', Account::INCOME)->get();
        $expenseAccounts = Account::where('account_type', Account::EXPENSE)->get();
        
        $totalIncome = 0;
        $totalExpenses = 0;

        $journals = Journal::whereBetween('date', [$dateFrom, $dateTo])->get();
        
        $accountBalances = [];
        foreach ($journals as $journal) {
            if (!isset($accountBalances[$journal->debit_account_id])) $accountBalances[$journal->debit_account_id] = 0;
            if (!isset($accountBalances[$journal->credit_account_id])) $accountBalances[$journal->credit_account_id] = 0;
            
            // For P&L accounts, we just need the net change in the period
            // Income: Credit - Debit
            // Expense: Debit - Credit
        }

        // A more robust way is to calculate net income for the period
        foreach ($incomeAccounts as $acc) {
            $debit = Journal::where('debit_account_id', $acc->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $credit = Journal::where('credit_account_id', $acc->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $totalIncome += ($credit - $debit);
        }
        foreach ($expenseAccounts as $acc) {
            $debit = Journal::where('debit_account_id', $acc->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $credit = Journal::where('credit_account_id', $acc->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $totalExpenses += ($debit - $credit);
        }

        $netIncome = $totalIncome - $totalExpenses;

        // Adjustments for non-cash items (Depreciation, etc.) - Placeholder
        $depreciation = 0; // Would need to find depreciation accounts

        // Changes in working capital (AR, AP, Inventory)
        $arAccount = Account::where('account_name', 'Accounts Receivable')->first();
        $apAccount = Account::where('account_name', 'Accounts Payable')->first();
        $inventoryAccount = Account::where('account_name', 'Inventory')->first();

        $changeInAR = 0;
        if ($arAccount) {
            $debit = Journal::where('debit_account_id', $arAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $credit = Journal::where('credit_account_id', $arAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $changeInAR = -($debit - $credit); // Increase in asset is cash outflow
        }

        $changeInInventory = 0;
        if ($inventoryAccount) {
            $debit = Journal::where('debit_account_id', $inventoryAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $credit = Journal::where('credit_account_id', $inventoryAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $changeInInventory = -($debit - $credit);
        }

        $changeInAP = 0;
        if ($apAccount) {
            $debit = Journal::where('debit_account_id', $apAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $credit = Journal::where('credit_account_id', $apAccount->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');
            $changeInAP = ($credit - $debit); // Increase in liability is cash inflow
        }

        $netCashOperating = $netIncome + $depreciation + $changeInAR + $changeInInventory + $changeInAP;

        // 2. Investing Activities (Purchase/Sale of Assets)
        $netCashInvesting = 0; // Simplified for now

        // 3. Financing Activities (Equity, Loans)
        $netCashFinancing = 0; // Simplified for now

        $netChangeInCash = $netCashOperating + $netCashInvesting + $netCashFinancing;

        // Beginning Cash
        $bankAccounts = Account::where('account_type', 'Bank')->get();
        $beginningCash = 0;
        foreach ($bankAccounts as $bank) {
            $debitBefore = Journal::where('debit_account_id', $bank->id)->where('date', '<', $dateFrom)->sum('amount');
            $creditBefore = Journal::where('credit_account_id', $bank->id)->where('date', '<', $dateFrom)->sum('amount');
            $beginningCash += ($bank->opening_balance ?? 0) + ($debitBefore - $creditBefore);
        }

        $endingCash = $beginningCash + $netChangeInCash;

        return view('accounts.reports.cash-flow', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'netIncome' => $netIncome,
            'depreciation' => $depreciation,
            'changeInAR' => $changeInAR,
            'changeInInventory' => $changeInInventory,
            'changeInAP' => $changeInAP,
            'netCashOperating' => $netCashOperating,
            'netCashInvesting' => $netCashInvesting,
            'netCashFinancing' => $netCashFinancing,
            'netChangeInCash' => $netChangeInCash,
            'beginningCash' => $beginningCash,
            'endingCash' => $endingCash,
        ]);
    }
}

