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
            <p class="mt-1 text-sm text-gray-600">Create a new vendor bill with expenses</p>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <form action="{{ route('bills.enter-bill.store') }}" method="POST" class="p-6">
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

                <!-- Supplier Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Liability Account <span class="text-red-500">*</span></label>
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

                <!-- Items Section -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bill Items</h3>
                    
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-1/3 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense Account</th>
                                    <th class="w-1/3 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="w-1/4 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="w-16 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                    <th class="w-12 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-3 py-2">
                                        <select class="form-select expense-account-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[0][expense_account_id]" required>
                                            <option value="">Select Expense Account</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[0][description]" placeholder="Description" required
                                               class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" name="items[0][amount]" step="0.01" min="0.01" required
                                               class="amount block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" name="items[0][tax_rate]" step="0.01" min="0" max="100" value="0"
                                               class="tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
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
    let itemIndex = 1;

    // Add item row
    document.getElementById('addItem').addEventListener('click', function() {
        const newRow = `
            <tr>
                <td class="px-3 py-2">
                    <select class="form-select expense-account-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="items[${itemIndex}][expense_account_id]" required>
                        <option value="">Select Expense Account</option>
                        @foreach($expenseAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="items[${itemIndex}][description]" placeholder="Description" required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="items[${itemIndex}][amount]" step="0.01" min="0.01" required
                           class="amount block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="items[${itemIndex}][tax_rate]" step="0.01" min="0" max="100" value="0"
                           class="tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
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

    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('tr').remove();
            calculateTotal();
        }
    });

    // Amount or tax rate change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('amount') || e.target.classList.contains('tax-rate')) {
            calculateTotal();
        }
    });

    function calculateTotal() {
        let subtotal = 0;
        let taxAmount = 0;
        
        // Calculate from bill items
        document.querySelectorAll('#itemsTableBody tr').forEach(function(row) {
            const amount = parseFloat(row.querySelector('.amount')?.value) || 0;
            const taxRate = parseFloat(row.querySelector('.tax-rate')?.value) || 0;
            
            const lineTaxAmount = amount * (taxRate / 100);
            const lineTotal = amount + lineTaxAmount;
            
            subtotal += amount;
            taxAmount += lineTaxAmount;
        });
        
        const total = subtotal + taxAmount;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = '$' + taxAmount.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }

    // Initial calculation
    calculateTotal();
});
</script>
@endsection