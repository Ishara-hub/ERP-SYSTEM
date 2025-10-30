@extends('layouts.modern')

@section('title', 'POS Dashboard')

@section('content')
<div class="min-h-screen max-w-7xl mx-auto bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Modern Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between">
                <!-- Left Side - Title and Breadcrumb -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Create Invoice</h1>
                            <p class="text-sm text-gray-500">Create Professional Invoices</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Current Invoice Total -->
                <div class="text-right">
                    <div class="text-sm text-gray-500">Current Invoice</div>
                    <div class="text-lg font-semibold text-gray-900" id="current-invoice-total">$0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Selection Bar -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-2 py-2">
            <div class="flex items-center justify-between">
                <!-- Customer Selection -->
                <div class="flex items-center space-x-4 flex-1">
                    <div class="flex-1 max-w-xs">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">CUSTOMER:</label>
                        <div class="relative">
                            <input type="text" id="customer-search"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Search customer...">
                            
                            <!-- Search Results Dropdown -->
                            <div id="customer-dropdown" class="hidden absolute z-10 mt-1 w-full bg-white shadow-sm max-h-60 rounded-lg py-2 ring-1 ring-black ring-opacity-5 overflow-auto">
                                <!-- Customer options will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Selected Customer Display -->
                    <div id="selected-customer-display" class="ml-4 hidden">
                        <div class="bg-white rounded-md shadow-sm border border-gray-200 p-3">
                            <div class="flex items-center space-x-2">
                                <div>
                                    <h3 class="text-md font-semibold text-gray-900" id="selected-customer-name"></h3>
                                    <p class="text-sm text-gray-600" id="selected-customer-email"></p>
                                </div>
                                <button type="button" id="clear-customer-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex max-w-7xl mx-auto">
        <!-- Left Panel - Invoice Form -->
        <div class="flex-1">
            <div class="mx-auto px-2 py-2 space-y-1 max-w-7xl">
                <!-- Invoice Header Section -->
                <div class="bg-white rounded shadow-lg max-w-3xl p-2 border border-gray-100 overflow-hidden">
                    <div class="p-2">
                        <div class="grid grid-cols-3 gap-1.5">
                            <!-- Left Column -->
                            <div class="flex items-start pt-4">
                                <h1 class="text-xl font-bold text-gray-900">Invoice</h1>
                            </div>
                            <!-- Second Column -->
                            <div class="grid grid-cols-2 gap-1.5 w-full">
                                <div class="space-y-1 justify-left">
                                    <div>
                                        <label class="block text-xs font-normal text-gray-700">DATE</label>
                                        <input type="date" id="invoice-date" value="{{ date('Y-m-d') }}"
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-normal text-gray-700">INVOICE #</label>
                                        <input type="text" id="invoice-number" value="INV-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}" readonly
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-gray-50">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="grid grid-cols-2 gap-1.5">
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">BILL TO</label>
                                    <textarea id="billing-address" rows="4"
                                              class="w-full px-4 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Billing address"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">SHIP TO</label>
                                    <textarea id="shipping-address" rows="4"
                                              class="w-full px-4 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Shipping address"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="grid grid-cols-6 gap-1.5 mt-1.5">
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">P.O. NUMBER</label>
                                <input type="text" id="po-number" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">TERMS</label>
                                <select id="payment-terms" class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="">Select terms</option>
                                    <option value="Net 15">Net 15</option>
                                    <option value="Net 30" selected>Net 30</option>
                                    <option value="Net 45">Net 45</option>
                                    <option value="Net 60">Net 60</option>
                                    <option value="Due on Receipt">Due on Receipt</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">REP</label>
                                <input type="text" id="representative" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">SHIP</label>
                                <input type="date" id="ship-date"
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">VIA</label>
                                <input type="text" id="shipping-via" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">F.O.B.</label>
                                <input type="text" id="fob" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Section -->
                <div class="bg-white rounded shadow-lg p-2 mt-2 mb-2 border border-gray-100 overflow-hidden">
                    <div class="p-1">
                        <div class="flex items-center justify-between mb-2 p-2">
                            <h3 class="text-md font-semibold text-gray-700">Invoice Items</h3>
                            <button type="button" id="addItem"
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <!-- Items Table -->
                        <div class="bg-white border border-gray-200 rounded overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                                    <thead class="bg-gray-50 p-2">
                                        <tr>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">
                                                Item
                                            </th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/8">
                                                Quantity
                                            </th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                                Unit Price
                                            </th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                                Total
                                            </th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                                Description
                                            </th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                        <tr class="item-row hover:bg-gray-50">
                                            <td class="px-2 py-2 whitespace-nowrap">
                                                <select name="items[0][item_id]" 
                                                        class="item-select block w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
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
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                <input type="number" 
                                                       name="items[0][quantity]" 
                                                       class="quantity-input block w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                                       min="0" 
                                                       step="0.01" 
                                                       required>
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                <input type="number" 
                                                       name="items[0][unit_price]" 
                                                       class="unit-price-input block w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                                       step="0.01" 
                                                       min="0" 
                                                       required>
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                <input type="number" 
                                                       name="items[0][total]" 
                                                       class="total-cost-input block w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-gray-50 text-sm" 
                                                       step="0.01" 
                                                       readonly>
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                <input type="text" 
                                                       name="items[0][description]" 
                                                       class="description-input block w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-gray-50 text-sm" 
                                                       readonly>
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap text-right text-xs font-medium">
                                                <button type="button" 
                                                        class="remove-item-btn inline-flex items-center px-1 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-1 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed" 
                                                        disabled>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                </div>

                <!-- Bottom Section -->
                <div class="bg-white rounded shadow-lg p-2 mt-2 mb-2 border border-gray-100 overflow-hidden">
                    <div class="p-2">
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Left Side -->
                            <div class="space-y-2 grid grid-cols-2 gap-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700">ONLINE PAY</label>
                                    <select id="online-payment" class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="0" selected>Off</option>
                                        <option value="1">On</option>
                                    </select>
                                </div>
                                
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700">CUSTOMER MESSAGE</label>
                                    <textarea id="customer-message" rows="3"
                                              class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Message..."></textarea>
                                </div>
                            </div>
                            
                            <!-- Right Side -->
                            <div class="space-y-2 p-2">
                                <div class="text-right space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-md font-semibold text-gray-700">TOTAL</span>
                                        <span id="total-amount" class="text-lg font-semibold text-gray-900">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-semibold text-gray-700">PAYMENTS APPLIED</span>
                                        <span id="payments-applied" class="text-lg font-semibold text-gray-900">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-gray-300">
                                        <span class="text-md font-bold text-gray-900">BALANCE DUE</span>
                                        <span id="balance-due" class="text-lg font-semibold text-yellow-600">0.00</span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                                    <button type="button" id="save-draft-btn"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            disabled>
                                        <svg class="w-3 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Save & Close
                                    </button>
                                    <button type="button" id="create-invoice-btn"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            disabled>
                                        <svg class="w-3 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Save & New
                                    </button>
                                    <button type="button" id="clear-btn"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Processing...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customers = @json($customers);
    const items = @json($items);
    let selectedCustomer = null;
    let itemIndex = 0;

    // Customer search functionality
    const customerSearch = document.getElementById('customer-search');
    const customerDropdown = document.getElementById('customer-dropdown');
    const selectedCustomerDisplay = document.getElementById('selected-customer-display');
    const selectedCustomerName = document.getElementById('selected-customer-name');
    const selectedCustomerEmail = document.getElementById('selected-customer-email');
    const clearCustomerBtn = document.getElementById('clear-customer-btn');

    customerSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (query.length < 2) {
            customerDropdown.classList.add('hidden');
            return;
        }

        const filteredCustomers = customers.filter(customer => 
            customer.name.toLowerCase().includes(query) ||
            (customer.email && customer.email.toLowerCase().includes(query)) ||
            (customer.phone && customer.phone.toLowerCase().includes(query))
        );

        if (filteredCustomers.length > 0) {
            customerDropdown.innerHTML = filteredCustomers.map(customer => `
                <div class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                     data-customer-id="${customer.id}">
                    <div class="flex-shrink-0">
                        <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900">${customer.name}</div>
                        <div class="text-sm text-gray-500">${customer.email || ''}</div>
                    </div>
                </div>
            `).join('');
            customerDropdown.classList.remove('hidden');
        } else {
            customerDropdown.classList.add('hidden');
        }
    });

    customerDropdown.addEventListener('click', function(e) {
        const customerElement = e.target.closest('[data-customer-id]');
        if (customerElement) {
            const customerId = parseInt(customerElement.dataset.customerId);
            selectedCustomer = customers.find(c => c.id === customerId);
            
            selectedCustomerName.textContent = selectedCustomer.name;
            selectedCustomerEmail.textContent = selectedCustomer.email || '';
            selectedCustomerDisplay.classList.remove('hidden');
            customerSearch.value = '';
            customerDropdown.classList.add('hidden');
            
            // Auto-fill billing address
            if (selectedCustomer.address) {
                document.getElementById('billing-address').value = selectedCustomer.address;
            }
            
            updateButtons();
        }
    });

    clearCustomerBtn.addEventListener('click', function() {
        selectedCustomer = null;
        selectedCustomerDisplay.classList.add('hidden');
        document.getElementById('billing-address').value = '';
        document.getElementById('shipping-address').value = '';
        updateButtons();
    });

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
        
        // Enable remove button for the new row
        const removeBtn = newRow.querySelector('.remove-item-btn');
        removeBtn.disabled = false;
        
        document.getElementById('itemsTableBody').appendChild(newRow);
        updateRemoveButtons();
    });

    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn') && !e.target.closest('.remove-item-btn').disabled) {
            const row = e.target.closest('.item-row');
            const tbody = document.getElementById('itemsTableBody');
            
            if (tbody.children.length > 1) {
                row.remove();
                updateRemoveButtons();
                calculateTotals();
            }
        }
    });

    // Enable/disable remove buttons based on row count
    function updateRemoveButtons() {
        const tbody = document.getElementById('itemsTableBody');
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach(row => {
            const removeBtn = row.querySelector('.remove-item-btn');
            removeBtn.disabled = rows.length === 1;
        });
    }

    // Auto-fill price and description when item is selected
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const description = selectedOption.getAttribute('data-description');
            
            if (price) {
                const row = e.target.closest('.item-row');
                row.querySelector('.unit-price-input').value = price;
                row.querySelector('.quantity-input').value = 1;
                row.querySelector('.description-input').value = description || '';
                updateRowTotal(row);
                calculateTotals();
            }
        }
    });

    // Calculate line total when quantity or unit price changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('unit-price-input')) {
            const row = e.target.closest('.item-row');
            updateRowTotal(row);
            calculateTotals();
        }
    });

    // Update row total
    function updateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const total = quantity * unitPrice;
        row.querySelector('.total-cost-input').value = total.toFixed(2);
    }

    // Calculate totals
    function calculateTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.item-row');
        
        rows.forEach(row => {
            const totalInput = row.querySelector('.total-cost-input');
            if (totalInput) {
                subtotal += parseFloat(totalInput.value) || 0;
            }
        });

        const taxRate = 0;
        const taxAmount = subtotal * (taxRate / 100);
        const discountAmount = 0;
        const paymentsApplied = 0;
        const total = subtotal + taxAmount - discountAmount;
        const balanceDue = total - paymentsApplied;

        document.getElementById('total-amount').textContent = `$${total.toFixed(2)}`;
        document.getElementById('payments-applied').textContent = `$${paymentsApplied.toFixed(2)}`;
        document.getElementById('balance-due').textContent = `$${balanceDue.toFixed(2)}`;
        document.getElementById('current-invoice-total').textContent = `$${balanceDue.toFixed(2)}`;
    }

    // Update buttons state
    function updateButtons() {
        const hasCustomer = selectedCustomer !== null;
        const hasItems = document.querySelectorAll('.item-row').length > 0;
        const canCreate = hasCustomer && hasItems;
        
        document.getElementById('save-draft-btn').disabled = !hasCustomer;
        document.getElementById('create-invoice-btn').disabled = !canCreate;
    }

    // Get form data helper
    function getInvoiceData() {
        const rows = document.querySelectorAll('.item-row');
        const invoiceItems = [];
        
        rows.forEach((row, index) => {
            const itemId = row.querySelector('.item-select').value;
            const quantity = row.querySelector('.quantity-input').value;
            const unitPrice = row.querySelector('.unit-price-input').value;
            const description = row.querySelector('.description-input').value;
            
            if (itemId && quantity && unitPrice) {
                invoiceItems.push({
                    item_id: itemId,
                    description: description,
                    quantity: parseFloat(quantity),
                    unit_price: parseFloat(unitPrice)
                });
            }
        });
        
        return invoiceItems;
    }

    // Create invoice functionality
    document.getElementById('create-invoice-btn').addEventListener('click', function() {
        if (!selectedCustomer) {
            alert('Please select a customer first');
            return;
        }

        const invoiceItems = getInvoiceData();
        if (invoiceItems.length === 0) {
            alert('Please add at least one item to the invoice');
            return;
        }

        showLoading();

        const invoiceData = {
            customer_id: selectedCustomer.id,
            invoice_no: document.getElementById('invoice-number').value,
            date: document.getElementById('invoice-date').value,
            po_number: document.getElementById('po-number').value,
            terms: document.getElementById('payment-terms').value,
            rep: document.getElementById('representative').value,
            ship_date: document.getElementById('ship-date').value,
            via: document.getElementById('shipping-via').value,
            fob: document.getElementById('fob').value,
            billing_address: document.getElementById('billing-address').value,
            shipping_address: document.getElementById('shipping-address').value,
            customer_message: document.getElementById('customer-message').value,
            is_online_payment_enabled: document.getElementById('online-payment').value === '1',
            tax_rate: 0,
            items: invoiceItems
        };

        fetch('{{ route("invoices.web.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(invoiceData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Invoice ${data.invoice.invoice_no} created successfully!`);
                // Reset form
                location.reload();
            } else {
                alert('Error creating invoice: ' + (data.message || 'Unknown error'));
            }
            hideLoading();
        })
        .catch(error => {
            console.error('Error creating invoice:', error);
            alert('Error creating invoice. Please try again.');
            hideLoading();
        });
    });

    // Clear button
    document.getElementById('clear-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all data?')) {
            selectedCustomer = null;
            selectedCustomerDisplay.classList.add('hidden');
            document.getElementById('billing-address').value = '';
            document.getElementById('shipping-address').value = '';
            document.getElementById('po-number').value = '';
            document.getElementById('customer-message').value = '';
            calculateTotals();
            updateButtons();
        }
    });

    function showLoading() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#customer-search') && !e.target.closest('#customer-dropdown')) {
            customerDropdown.classList.add('hidden');
        }
    });

    // Initialize
    calculateTotals();
    updateButtons();
    updateRemoveButtons();
});
</script>
@endsection