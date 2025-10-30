@extends('layouts.modern')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create Quotation
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new quotation for your customer
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('quotations.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Quotations
                </a>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('quotations.store') }}" method="POST" class="mt-6">
            @csrf
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Customer Selection -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select name="customer_id" id="customer_id" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="quotation_date" class="block text-sm font-medium text-gray-700">
                                Quotation Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="quotation_date" id="quotation_date" value="{{ old('quotation_date', date('Y-m-d')) }}" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('quotation_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Quotation Details -->
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="valid_until" class="block text-sm font-medium text-gray-700">
                                Valid Until
                            </label>
                            <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('valid_until')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700">
                                Payment Terms
                            </label>
                            <select name="payment_terms" id="payment_terms"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select payment terms</option>
                                <option value="Net 15">Net 15</option>
                                <option value="Net 30">Net 30</option>
                                <option value="Net 45">Net 45</option>
                                <option value="Net 60">Net 60</option>
                                <option value="Due on Receipt">Due on Receipt</option>
                            </select>
                            @error('payment_terms')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="shipping_method" class="block text-sm font-medium text-gray-700">
                                Shipping Method
                            </label>
                            <input type="text" name="shipping_method" id="shipping_method" value="{{ old('shipping_method') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="e.g., Standard Shipping, Express">
                            @error('shipping_method')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700">
                                Shipping Address
                            </label>
                            <textarea name="shipping_address" id="shipping_address" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="Enter shipping address">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="mt-6">
                        <label for="billing_address" class="block text-sm font-medium text-gray-700">
                            Billing Address
                        </label>
                        <textarea name="billing_address" id="billing_address" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="Enter billing address">{{ old('billing_address') }}</textarea>
                        @error('billing_address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes and Terms -->
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="Internal notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="terms_conditions" class="block text-sm font-medium text-gray-700">
                                Terms & Conditions
                            </label>
                            <textarea name="terms_conditions" id="terms_conditions" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="Terms and conditions">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quotation Items</h3>
                        <div id="line-items-container">
                            <div class="line-item border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Item</label>
                                        <select name="line_items[0][item_id]" required
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                            <option value="">Select item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->item_number }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <input type="text" name="line_items[0][description]" required
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" name="line_items[0][quantity]" step="0.01" min="0.01" required
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                        <input type="number" name="line_items[0][unit_price]" step="0.01" min="0" required
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                        <input type="number" name="line_items[0][tax_rate]" step="0.01" min="0" max="100"
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Discount Rate (%)</label>
                                        <input type="number" name="line_items[0][discount_rate]" step="0.01" min="0" max="100"
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-line-item" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('quotations.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Quotation
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let lineItemIndex = 1;

document.getElementById('add-line-item').addEventListener('click', function() {
    const container = document.getElementById('line-items-container');
    const newLineItem = document.createElement('div');
    newLineItem.className = 'line-item border border-gray-200 rounded-lg p-4 mb-4';
    newLineItem.innerHTML = `
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Item</label>
                <select name="line_items[${lineItemIndex}][item_id]" required
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Select item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->item_number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" name="line_items[${lineItemIndex}][description]" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="line_items[${lineItemIndex}][quantity]" step="0.01" min="0.01" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                <input type="number" name="line_items[${lineItemIndex}][unit_price]" step="0.01" min="0" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                <input type="number" name="line_items[${lineItemIndex}][tax_rate]" step="0.01" min="0" max="100"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Discount Rate (%)</label>
                <input type="number" name="line_items[${lineItemIndex}][discount_rate]" step="0.01" min="0" max="100"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
        </div>
        <div class="mt-2 flex justify-end">
            <button type="button" class="remove-line-item text-red-600 hover:text-red-900 text-sm">
                Remove Item
            </button>
        </div>
    `;
    
    container.appendChild(newLineItem);
    lineItemIndex++;
    
    // Add event listener for remove button
    newLineItem.querySelector('.remove-line-item').addEventListener('click', function() {
        newLineItem.remove();
    });
});

// Add event listeners for existing remove buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-line-item')) {
        e.target.closest('.line-item').remove();
    }
});
</script>
@endsection
