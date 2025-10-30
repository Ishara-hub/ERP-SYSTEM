@extends('layouts.modern')

@section('title', 'Create Account')
@section('breadcrumb', 'Create Account')

@section('content')
<div class="min-h-screen max-w-6xl mx-auto bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Modern Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Left Side - Title and Breadcrumb -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Create Account</h1>
                            <p class="text-sm text-gray-500">Add new account to chart of accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Back Button -->
                <div>
                    <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Type Selection Tabs -->
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex" aria-label="Tabs">
                    <button type="button" 
                            id="parent-account-tab"
                            class="tab-button active flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors"
                            data-tab="parent">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Create Parent Account</span>
                        </div>
                    </button>
                    <button type="button" 
                            id="sub-account-tab"
                            class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors"
                            data-tab="sub">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                            <span>Create Sub-Account</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Parent Account Form -->
            <div id="parent-account-form" class="tab-content p-6">
                <form action="{{ route('accounts.store') }}" method="POST" id="account-form">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Account Code -->
                        <div>
                            <label for="account_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Code <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="account_code" 
                                       id="account_code"
                                       value="{{ old('account_code') }}"
                                       class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('account_code') border-red-500 @enderror"
                                       placeholder="Auto-generated if empty">
                                <button type="button" 
                                        id="generate-code-btn"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors"
                                        title="Generate Code">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('account_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Leave empty for auto-generation based on account type</p>
                        </div>

                        <!-- Account Type -->
                        <div>
                            <label for="account_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Type <span class="text-red-500">*</span>
                            </label>
                            <select name="account_type" 
                                    id="account_type"
                                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('account_type') border-red-500 @enderror"
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

                        <!-- Account Name -->
                        <div class="md:col-span-2">
                            <label for="account_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="account_name" 
                                   id="account_name"
                                   value="{{ old('account_name') }}"
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('account_name') border-red-500 @enderror"
                                   placeholder="Enter account name"
                                   required>
                            @error('account_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Opening Balance -->
                        <div>
                            <label for="opening_balance" class="block text-sm font-semibold text-gray-700 mb-2">
                                Opening Balance
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <input type="number" 
                                       name="opening_balance" 
                                       id="opening_balance"
                                       value="{{ old('opening_balance', 0) }}"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('opening_balance') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('opening_balance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('sort_order') border-red-500 @enderror"
                                   placeholder="0">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in listings</p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none @error('description') border-red-500 @enderror"
                                      placeholder="Enter account description">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-2">
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                                <label for="is_active" class="block text-sm font-medium text-gray-700 cursor-pointer">
                                    Account is active
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Hidden field for parent_id -->
                    <input type="hidden" name="parent_id" id="parent_id" value="">

                    <!-- Form Actions -->
                    <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sub-Account Form -->
            <div id="sub-account-form" class="tab-content p-6 hidden">
                <form action="{{ route('accounts.store') }}" method="POST" id="sub-account-form-element">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Parent Account Selection -->
                        <div class="md:col-span-2">
                            <label for="sub_parent_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Parent Account <span class="text-red-500">*</span>
                            </label>
                            <select name="parent_id" 
                                    id="sub_parent_id"
                                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('parent_id') border-red-500 @enderror"
                                    required>
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
                            <p class="mt-1 text-xs text-gray-500">The sub-account will inherit the parent's account type</p>
                        </div>

                        <!-- Account Code -->
                        <div>
                            <label for="sub_account_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Code <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="account_code" 
                                       id="sub_account_code"
                                       value="{{ old('account_code') }}"
                                       class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('account_code') border-red-500 @enderror"
                                       placeholder="Auto-generated if empty"
                                       required>
                                <button type="button" 
                                        id="generate-sub-code-btn"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors"
                                        title="Generate Code">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('account_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Leave empty for auto-generation</p>
                        </div>

                        <!-- Account Type (Auto-filled from parent) -->
                        <div>
                            <label for="sub_account_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Type
                            </label>
                            <input type="text" 
                                   id="sub_account_type"
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-600"
                                   readonly
                                   placeholder="Inherited from parent">
                            <input type="hidden" name="account_type" id="sub_account_type_hidden">
                        </div>

                        <!-- Account Name -->
                        <div class="md:col-span-2">
                            <label for="sub_account_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="account_name" 
                                   id="sub_account_name"
                                   value="{{ old('account_name') }}"
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('account_name') border-red-500 @enderror"
                                   placeholder="Enter sub-account name"
                                   required>
                            @error('account_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Opening Balance -->
                        <div>
                            <label for="sub_opening_balance" class="block text-sm font-semibold text-gray-700 mb-2">
                                Opening Balance
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <input type="number" 
                                       name="opening_balance" 
                                       id="sub_opening_balance"
                                       value="{{ old('opening_balance', 0) }}"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('opening_balance') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('opening_balance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sub_sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sub_sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('sort_order') border-red-500 @enderror"
                                   placeholder="0">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="sub_description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="sub_description"
                                      rows="3"
                                      class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none @error('description') border-red-500 @enderror"
                                      placeholder="Enter sub-account description">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-2">
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="sub_is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                                <label for="sub_is_active" class="block text-sm font-medium text-gray-700 cursor-pointer">
                                    Account is active
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Create Sub-Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.tab-button {
    border: 1px solid transparent;
    color: #6b7280;
    background: transparent;
    cursor: pointer;
}

.tab-button:hover {
    color: #374151;
    background-color: #f9fafb;
}

.tab-button.active {
    border-color: #3b82f6;
    color: #2563eb;
    background-color: white;
    border-bottom-color: transparent;
    margin-bottom: -1px;
}

.tab-content {
    display: block;
    animation: fadeIn 0.3s ease-in;
}

.tab-content.hidden {
    display: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const parentTab = document.getElementById('parent-account-tab');
    const subTab = document.getElementById('sub-account-tab');
    const parentForm = document.getElementById('parent-account-form');
    const subForm = document.getElementById('sub-account-form');

    function switchTab(tab) {
        if (tab === 'parent') {
            parentTab.classList.add('active');
            subTab.classList.remove('active');
            parentForm.classList.remove('hidden');
            subForm.classList.add('hidden');
        } else {
            subTab.classList.add('active');
            parentTab.classList.remove('active');
            subForm.classList.remove('hidden');
            parentForm.classList.add('hidden');
        }
    }

    parentTab.addEventListener('click', function() {
        switchTab('parent');
    });

    subTab.addEventListener('click', function() {
        switchTab('sub');
    });

    // Auto-generate account code functionality for parent account
    const generateCodeBtn = document.getElementById('generate-code-btn');
    const accountCodeInput = document.getElementById('account_code');
    const accountTypeSelect = document.getElementById('account_type');

    generateCodeBtn.addEventListener('click', function() {
        const accountType = accountTypeSelect.value;
        if (accountType) {
            generateAccountCode(accountType, accountCodeInput);
        } else {
            alert('Please select an account type first');
        }
    });

    // Auto-generate account code when account type changes (parent form)
    accountTypeSelect.addEventListener('change', function() {
        if (this.value && !accountCodeInput.value) {
            generateAccountCode(this.value, accountCodeInput);
        }
    });

    // Sub-account form functionality
    const subParentSelect = document.getElementById('sub_parent_id');
    const subAccountTypeInput = document.getElementById('sub_account_type');
    const subAccountTypeHidden = document.getElementById('sub_account_type_hidden');
    const generateSubCodeBtn = document.getElementById('generate-sub-code-btn');
    const subAccountCodeInput = document.getElementById('sub_account_code');

    subParentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const accountType = selectedOption.getAttribute('data-account-type');
            if (accountType) {
                subAccountTypeInput.value = accountType;
                subAccountTypeHidden.value = accountType;
                
                // Auto-generate code if field is empty
                if (!subAccountCodeInput.value) {
                    generateAccountCode(accountType, subAccountCodeInput);
                }
            }
        } else {
            subAccountTypeInput.value = '';
            subAccountTypeHidden.value = '';
        }
    });

    generateSubCodeBtn.addEventListener('click', function() {
        const accountType = subAccountTypeHidden.value;
        if (accountType) {
            generateAccountCode(accountType, subAccountCodeInput);
        } else {
            alert('Please select a parent account first');
        }
    });

    function generateAccountCode(accountType, inputElement) {
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.account_code) {
                inputElement.value = data.account_code;
                inputElement.classList.remove('border-red-500');
            } else if (data.error) {
                throw new Error(data.error);
            } else {
                throw new Error('No code returned from server');
            }
        })
        .catch(error => {
            console.error('Error generating account code:', error);
            // Fallback: generate a simple code
            const prefixes = {
                'Asset': '1000',
                'Liability': '2000',
                'Equity': '3000',
                'Income': '4000',
                'Expense': '5000'
            };
            const prefix = prefixes[accountType] || '0000';
            const timestamp = Date.now().toString().slice(-3);
            inputElement.value = prefix + timestamp;
            alert('Unable to generate code automatically. Please enter manually or try again.');
        });
    }

    // Form validation and submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            let emptyFields = [];

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                    const label = field.closest('div')?.querySelector('label')?.textContent?.trim() || field.name;
                    emptyFields.push(label);
                } else {
                    field.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields:\n' + emptyFields.join('\n'));
                return false;
            }
        });
    });

    // Real-time validation
    document.querySelectorAll('input[required], select[required]').forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    });
});
</script>
@endpush
@endsection
