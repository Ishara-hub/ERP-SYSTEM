<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WriteCheckController extends Controller
{
    /**
     * Display the Write Check form
     */
    public function index()
    {
        // Get bank accounts (sub-accounts of Cash and Cash Equivalents)
        $cashAccount = Account::where('account_name', 'LIKE', '%Cash%')
            ->orWhere('account_name', 'LIKE', '%Cash Equivalents%')
            ->whereNull('parent_id')
            ->first();

        $bankAccounts = collect();
        if ($cashAccount) {
            $bankAccounts = Account::where('parent_id', $cashAccount->id)
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get();
        } else {
            // Fallback: get all Asset accounts that might be bank accounts
            $bankAccounts = Account::where('account_type', Account::ASSET)
                ->whereNotNull('parent_id')
                ->where('is_active', true)
                ->where(function($query) {
                    $query->where('account_name', 'LIKE', '%Bank%')
                          ->orWhere('account_name', 'LIKE', '%Cash%')
                          ->orWhere('account_name', 'LIKE', '%Checking%')
                          ->orWhere('account_name', 'LIKE', '%Savings%');
                })
                ->orderBy('account_name')
                ->get();
        }

        // Get suppliers/vendors for Pay To The Order Of
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get customers for Customer:Job column
        $customers = \App\Models\Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get expense and item accounts for allocation
        $expenseAccounts = Account::where('account_type', Account::EXPENSE)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();

        // Get next check number (you may want to store this in settings)
        $lastCheck = DB::table('payments')
            ->whereNotNull('check_number')
            ->where('payment_method', 'check')
            ->orderByRaw('CAST(check_number AS UNSIGNED) DESC')
            ->first();

        $nextCheckNumber = $lastCheck ? (intval($lastCheck->check_number) + 1) : 1;

        return view('accounts.write-check', [
            'bankAccounts' => $bankAccounts,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'expenseAccounts' => $expenseAccounts,
            'nextCheckNumber' => $nextCheckNumber,
        ]);
    }

    /**
     * Store a new check
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:accounts,id',
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string|max:500',
            'check_date' => 'required|date',
            'check_number' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'memo' => 'nullable|string|max:500',
            'print_later' => 'boolean',
            'pay_online' => 'boolean',
            'expenses' => 'nullable|array',
            'expenses.*.account_id' => 'required|exists:accounts,id',
            'expenses.*.amount' => 'required|numeric|min:0.01',
            'expenses.*.memo' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Validate expenses exist
            if (!$request->has('expenses') || empty($request->expenses)) {
                return back()->withErrors(['expenses' => 'Please add at least one expense line.'])->withInput();
            }
            
            // Calculate expense total for validation (amount is auto-filled from expenses)
            $expenseTotal = 0;
            foreach ($request->expenses as $expense) {
                if (isset($expense['amount']) && $expense['amount'] > 0) {
                    $expenseTotal += $expense['amount'];
                }
            }

            // Update check amount to match expense total if it was auto-calculated
            $finalAmount = $expenseTotal > 0 ? $expenseTotal : $request->amount;

            // Get bank account
            $bankAccount = Account::findOrFail($request->bank_account_id);

            // Create Transaction and Journal entries for each expense
            $transactions = [];
            foreach ($request->expenses as $expense) {
                // Skip empty expense lines
                if (empty($expense['account_id']) || empty($expense['amount']) || $expense['amount'] <= 0) {
                    continue;
                }
                
                $expenseAccount = Account::findOrFail($expense['account_id']);
                
                // Get expense description/memo
                $expenseDescription = !empty($expense['memo']) 
                    ? $expense['memo'] 
                    : $expenseAccount->account_name;

                // Create Transaction for this expense line
                $transaction = Transaction::create([
                    'account_id' => $expenseAccount->id,
                    'type' => 'debit',
                    'amount' => $expense['amount'],
                    'description' => "Check #{$request->check_number} - {$expenseDescription}",
                    'transaction_date' => $request->check_date,
                ]);
                
                $transactions[] = $transaction;

                // Create journal entry: Debit Expense, Credit Bank
                Journal::create([
                    'transaction_id' => $transaction->id,
                    'debit_account_id' => $expenseAccount->id,
                    'credit_account_id' => $bankAccount->id,
                    'amount' => $expense['amount'],
                    'date' => $request->check_date,
                ]);

                // Update account balances
                $expenseAccount->increment('current_balance', $expense['amount']);
                $bankAccount->decrement('current_balance', $expense['amount']);
            }
            
            // Get the first transaction for payment record reference
            $mainTransaction = !empty($transactions) ? $transactions[0] : null;

            // Create payment record
            $supplier = Supplier::where('name', $request->pay_to)
                ->orWhere('company_name', $request->pay_to)
                ->first();

            $payment = \App\Models\Payment::create([
                'supplier_id' => $supplier->id ?? null,
                'bank_account_id' => $bankAccount->id,
                'payment_number' => 'CHK-' . $request->check_number,
                'payment_date' => $request->check_date,
                'payment_method' => 'check',
                'amount' => $finalAmount,
                'reference' => $request->check_number,
                'check_number' => $request->check_number,
                'notes' => $request->memo,
                'status' => $request->print_later ? 'pending' : 'completed',
                'payment_type' => 'expense',
                'payee' => $request->pay_to,
                'address' => $request->pay_to_address,
                'print_later' => $request->print_later ?? false,
                'pay_online' => $request->pay_online ?? false,
                'transaction_id' => $mainTransaction ? $mainTransaction->id : null,
            ]);

            DB::commit();

            $redirectRoute = route('accounts.write-check.index');
            if ($request->has('save_and_close')) {
                return redirect($redirectRoute)
                    ->with('success', "Check #{$request->check_number} for \${$finalAmount} has been created successfully.");
            } else {
                // Save & New - redirect back to form with new check number
                return redirect($redirectRoute)
                    ->with('success', "Check #{$request->check_number} for \${$finalAmount} has been created successfully. Creating new check...");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create check: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance(Request $request)
    {
        $accountId = $request->get('account_id');
        $account = Account::find($accountId);
        
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return response()->json([
            'balance' => $account->current_balance ?? 0,
            'account_code' => $account->account_code,
            'account_name' => $account->account_name,
        ]);
    }
}
