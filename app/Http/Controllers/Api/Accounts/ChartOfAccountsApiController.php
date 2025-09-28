<?php

namespace App\Http\Controllers\Api\Accounts;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChartOfAccountsApiController extends ApiController
{
    /**
     * Display the Chart of Accounts
     */
    public function index(Request $request)
    {
        try {
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

            $data = [
                'accounts' => $accounts,
                'groupedAccounts' => $groupedAccounts,
                'accountTypes' => $accountTypes,
                'parentAccounts' => $parentAccounts,
                'filters' => $request->only(['search', 'account_type', 'parent_id'])
            ];

            return $this->success($data, 'Chart of accounts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve chart of accounts: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'account_code' => 'required|string|max:20|unique:accounts,account_code',
                'account_name' => 'required|string|max:255',
                'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity',
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
            if (!$validated['account_code']) {
                $validated['account_code'] = $this->generateAccountCode($validated['account_type']);
            }

            $account = Account::create($validated);

            return $this->success($account, 'Account created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        try {
            $account->load(['parent', 'children', 'transactions' => function ($query) {
                $query->latest()->limit(10);
            }]);

            return $this->success($account, 'Account retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve account: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, Account $account)
    {
        try {
            $validated = $request->validate([
                'account_code' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('accounts', 'account_code')->ignore($account->id)
                ],
                'account_name' => 'required|string|max:255',
                'account_type' => 'required|in:Asset,Liability,Income,Expense,Equity',
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

            $account->update($validated);

            return $this->success($account, 'Account updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update account: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        try {
            // Prevent deletion of system accounts
            if ($account->is_system) {
                return $this->error('Cannot delete system accounts.', null, 403);
            }

            // Prevent deletion of accounts with children
            if ($account->hasChildren()) {
                return $this->error('Cannot delete account with sub-accounts. Please delete sub-accounts first.', null, 403);
            }

            // Prevent deletion of accounts with transactions
            if ($account->transactions()->count() > 0) {
                return $this->error('Cannot delete account with transactions. Please transfer or delete transactions first.', null, 403);
            }

            $account->delete();

            return $this->success(null, 'Account deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete account: ' . $e->getMessage());
        }
    }

    /**
     * Toggle account active status
     */
    public function toggleStatus(Account $account)
    {
        try {
            $account->update(['is_active' => !$account->is_active]);

            $status = $account->is_active ? 'activated' : 'deactivated';
            return $this->success($account, "Account {$status} successfully");
        } catch (\Exception $e) {
            return $this->serverError('Failed to toggle account status: ' . $e->getMessage());
        }
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
     * Get parent accounts by account type (for dynamic loading)
     */
    public function getParentAccountsByType(Request $request)
    {
        try {
            $accountType = $request->get('account_type');
            
            if (!$accountType) {
                return $this->error('Account type is required', null, 400);
            }

            $parentAccounts = Account::whereNull('parent_id')
                ->where('account_type', $accountType)
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get(['id', 'account_code', 'account_name', 'account_type']);

            return $this->success($parentAccounts, 'Parent accounts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve parent accounts: ' . $e->getMessage());
        }
    }

    /**
     * Get all parent accounts for account creation
     */
    public function getParentAccounts()
    {
        try {
            $parentAccounts = Account::whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('account_type')
                ->orderBy('account_name')
                ->get(['id', 'account_code', 'account_name', 'account_type']);

            $groupedAccounts = $parentAccounts->groupBy('account_type');

            $data = [
                'parentAccounts' => $parentAccounts,
                'groupedParentAccounts' => $groupedAccounts
            ];

            return $this->success($data, 'Parent accounts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve parent accounts: ' . $e->getMessage());
        }
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
     * Get account balance summary
     */
    public function balanceSummary()
    {
        try {
            $summary = Account::select('account_type')
                ->selectRaw('SUM(current_balance) as total_balance')
                ->where('is_active', true)
                ->groupBy('account_type')
                ->get()
                ->keyBy('account_type');

            return $this->success($summary, 'Account balance summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve account balance summary: ' . $e->getMessage());
        }
    }
}
