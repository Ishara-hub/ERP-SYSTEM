@extends('layouts.modern')

@section('title', 'Edit Account')
@section('breadcrumb', 'Edit Account')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Edit Account</h2>
                <p class="text-sm text-gray-500">Update account information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('accounts.show', $account) }}" class="btn-outline text-sm px-4 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Account
                </a>
                <a href="{{ route('accounts.index') }}" class="btn-outline text-sm px-4 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Accounts
                </a>
            </div>
        </div>
    </div>

    <!-- Account Information Card -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Account Details</h3>
        </div>
        
        <form action="{{ route('accounts.update', $account) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account Code -->
                <div>
                    <label for="account_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Account Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="account_code" 
                           id="account_code"
                           value="{{ old('account_code', $account->account_code) }}"
                           class="form-control @error('account_code') border-red-500 @enderror"
                           required>
                    @error('account_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Type -->
                <div>
                    <label for="account_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Account Type <span class="text-red-500">*</span>
                    </label>
                    <select name="account_type" 
                            id="account_type"
                            class="form-control @error('account_type') border-red-500 @enderror"
                            required>
                        <option value="">Select Account Type</option>
                        @foreach($accountTypes as $key => $value)
                            <option value="{{ $key }}" {{ old('account_type', $account->account_type) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Name -->
                <div class="md:col-span-2">
                    <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Account Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="account_name" 
                           id="account_name"
                           value="{{ old('account_name', $account->account_name) }}"
                           class="form-control @error('account_name') border-red-500 @enderror"
                           placeholder="Enter account name"
                           required>
                    @error('account_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent Account -->
                <div class="md:col-span-2">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Parent Account
                    </label>
                    <select name="parent_id" 
                            id="parent_id"
                            class="form-control @error('parent_id') border-red-500 @enderror">
                        <option value="">No Parent (Top Level Account)</option>
                        @foreach($groupedParentAccounts as $type => $accounts)
                            <optgroup label="{{ $type }}">
                                @foreach($accounts as $parentAccount)
                                    <option value="{{ $parentAccount->id }}" 
                                            {{ old('parent_id', $account->parent_id) == $parentAccount->id ? 'selected' : '' }}
                                            {{ $parentAccount->id == $account->id ? 'disabled' : '' }}>
                                        {{ $parentAccount->account_code }} - {{ $parentAccount->account_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        @if($account->parent)
                            Current parent: {{ $account->parent->account_name }} ({{ $account->parent->account_code }})
                        @else
                            This is a top-level account
                        @endif
                    </p>
                </div>

                <!-- Opening Balance -->
                <div>
                    <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">
                        Opening Balance
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" 
                               name="opening_balance" 
                               id="opening_balance"
                               value="{{ old('opening_balance', $account->opening_balance) }}"
                               step="0.01"
                               min="0"
                               class="form-control pl-7 @error('opening_balance') border-red-500 @enderror"
                               placeholder="0.00">
                    </div>
                    @error('opening_balance')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                        Sort Order
                    </label>
                    <input type="number" 
                           name="sort_order" 
                           id="sort_order"
                           value="{{ old('sort_order', $account->sort_order) }}"
                           min="0"
                           class="form-control @error('sort_order') border-red-500 @enderror"
                           placeholder="0">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description"
                              rows="3"
                              class="form-control @error('description') border-red-500 @enderror"
                              placeholder="Enter account description">{{ old('description', $account->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active"
                               value="1"
                               {{ old('is_active', $account->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Account is active
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('accounts.show', $account) }}" class="btn-outline px-4 py-2">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-6 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Account
                </button>
            </div>
        </form>
    </div>

    <!-- Account Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Current Balance</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($account->current_balance, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Sub-Accounts</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $account->children->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Transactions</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $account->transactions->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white rounded-lg shadow-sm border border-red-200">
        <div class="px-6 py-4 border-b border-red-200 bg-red-50">
            <h3 class="text-lg font-medium text-red-900">Danger Zone</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-red-900">Delete Account</h4>
                    <p class="text-sm text-red-600">Once you delete an account, there is no going back. Please be certain.</p>
                </div>
                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="btn-danger px-4 py-2 confirm-delete"
                            data-confirm-message="Are you sure you want to delete {{ $account->account_name }}? This action cannot be undone.">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.form-control {
    @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}

.btn-outline {
    @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}

.btn-danger {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });

    // Confirm delete functionality
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-message');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Parent account change handler
    const parentSelect = document.getElementById('parent_id');
    const accountTypeSelect = document.getElementById('account_type');
    
    parentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Extract account type from parent account
            const parentAccountType = getAccountTypeFromParent(selectedOption.text);
            if (parentAccountType && accountTypeSelect.value !== parentAccountType) {
                if (confirm('Changing the parent account will also change the account type. Continue?')) {
                    accountTypeSelect.value = parentAccountType;
                } else {
                    this.value = '';
                }
            }
        }
    });

    function getAccountTypeFromParent(parentText) {
        const accountTypes = ['Asset', 'Liability', 'Equity', 'Income', 'Expense'];
        for (let type of accountTypes) {
            if (parentText.includes(type)) {
                return type;
            }
        }
        return '';
    }
});
</script>
@endpush
@endsection
