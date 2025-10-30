@extends('layouts.modern')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create Payment
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Record a payment for a purchase order
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('payments.web.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('payments.web.store') }}" class="p-6">
                @csrf

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Purchase Order Selection -->
                <div class="mb-6">
                    <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-2">Purchase Order <span class="text-red-500">*</span></label>
                    <select name="purchase_order_id" id="purchase_order_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('purchase_order_id') border-red-500 @enderror">
                        <option value="">Select Purchase Order</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}" 
                                    data-total="{{ $po->total_amount }}"
                                    data-balance="{{ $po->balance_due }}"
                                    data-supplier="{{ $po->supplier->name }}"
                                    {{ old('purchase_order_id', $purchaseOrder?->id) == $po->id ? 'selected' : '' }}>
                                {{ $po->po_number }} - {{ $po->supplier->name }} (${{ number_format($po->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purchase Order Details (Hidden by default) -->
                <div id="po-details" class="mb-6 hidden">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Purchase Order Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Supplier:</span>
                                <span id="po-supplier" class="ml-2 font-medium text-gray-900"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Total Amount:</span>
                                <span id="po-total" class="ml-2 font-medium text-gray-900"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Balance Due:</span>
                                <span id="po-balance" class="ml-2 font-medium text-red-600"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('payment_date') border-red-500 @enderror">
                        @error('payment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" id="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('payment_method') border-red-500 @enderror">
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Amount and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-500 @enderror" placeholder="0.00">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Reference and Notes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('reference') border-red-500 @enderror" placeholder="Check number, transaction ID, etc.">
                        @error('reference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('bank_name') border-red-500 @enderror" placeholder="Bank name">
                        @error('bank_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="check_number" class="block text-sm font-medium text-gray-700 mb-2">Check Number</label>
                        <input type="text" name="check_number" id="check_number" value="{{ old('check_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('check_number') border-red-500 @enderror" placeholder="Check number">
                        @error('check_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-2">Transaction ID</label>
                        <input type="text" name="transaction_id" id="transaction_id" value="{{ old('transaction_id') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('transaction_id') border-red-500 @enderror" placeholder="Transaction ID">
                        @error('transaction_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Fee and Received By -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="fee_amount" class="block text-sm font-medium text-gray-700 mb-2">Fee Amount</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="fee_amount" id="fee_amount" step="0.01" min="0" value="{{ old('fee_amount', 0) }}" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('fee_amount') border-red-500 @enderror" placeholder="0.00">
                        </div>
                        @error('fee_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="received_by" class="block text-sm font-medium text-gray-700 mb-2">Received By</label>
                        <input type="text" name="received_by" id="received_by" value="{{ old('received_by') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('received_by') border-red-500 @enderror" placeholder="Person who received the payment">
                        @error('received_by')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror" placeholder="Additional notes about this payment">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('payments.web.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('purchase_order_id');
    const poDetails = document.getElementById('po-details');
    const poSupplier = document.getElementById('po-supplier');
    const poTotal = document.getElementById('po-total');
    const poBalance = document.getElementById('po-balance');
    const amountInput = document.getElementById('amount');

    poSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            poSupplier.textContent = selectedOption.dataset.supplier;
            poTotal.textContent = '$' + parseFloat(selectedOption.dataset.total).toFixed(2);
            poBalance.textContent = '$' + parseFloat(selectedOption.dataset.balance).toFixed(2);
            poDetails.classList.remove('hidden');
            
            // Set max amount to balance due
            amountInput.max = selectedOption.dataset.balance;
        } else {
            poDetails.classList.add('hidden');
        }
    });

    // Trigger change event if there's a pre-selected value
    if (poSelect.value) {
        poSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
