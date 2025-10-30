@extends('layouts.modern')

@section('title', 'Create Purchase Order')
@section('breadcrumb', 'Create Purchase Order')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
                <p class="mt-1 text-sm text-gray-600">Add a new purchase order to the system</p>
            </div>
            <a href="{{ route('purchase-orders.web.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Purchase Orders
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Purchase Order Form -->
    <div class="bg-white rounded-lg shadow-sm border">
        <form action="{{ route('purchase-orders.web.store') }}" method="POST" class="p-6" id="purchaseForm">
            @csrf
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border mb-6">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Purchase Date -->
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Purchase Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="order_date" 
                                   id="order_date"
                                   value="{{ old('order_date', date('Y-m-d')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('order_date') border-red-500 @enderror"
                                   required>
                            @error('order_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expected Delivery -->
                        <div>
                            <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Expected Delivery
                            </label>
                            <input type="date" 
                                   name="expected_delivery_date" 
                                   id="expected_delivery_date"
                                   value="{{ old('expected_delivery_date') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('expected_delivery_date') border-red-500 @enderror">
                            @error('expected_delivery_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <select name="supplier_id" 
                                    id="supplier_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('supplier_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reference -->
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">
                                Reference
                            </label>
                            <input type="text" 
                                   name="reference" 
                                   id="reference"
                                   value="{{ old('reference') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('reference') border-red-500 @enderror"
                                   placeholder="PO Reference">
                            @error('reference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Second Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <!-- Terms -->
                        <div>
                            <label for="terms" class="block text-sm font-medium text-gray-700 mb-1">
                                Terms
                            </label>
                            <input type="text" 
                                   name="terms" 
                                   id="terms"
                                   value="{{ old('terms') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('terms') border-red-500 @enderror"
                                   placeholder="e.g., Net 30">
                            @error('terms')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Shipping Address -->
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Shipping Address
                            </label>
                            <textarea name="shipping_address" 
                                        id="shipping_address"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('shipping_address') border-red-500 @enderror"
                                        placeholder="Enter shipping address">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Billing Address -->
                        <div>
                            <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Billing Address
                            </label>
                            <textarea name="billing_address" 
                                        id="billing_address"
                                        rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 @error('billing_address') border-red-500 @enderror"
                                        placeholder="Enter billing address">{{ old('billing_address') }}</textarea>
                            @error('billing_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Purchase Items</h3>
                    <button type="button" 
                            id="addItem"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Item
                    </button>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/3">
                                        Item
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/8">
                                        Quantity
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Unit Price
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Total
                                    </th>
                                    <th class="px-2 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                        Description
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-16">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr class="item-row hover:bg-gray-50">
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <select name="items[0][item_id]" 
                                                class="item-select block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                                required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-price="{{ $item->sales_price ?? $item->cost ?? 0 }}"
                                                        data-description="{{ $item->item_name }}">
                                                    {{ $item->item_number }} - {{ $item->item_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <input type="number" 
                                               name="items[0][quantity]" 
                                               class="quantity-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               min="0" 
                                               step="0" 
                                               required>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <input type="number" 
                                               name="items[0][unit_price]" 
                                               class="unit-price-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               step="0.01" 
                                               min="0" 
                                               required>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <input type="number" 
                                               name="items[0][total_cost]" 
                                               class="total-cost-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm" 
                                               step="0.01" 
                                               readonly>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <input type="text" 
                                               name="items[0][description]" 
                                               class="description-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm" 
                                               readonly>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-right text-sm font-medium">
                                        <button type="button" 
                                                class="remove-item-btn inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed" 
                                                disabled>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="mb-6">
                <div class="flex justify-end">
                    <div class="w-80">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                    <div class="w-32">
                                        <input type="number" 
                                               name="subtotal" 
                                               id="subtotal"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm text-right font-medium" 
                                               step="0.01" 
                                               readonly>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Shipping:</span>
                                    <div class="w-32">
                                        <input type="number" 
                                               name="shipping_amount" 
                                               id="shipping_amount"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-right" 
                                               step="0.01" 
                                               min="0" 
                                               value="0">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Discount:</span>
                                    <div class="w-32">
                                        <input type="number" 
                                               name="discount_amount" 
                                               id="discount_amount"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-right" 
                                               step="0.01" 
                                               min="0" 
                                               value="0">
                                    </div>
                                </div>
                                <div class="border-t border-gray-300 pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                                        <div class="w-32">
                                            <input type="number" 
                                                   name="total_amount" 
                                                   id="total_amount"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm text-right font-semibold" 
                                                   step="0.01" 
                                                   readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea name="notes" 
                          id="notes"
                          rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                          placeholder="Additional notes">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('purchase-orders.web.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
                <button type="submit" 
                        name="action" 
                        value="save_and_new"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Save & New
                </button>
                <button type="submit" 
                        name="action" 
                        value="save_and_close"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save & Close
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    
    // Add item row
    document.getElementById('addItem').addEventListener('click', function() {
        itemIndex++;
        const newRow = document.querySelector('.item-row').cloneNode(true);
        
        // Update input names and clear values
        newRow.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', '[' + itemIndex + ']'));
            }
            if (input.type !== 'hidden') {
                input.value = '';
            }
        });
        
        // Enable remove button
        newRow.querySelector('.remove-item-btn').disabled = false;
        
        // Add event listeners
        addItemEventListeners(newRow);
        
        document.getElementById('itemsTableBody').appendChild(newRow);
        updateRemoveButtons();
    });
    
    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn') || e.target.closest('.remove-item-btn')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
            calculateTotals();
        }
    });
    
    // Add event listeners to existing row
    addItemEventListeners(document.querySelector('.item-row'));
    
    function addItemEventListeners(row) {
        // Item selection
        row.querySelector('.item-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Auto-fill unit price
                const unitPrice = parseFloat(selectedOption.dataset.price) || 0;
                row.querySelector('.unit-price-input').value = unitPrice.toFixed(2);
                
                // Auto-fill description
                const description = selectedOption.dataset.description || '';
                row.querySelector('.description-input').value = description;
                
                // Calculate total
                calculateRowTotal(row);
            } else {
                // Clear fields if no item selected
                row.querySelector('.unit-price-input').value = '';
                row.querySelector('.description-input').value = '';
                row.querySelector('.total-cost-input').value = '';
            }
        });
        
        // Quantity and unit price changes
        row.querySelector('.quantity-input').addEventListener('input', () => calculateRowTotal(row));
        row.querySelector('.unit-price-input').addEventListener('input', () => calculateRowTotal(row));
    }
    
    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const totalCost = quantity * unitPrice;
        row.querySelector('.total-cost-input').value = totalCost.toFixed(2);
        calculateTotals();
    }
    
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.total-cost-input').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        const shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const total = subtotal + shipping - discount;
        
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('total_amount').value = total.toFixed(2);
    }
    
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-item-btn');
            removeBtn.disabled = rows.length === 1;
        });
    }
    
    // Shipping and discount changes
    document.getElementById('shipping_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);
    
    // Form validation
    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        const itemRows = document.querySelectorAll('.item-row');
        if (itemRows.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the purchase order.');
            return false;
        }
        
        let hasValidItem = false;
        itemRows.forEach(row => {
            const itemId = row.querySelector('.item-select').value;
            const quantity = row.querySelector('.quantity-input').value;
            if (itemId && quantity) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            e.preventDefault();
            alert('Please select at least one item with quantity.');
            return false;
        }
    });
});
</script>
@endsection