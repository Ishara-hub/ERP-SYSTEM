@extends('layouts.modern')

@section('title', 'Enter New Bill')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('bills.enter-bill.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Enter Bills
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Enter New Bill</h1>
            <p class="mt-1 text-sm text-gray-600">Create a vendor bill with expenses or receive items from purchase order</p>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <form action="{{ route('bills.enter-bill.store') }}" method="POST" class="p-6" id="bill-form">
                @csrf
                
                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Supplier and PO Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select name="supplier_id" id="supplier_id" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Purchase Order <span class="text-xs text-gray-500">(Optional)</span>
                        </label>
                        <select name="purchase_order_id" id="purchase_order_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select PO (for receiving items)</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}">
                                    {{ $po->po_number }} - {{ $po->supplier->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Liability Account <span class="text-red-500">*</span>
                        </label>
                        <select name="liability_account_id" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select Liability Account</option>
                            @foreach($liabilityAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('liability_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Bill Details -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bill Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="bill_date" 
                               value="{{ old('bill_date', now()->format('Y-m-d')) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="due_date" 
                               value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Terms</label>
                        <input type="text" name="terms" value="{{ old('terms') }}" placeholder="e.g., Net 30"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Memo</label>
                    <textarea name="memo" rows="3" placeholder="Internal memo"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('memo') }}</textarea>
                </div>

                <!-- Tabs for Expenses and Items -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button type="button" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" data-tab="expenses">
                                Expenses <span id="expensesTotal" class="ml-2 text-xs font-normal">$0.00</span>
                            </button>
                            <button type="button" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="items">
                                Items <span id="itemsTotal" class="ml-2 text-xs font-normal">$0.00</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Expenses Tab Content -->
                    <div id="expenses-tab" class="tab-content mt-4">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense Account</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                        <th class="w-12 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expensesTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select class="form-select expense-account-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="expenses[0][expense_account_id]">
                                                <option value="">Select Expense Account</option>
                                                @foreach($expenseAccounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="expenses[0][description]" placeholder="Description"
                                                   class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="expenses[0][amount]" step="0.01" min="0"
                                                   class="expense-amount block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="expenses[0][tax_rate]" step="0.01" min="0" max="100" value="0"
                                                   class="expense-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-900 remove-expense">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" id="addExpense" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Expense
                            </button>
                        </div>
                    </div>

                    <!-- Items Tab Content -->
                    <div id="items-tab" class="tab-content mt-4" style="display: none;">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                        <th class="w-12 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[0][item_id]">
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-price="{{ $item->cost }}" data-account="{{ $item->cogs_account_id }}">
                                                        {{ $item->item_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="items[0][description]" placeholder="Description"
                                                   class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="items[0][quantity]" step="0.01" min="0"
                                                   class="item-quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="items[0][unit_price]" step="0.01" min="0"
                                                   class="item-unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="items[0][tax_rate]" step="0.01" min="0" max="100" value="0"
                                                   class="item-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-900 remove-item">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" id="addItem" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Total Section -->
                <div class="flex justify-end mb-6">
                    <div class="w-full max-w-md">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span id="subtotal" class="font-medium">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span id="taxAmount" class="font-medium">$0.00</span>
                                </div>
                                <hr class="border-gray-300">
                                <div class="flex justify-between text-base font-semibold">
                                    <span>Total:</span>
                                    <span id="total">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('bills.enter-bill.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let expenseIndex = 1;
    let itemIndex = 1;
    
    // Available items for PO loading
    const availableItems = @json($items);
    
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            
            // Update tab buttons
            tabBtns.forEach(b => {
                b.classList.remove('border-blue-500', 'text-blue-600', 'active');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600', 'active');
            
            // Update tab content
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(tab + '-tab').style.display = 'block';
            
            calculateTotal();
        });
    });

    // PO selection handler
    const poSelect = document.getElementById('purchase_order_id');
    const supplierSelect = document.getElementById('supplier_id');
    
    // Filter PO dropdown based on supplier
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        poSelect.querySelectorAll('option').forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            const optionSupplier = option.getAttribute('data-supplier');
            if (optionSupplier && optionSupplier !== supplierId) {
                option.style.display = 'none';
            } else {
                option.style.display = 'block';
            }
        });
        
        if (supplierId && poSelect.value) {
            const selectedOption = poSelect.options[poSelect.selectedIndex];
            if (selectedOption.getAttribute('data-supplier') !== supplierId) {
                poSelect.value = '';
            }
        }
    });
    
    // Load PO items when PO is selected
    poSelect.addEventListener('change', function() {
        if (this.value) {
            loadPOItems(this.value);
        } else {
            // Clear items table
            document.getElementById('itemsTableBody').innerHTML = `
                <tr>
                    <td class="px-3 py-2">
                        <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[0][item_id]">
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-price="{{ $item->cost }}" data-account="{{ $item->cogs_account_id }}">
                                    {{ $item->item_name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="items[0][description]" placeholder="Description"
                               class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="items[0][quantity]" step="0.01" min="0"
                               class="item-quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="items[0][unit_price]" step="0.01" min="0"
                               class="item-unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="items[0][tax_rate]" step="0.01" min="0" max="100" value="0"
                               class="item-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                    </td>
                    <td class="px-2 py-2 text-center">
                        <button type="button" class="text-red-600 hover:text-red-900 remove-item">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
            itemIndex = 1;
            // Switch to Items tab
            document.querySelector('[data-tab="items"]').click();
        }
    });
    
    // Function to load PO items
    function loadPOItems(poId) {
        fetch(`{{ route('bills.enter-bill.get-po-items') }}?po_id=${poId}`)
            .then(response => response.json())
            .then(data => {
                const itemsBody = document.getElementById('itemsTableBody');
                itemsBody.innerHTML = '';
                
                if (data.length === 0) {
                    itemsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">No items found in this Purchase Order</td></tr>';
                    return;
                }
                
                data.forEach((item, index) => {
                    let optionsHtml = '<option value="">Select Item</option>';
                    availableItems.forEach(function(itm) {
                        const selected = (item.item_id == itm.id) ? 'selected' : '';
                        optionsHtml += `<option value="${itm.id}" data-price="${itm.cost || 0}" data-account="${itm.cogs_account_id || ''}" ${selected}>${itm.item_name}</option>`;
                    });
                    
                    const row = `
                        <tr>
                            <td class="px-3 py-2">
                                <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[${index}][item_id]">
                                    ${optionsHtml}
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="items[${index}][description]" placeholder="Description" value="${item.description || ''}"
                                       class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[${index}][quantity]" step="0.01" min="0" value="${item.quantity || ''}"
                                       class="item-quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="${item.unit_price || ''}"
                                       class="item-unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" name="items[${index}][tax_rate]" step="0.01" min="0" max="100" value="${item.tax_rate || 0}"
                                       class="item-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" class="text-red-600 hover:text-red-900 remove-item">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `;
                    itemsBody.insertAdjacentHTML('beforeend', row);
                });
                
                itemIndex = data.length;
                calculateTotal();
                
                // Switch to Items tab
                document.querySelector('[data-tab="items"]').click();
            })
            .catch(error => {
                console.error('Error loading PO items:', error);
                alert('Failed to load purchase order items. Please try again.');
            });
    }

    // Add Expense row
    document.getElementById('addExpense').addEventListener('click', function() {
        const newRow = `
            <tr>
                <td class="px-3 py-2">
                    <select class="form-select expense-account-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="expenses[${expenseIndex}][expense_account_id]">
                        <option value="">Select Expense Account</option>
                        @foreach($expenseAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="expenses[${expenseIndex}][description]" placeholder="Description"
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="expenses[${expenseIndex}][amount]" step="0.01" min="0"
                           class="expense-amount block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="expenses[${expenseIndex}][tax_rate]" step="0.01" min="0" max="100" value="0"
                           class="expense-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-900 remove-expense">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('expensesTableBody').insertAdjacentHTML('beforeend', newRow);
        expenseIndex++;
    });

    // Add Item row
    document.getElementById('addItem').addEventListener('click', function() {
        const newRow = `
            <tr>
                <td class="px-3 py-2">
                    <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[${itemIndex}][item_id]">
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->cost }}" data-account="{{ $item->cogs_account_id }}">
                                {{ $item->item_name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="items[${itemIndex}][description]" placeholder="Description"
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="items[${itemIndex}][quantity]" step="0.01" min="0"
                           class="item-quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="items[${itemIndex}][unit_price]" step="0.01" min="0"
                           class="item-unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="items[${itemIndex}][tax_rate]" step="0.01" min="0" max="100" value="0"
                           class="item-tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-900 remove-item">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('itemsTableBody').insertAdjacentHTML('beforeend', newRow);
        itemIndex++;
    });

    // Item select - auto fill price and account
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-select') && e.target.value) {
            const option = e.target.options[e.target.selectedIndex];
            const unitPriceInput = e.target.closest('tr').querySelector('.item-unit-price');
            if (option.getAttribute('data-price')) {
                unitPriceInput.value = option.getAttribute('data-price');
                calculateTotal();
            }
        }
    });

    // Remove row handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-expense')) {
            e.target.closest('tr').remove();
            calculateTotal();
        }
        if (e.target.closest('.remove-item')) {
            e.target.closest('tr').remove();
            calculateTotal();
        }
    });

    // Calculate totals on input change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('expense-amount') || e.target.classList.contains('expense-tax-rate') || 
            e.target.classList.contains('item-quantity') || e.target.classList.contains('item-unit-price') || 
            e.target.classList.contains('item-tax-rate')) {
            calculateTotal();
        }
    });

    function calculateTotal() {
        let subtotal = 0;
        let taxAmount = 0;
        let expensesTotal = 0;
        let itemsTotal = 0;
        
        // Calculate expenses
        document.querySelectorAll('#expensesTableBody tr').forEach(function(row) {
            const amount = parseFloat(row.querySelector('.expense-amount')?.value) || 0;
            const taxRate = parseFloat(row.querySelector('.expense-tax-rate')?.value) || 0;
            const lineTax = amount * (taxRate / 100);
            expensesTotal += amount + lineTax;
            subtotal += amount;
            taxAmount += lineTax;
        });
        
        // Calculate items
        document.querySelectorAll('#itemsTableBody tr').forEach(function(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity')?.value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-unit-price')?.value) || 0;
            const taxRate = parseFloat(row.querySelector('.item-tax-rate')?.value) || 0;
            const amount = quantity * unitPrice;
            const lineTax = amount * (taxRate / 100);
            itemsTotal += amount + lineTax;
            subtotal += amount;
            taxAmount += lineTax;
        });
        
        const total = subtotal + taxAmount;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = '$' + taxAmount.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
        document.getElementById('expensesTotal').textContent = '$' + expensesTotal.toFixed(2);
        document.getElementById('itemsTotal').textContent = '$' + itemsTotal.toFixed(2);
    }

    // Initial calculation
    calculateTotal();

    // Form validation
    document.getElementById('bill-form').addEventListener('submit', function(e) {
        const hasExpenses = document.querySelectorAll('#expensesTableBody tr .expense-amount').length > 0 && 
                          Array.from(document.querySelectorAll('#expensesTableBody tr .expense-amount')).some(input => parseFloat(input.value) > 0);
        const hasItems = document.querySelectorAll('#itemsTableBody tr .item-quantity').length > 0 && 
                        Array.from(document.querySelectorAll('#itemsTableBody tr .item-quantity')).some(input => parseFloat(input.value) > 0);
        
        if (!hasExpenses && !hasItems) {
            e.preventDefault();
            alert('Please add at least one expense or item line.');
            return false;
        }
    });
});
</script>
@endsection