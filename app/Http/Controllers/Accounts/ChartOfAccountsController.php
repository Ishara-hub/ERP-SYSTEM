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
            Account::ACCOUNTS_RECEIVABLE => 'Accounts Receivable',
            Account::OTHER_CURRENT_ASSET => 'Other Current Asset',
            Account::FIXED_ASSET => 'Fixed Asset',
            Account::ACCOUNTS_PAYABLE => 'Accounts Payable',
            Account::OTHER_CURRENT_LIABILITY => 'Other Current Liability',
            Account::COST_OF_GOODS_SOLD => 'Cost of Goods Sold',
            Account::BANK => 'Bank',
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
            Account::ACCOUNTS_RECEIVABLE => 'Accounts Receivable',
            Account::OTHER_CURRENT_ASSET => 'Other Current Asset',
            Account::FIXED_ASSET => 'Fixed Asset',
            Account::ACCOUNTS_PAYABLE => 'Accounts Payable',
            Account::OTHER_CURRENT_LIABILITY => 'Other Current Liability',
            Account::COST_OF_GOODS_SOLD => 'Cost of Goods Sold',
            Account::BANK => 'Bank',
        ];

        // Get all parent accounts (accounts without parent_id) grouped by type
        $parentAccountsQuery = Account::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_name');

        $parentAccounts = $parentAccountsQuery->get();
        
        // Group parent accounts by type for better organization
        $groupedParentAccounts = $parentAccounts->groupBy('account_type');

        return Inertia::render('accounts/create', [
            'accountTypes' => $accountTypes,
            'parentAccounts' => $parentAccounts,
            'groupedParentAccounts' => $groupedParentAccounts
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
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity,Accounts Receivable,Other Current Asset,Fixed Asset,Accounts Payable,Other Current Liability,Cost of Goods Sold,Bank',
            'parent_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $parentAccount = Account::find($value);
                        if ($parentAccount && $parentAccount->account_type !== $request->account_type) {
                            $fail('Parent account must be of the same account type.');
                        }
                        if ($parentAccount && $parentAccount->parent_id !== null) {
                            $fail('Parent account cannot be a sub-account. Please select a main account.');
                        }
                    }
                }
            ],
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

        return redirect()->route('accounts.index')
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
            Account::ACCOUNTS_RECEIVABLE => 'Accounts Receivable',
            Account::OTHER_CURRENT_ASSET => 'Other Current Asset',
            Account::FIXED_ASSET => 'Fixed Asset',
            Account::ACCOUNTS_PAYABLE => 'Accounts Payable',
            Account::OTHER_CURRENT_LIABILITY => 'Other Current Liability',
            Account::COST_OF_GOODS_SOLD => 'Cost of Goods Sold',
            Account::BANK => 'Bank',
        ];

        // Get parent accounts, excluding the current account and its children
        $parentAccounts = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get();

        // Group parent accounts by type for better organization
        $groupedParentAccounts = $parentAccounts->groupBy('account_type');

        return Inertia::render('accounts/edit', [
            'account' => $account,
            'accountTypes' => $accountTypes,
            'parentAccounts' => $parentAccounts,
            'groupedParentAccounts' => $groupedParentAccounts
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
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity,Accounts Receivable,Other Current Asset,Fixed Asset,Accounts Payable,Other Current Liability,Cost of Goods Sold,Bank',
            'parent_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use ($request, $account) {
                    if ($value) {
                        // Prevent setting parent as child of itself
                        if ($value == $account->id) {
                            $fail('Account cannot be its own parent.');
                        }
                        
                        $parentAccount = Account::find($value);
                        if ($parentAccount && $parentAccount->account_type !== $request->account_type) {
                            $fail('Parent account must be of the same account type.');
                        }
                        if ($parentAccount && $parentAccount->parent_id !== null) {
                            $fail('Parent account cannot be a sub-account. Please select a main account.');
                        }
                        
                        // Prevent circular relationships (parent cannot be a child of this account)
                        if ($this->wouldCreateCircularRelationship($account->id, $value)) {
                            $fail('This would create a circular relationship. Please select a different parent.');
                        }
                    }
                }
            ],
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $account->update($request->all());

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        // Prevent deletion of system accounts
        if ($account->is_system) {
        return redirect()->route('accounts.index')
            ->with('error', 'Cannot delete system accounts.');
        }

        // Prevent deletion of accounts with children
        if ($account->hasChildren()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Cannot delete account with sub-accounts. Please delete sub-accounts first.');
        }

        // Prevent deletion of accounts with transactions
        if ($account->transactions()->count() > 0) {
            return redirect()->route('accounts.index')
                ->with('error', 'Cannot delete account with transactions. Please transfer or delete transactions first.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
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
     * Check if setting a parent would create a circular relationship
     */
    private function wouldCreateCircularRelationship(int $accountId, int $parentId): bool
    {
        $currentParent = $parentId;
        
        while ($currentParent) {
            if ($currentParent == $accountId) {
                return true;
            }
            
            $parent = Account::find($currentParent);
            $currentParent = $parent ? $parent->parent_id : null;
        }
        
        return false;
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
            Account::ACCOUNTS_RECEIVABLE => '1100',
            Account::OTHER_CURRENT_ASSET => '1200',
            Account::FIXED_ASSET => '1300',
            Account::BANK => '1400',
            Account::ACCOUNTS_PAYABLE => '2100',
            Account::OTHER_CURRENT_LIABILITY => '2200',
            Account::COST_OF_GOODS_SOLD => '5100',
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
     * Get parent accounts by account type (for dynamic loading)
     */
    public function getParentAccountsByType(Request $request)
    {
        $accountType = $request->get('account_type');
        
        if (!$accountType) {
            return response()->json(['error' => 'Account type is required'], 400);
        }

        $parentAccounts = Account::whereNull('parent_id')
            ->where('account_type', $accountType)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get(['id', 'account_code', 'account_name', 'account_type']);

        return response()->json($parentAccounts);
    }

    /**
     * Get all parent accounts for account creation
     */
    public function getParentAccounts()
    {
        $parentAccounts = Account::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get(['id', 'account_code', 'account_name', 'account_type']);

        $groupedAccounts = $parentAccounts->groupBy('account_type');

        return response()->json([
            'parentAccounts' => $parentAccounts,
            'groupedParentAccounts' => $groupedAccounts
        ]);
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

    /**
     * Generate account code via AJAX
     */
    public function generateAccountCodeAjax(Request $request)
    {
        $accountType = $request->get('account_type');
        
        if (!$accountType) {
            return response()->json(['error' => 'Account type is required'], 400);
        }

        $accountCode = $this->generateAccountCode($accountType);
        
        return response()->json([
            'account_code' => $accountCode
        ]);
    }

    /**
     * Create account type (parent account) via AJAX
     */
    public function createAccountType(Request $request)
    {
        $request->validate([
            'account_code' => 'nullable|string|max:20|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity,Accounts Receivable,Other Current Asset,Fixed Asset,Accounts Payable,Other Current Liability,Cost of Goods Sold,Bank',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Generate account code if not provided
        if (!$request->account_code) {
            $request->merge(['account_code' => $this->generateAccountCode($request->account_type)]);
        }

        // Ensure parent_id is null for account types
        $request->merge(['parent_id' => null]);

        $account = Account::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Account type created successfully.',
            'account' => $account->load('parent', 'children')
        ], 201);
    }

    /**
     * Create sub-account via AJAX
     */
    public function createSubAccount(Request $request)
    {
        $request->validate([
            'account_code' => 'nullable|string|max:20|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity,Accounts Receivable,Other Current Asset,Fixed Asset,Accounts Payable,Other Current Liability,Cost of Goods Sold,Bank',
            'parent_id' => [
                'required',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $parentAccount = Account::find($value);
                        if ($parentAccount && $parentAccount->account_type !== $request->account_type) {
                            $fail('Parent account must be of the same account type.');
                        }
                        if ($parentAccount && $parentAccount->parent_id !== null) {
                            $fail('Parent account cannot be a sub-account. Please select a main account.');
                        }
                    }
                }
            ],
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

        return response()->json([
            'success' => true,
            'message' => 'Sub-account created successfully.',
            'account' => $account->load('parent', 'children')
        ], 201);
    }

    /**
     * Get sub-accounts for a parent account
     */
    public function getSubAccounts(Request $request, Account $account)
    {
        $subAccounts = $account->children()
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('account_name')
            ->get();

        return response()->json([
            'subAccounts' => $subAccounts,
            'parent' => $account
        ]);
    }
}