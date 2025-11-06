@extends('layouts.modern')

@section('title', 'Create Account')
@section('breadcrumb', 'Create Account')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
            <p class="text-sm text-gray-500 mt-1">Add a new account to your chart of accounts</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('accounts.store') }}" method="POST" id="account-form">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- Account Type and Number Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Account Type -->
                        <div>
                            <label for="account_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Type <span class="text-red-500">*</span>
                            </label>
                            <select name="account_type" 
                                    id="account_type"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('account_type') border-red-500 @enderror"
                                    required>
                                <option value="">Select Account Type</option>
                                @foreach($accountTypes as $key => $value)
                                    <option value="{{ $key }}" {{ old('account_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Number (Account Code) -->
                        <div>
                            <label for="account_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Number
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="account_code" 
                                       id="account_code"
                                       value="{{ old('account_code') }}"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('account_code') border-red-500 @enderror"
                                       placeholder="Auto-generated">
                                <button type="button" 
                                        id="generate-code-btn"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 text-blue-600 hover:text-blue-800"
                                        title="Generate Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('account_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Name with Select from Examples -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="account_name" class="block text-sm font-medium text-gray-700">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <button type="button" 
                                    id="select-examples-btn"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Select from Examples
                            </button>
                        </div>
                        <input type="text" 
                               name="account_name" 
                               id="account_name"
                               value="{{ old('account_name') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('account_name') border-red-500 @enderror"
                               placeholder="Enter account name"
                               required>
                        @error('account_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subaccount of Checkbox and Dropdown -->
                    <div>
                        <div class="flex items-start space-x-3">
                            <input type="checkbox" 
                                   id="is_subaccount"
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <div class="flex-1">
                                <label for="is_subaccount" class="block text-sm font-medium text-gray-700 mb-2 cursor-pointer">
                                    Subaccount of
                                </label>
                                <select name="parent_id" 
                                        id="parent_id"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('parent_id') border-red-500 @enderror"
                                        disabled>
                                    <option value="">Select Parent Account</option>
                                    @foreach($groupedParentAccounts as $type => $accounts)
                                        <optgroup label="{{ $type }}">
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" 
                                                        data-account-type="{{ $account->account_type }}"
                                                        {{ old('parent_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- OPTIONAL Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">OPTIONAL</h3>
                        
                        <div class="space-y-4">
                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea name="description" 
                                          id="description"
                                          rows="3"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('description') border-red-500 @enderror"
                                          placeholder="Enter account description">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" name="account_type_hidden" id="account_type_hidden" value="">
                    <input type="hidden" name="opening_balance" value="0">
                    <input type="hidden" name="sort_order" value="0">
                    <input type="hidden" name="is_active" value="1">
                    
                    <!-- Note: account_type will be set via JavaScript when subaccount is selected -->
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button type="button" 
                            id="save-and-new-btn"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save & New
                    </button>
                    <a href="{{ route('accounts.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save & Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Examples Modal -->
<div id="examples-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select from Examples</h3>
                <button type="button" id="close-examples-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <div id="examples-list" class="space-y-2">
                    <!-- Examples will be populated by JavaScript based on account type -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountTypeSelect = document.getElementById('account_type');
    const accountCodeInput = document.getElementById('account_code');
    const accountNameInput = document.getElementById('account_name');
    const isSubaccountCheckbox = document.getElementById('is_subaccount');
    const parentIdSelect = document.getElementById('parent_id');
    const accountTypeHidden = document.getElementById('account_type_hidden');
    const generateCodeBtn = document.getElementById('generate-code-btn');
    const selectExamplesBtn = document.getElementById('select-examples-btn');
    const examplesModal = document.getElementById('examples-modal');
    const closeExamplesModal = document.getElementById('close-examples-modal');
    const examplesList = document.getElementById('examples-list');
    const saveAndNewBtn = document.getElementById('save-and-new-btn');
    const accountForm = document.getElementById('account-form');

    // Account name examples by type
    const accountExamples = {
        'Accounts Receivable': ['Accounts Receivable', 'Trade Receivables', 'Customer Receivables', 'Notes Receivable'],
        'Other Current Asset': ['Prepaid Expenses', 'Inventory', 'Supplies', 'Short-term Investments'],
        'Fixed Asset': ['Buildings', 'Equipment', 'Vehicles', 'Furniture and Fixtures', 'Land', 'Machinery'],
        'Accounts Payable': ['Accounts Payable', 'Trade Payables', 'Vendor Payables', 'Bills Payable'],
        'Other Current Liability': ['Accrued Expenses', 'Short-term Loans', 'Current Portion of Long-term Debt', 'Taxes Payable'],
        'Cost of Goods Sold': ['Cost of Sales', 'Direct Materials', 'Direct Labor', 'Manufacturing Overhead'],
        'Bank': ['Cash', 'Checking Account', 'Savings Account', 'Petty Cash', 'Money Market Account'],
        'Asset': ['Cash', 'Accounts Receivable', 'Inventory', 'Prepaid Expenses', 'Equipment'],
        'Liability': ['Accounts Payable', 'Accrued Expenses', 'Notes Payable', 'Taxes Payable'],
        'Equity': ['Owner\'s Equity', 'Retained Earnings', 'Common Stock', 'Preferred Stock'],
        'Income': ['Sales Revenue', 'Service Revenue', 'Interest Income', 'Other Income'],
        'Expense': ['Rent Expense', 'Utilities Expense', 'Salaries Expense', 'Office Supplies Expense']
    };

    // Toggle subaccount dropdown
    isSubaccountCheckbox.addEventListener('change', function() {
        if (this.checked) {
            parentIdSelect.disabled = false;
            parentIdSelect.required = true;
            // Keep account type enabled but visually indicate it's auto-set
            accountTypeSelect.disabled = false;
            accountTypeSelect.classList.add('bg-gray-50');
            accountTypeSelect.required = false;
            
            // When parent is selected, set account type from parent
            const selectedParent = parentIdSelect.options[parentIdSelect.selectedIndex];
            if (selectedParent.value) {
                const parentAccountType = selectedParent.getAttribute('data-account-type');
                if (parentAccountType) {
                    accountTypeHidden.value = parentAccountType;
                    accountTypeSelect.value = parentAccountType;
                }
            }
        } else {
            parentIdSelect.disabled = true;
            parentIdSelect.required = false;
            parentIdSelect.value = '';
            accountTypeSelect.disabled = false;
            accountTypeSelect.classList.remove('bg-gray-50');
            accountTypeSelect.required = true;
            accountTypeHidden.value = '';
        }
    });

    // Update account type when parent is selected
    parentIdSelect.addEventListener('change', function() {
        if (isSubaccountCheckbox.checked && this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const parentAccountType = selectedOption.getAttribute('data-account-type');
            if (parentAccountType) {
                accountTypeHidden.value = parentAccountType;
                accountTypeSelect.value = parentAccountType;
                
                // Auto-generate code if empty
                if (!accountCodeInput.value) {
                    generateAccountCode(parentAccountType);
                }
            }
        }
    });

    // Auto-generate account code when account type changes
    accountTypeSelect.addEventListener('change', function() {
        // Prevent changes if subaccount is checked
        if (isSubaccountCheckbox.checked) {
            // Restore the value from parent
            const selectedParent = parentIdSelect.options[parentIdSelect.selectedIndex];
            if (selectedParent && selectedParent.value) {
                const parentAccountType = selectedParent.getAttribute('data-account-type');
                if (parentAccountType) {
                    this.value = parentAccountType;
                    return;
                }
            }
        }
        
        if (!isSubaccountCheckbox.checked && this.value && !accountCodeInput.value) {
            generateAccountCode(this.value);
        }
        accountTypeHidden.value = this.value;
    });

    // Generate code button
    generateCodeBtn.addEventListener('click', function() {
        const accountType = accountTypeSelect.value || accountTypeHidden.value;
        if (accountType) {
            generateAccountCode(accountType);
        } else {
            alert('Please select an account type first');
        }
    });

    // Generate account code function
    function generateAccountCode(accountType) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch(`{{ route('accounts.generate-code') }}?account_type=${encodeURIComponent(accountType)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.account_code) {
                accountCodeInput.value = data.account_code;
            }
        })
        .catch(error => {
            console.error('Error generating account code:', error);
        });
    }

    // Examples modal functionality
    selectExamplesBtn.addEventListener('click', function() {
        const accountType = accountTypeSelect.value;
        if (!accountType) {
            alert('Please select an account type first');
            return;
        }

        const examples = accountExamples[accountType] || [];
        examplesList.innerHTML = '';
        
        if (examples.length === 0) {
            examplesList.innerHTML = '<p class="text-sm text-gray-500">No examples available for this account type.</p>';
        } else {
            examples.forEach(example => {
                const div = document.createElement('div');
                div.className = 'p-2 hover:bg-blue-50 rounded cursor-pointer border border-transparent hover:border-blue-200';
                div.textContent = example;
                div.addEventListener('click', function() {
                    accountNameInput.value = example;
                    examplesModal.classList.add('hidden');
                });
                examplesList.appendChild(div);
            });
        }
        
        examplesModal.classList.remove('hidden');
    });

    closeExamplesModal.addEventListener('click', function() {
        examplesModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    examplesModal.addEventListener('click', function(e) {
        if (e.target === examplesModal) {
            examplesModal.classList.add('hidden');
        }
    });

    // Save & New functionality
    saveAndNewBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Create a hidden input to indicate "save and new"
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'save_and_new';
        hiddenInput.value = '1';
        accountForm.appendChild(hiddenInput);
        
        // Submit form
        accountForm.submit();
    });

    // Update account type on form submit
    accountForm.addEventListener('submit', function(e) {
        // Validate required fields
        if (isSubaccountCheckbox.checked) {
            if (!parentIdSelect.value) {
                e.preventDefault();
                alert('Please select a parent account');
                isSubaccountCheckbox.focus();
                return false;
            }
            // Ensure account_type is set from parent
            const selectedParent = parentIdSelect.options[parentIdSelect.selectedIndex];
            if (selectedParent.value) {
                const parentAccountType = selectedParent.getAttribute('data-account-type');
                if (parentAccountType) {
                    accountTypeSelect.value = parentAccountType;
                }
            }
        } else {
            // For parent accounts, ensure account type is selected
            if (!accountTypeSelect.value) {
                e.preventDefault();
                alert('Please select an account type');
                accountTypeSelect.focus();
                return false;
            }
        }
        
        // Ensure account name is filled
        if (!accountNameInput.value.trim()) {
            e.preventDefault();
            alert('Please enter an account name');
            accountNameInput.focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection
