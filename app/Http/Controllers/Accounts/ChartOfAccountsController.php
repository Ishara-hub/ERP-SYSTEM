<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ChartOfAccountsController extends Controller
{
    /**
     * Display the Chart of Accounts
     */
    public function index(Request $request)
    {
        $query = Account::with(['parent', 'children'])
            ->orderBy('account_type')
            ->orderBy('sort_order')
            ->orderBy('account_name');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('account_name', 'like', '%' . $request->search . '%')
                  ->orWhere('account_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by account type
        if ($request->has('account_type') && $request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        // Filter by parent (for hierarchical view)
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        $accounts = $query->get();

        // Group accounts by type for better organization
        $groupedAccounts = $accounts->groupBy('account_type');

        // Get account types for filter dropdown
        $accountTypes = [
            Account::ASSET => 'Assets',
            Account::LIABILITY => 'Liabilities',
            Account::EQUITY => 'Equity',
            Account::INCOME => 'Income',
            Account::EXPENSE => 'Expenses',
        ];

        // Get parent accounts for hierarchical selection
        $parentAccounts = Account::whereNull('parent_id')
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get();

        return Inertia::render('accounts/chart-of-accounts', [
            'accounts' => $accounts,
            'groupedAccounts' => $groupedAccounts,
            'accountTypes' => $accountTypes,
            'parentAccounts' => $parentAccounts,
            'filters' => $request->only(['search', 'account_type', 'parent_id'])
        ]);
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        $accountTypes = [
            Account::ASSET => 'Assets',
            Account::LIABILITY => 'Liabilities',
            Account::EQUITY => 'Equity',
            Account::INCOME => 'Income',
            Account::EXPENSE => 'Expenses',
        ];

        $parentAccounts = Account::whereNull('parent_id')
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get();

        return Inertia::render('accounts/create', [
            'accountTypes' => $accountTypes,
            'parentAccounts' => $parentAccounts
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_code' => 'required|string|max:20|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Generate account code if not provided
        if (!$request->account_code) {
            $request->merge(['account_code' => $this->generateAccountCode($request->account_type)]);
        }

        $account = Account::create($request->all());

        return redirect()->route('accounts.chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        $account->load(['parent', 'children', 'transactions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('accounts/show', [
            'account' => $account
        ]);
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Account $account)
    {
        $accountTypes = [
            Account::ASSET => 'Assets',
            Account::LIABILITY => 'Liabilities',
            Account::EQUITY => 'Equity',
            Account::INCOME => 'Income',
            Account::EXPENSE => 'Expenses',
        ];

        $parentAccounts = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get();

        return Inertia::render('accounts/edit', [
            'account' => $account,
            'accountTypes' => $accountTypes,
            'parentAccounts' => $parentAccounts
        ]);
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, Account $account)
    {
        $request->validate([
            'account_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts', 'account_code')->ignore($account->id)
            ],
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Prevent setting parent as child of itself
        if ($request->parent_id == $account->id) {
            return back()->withErrors(['parent_id' => 'Account cannot be its own parent.']);
        }

        $account->update($request->all());

        return redirect()->route('accounts.chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        // Prevent deletion of system accounts
        if ($account->is_system) {
            return redirect()->route('accounts.chart-of-accounts.index')
                ->with('error', 'Cannot delete system accounts.');
        }

        // Prevent deletion of accounts with children
        if ($account->hasChildren()) {
            return redirect()->route('accounts.chart-of-accounts.index')
                ->with('error', 'Cannot delete account with sub-accounts. Please delete sub-accounts first.');
        }

        // Prevent deletion of accounts with transactions
        if ($account->transactions()->count() > 0) {
            return redirect()->route('accounts.chart-of-accounts.index')
                ->with('error', 'Cannot delete account with transactions. Please transfer or delete transactions first.');
        }

        $account->delete();

        return redirect()->route('accounts.chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    /**
     * Toggle account active status
     */
    public function toggleStatus(Account $account)
    {
        $account->update(['is_active' => !$account->is_active]);

        $status = $account->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Account {$status} successfully.");
    }

    /**
     * Generate account code based on account type
     */
    private function generateAccountCode(string $accountType): string
    {
        $prefixes = [
            Account::ASSET => '1000',
            Account::LIABILITY => '2000',
            Account::EQUITY => '3000',
            Account::INCOME => '4000',
            Account::EXPENSE => '5000',
        ];

        $prefix = $prefixes[$accountType] ?? '0000';
        
        // Get the highest account code for this type
        $lastAccount = Account::where('account_code', 'like', $prefix . '%')
            ->orderBy('account_code', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = (int) substr($lastAccount->account_code, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get account balance summary
     */
    public function balanceSummary()
    {
        $summary = Account::select('account_type')
            ->selectRaw('SUM(current_balance) as total_balance')
            ->where('is_active', true)
            ->groupBy('account_type')
            ->get()
            ->keyBy('account_type');

        return response()->json($summary);
    }
}