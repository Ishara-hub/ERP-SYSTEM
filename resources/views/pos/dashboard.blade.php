@extends('layouts.modern')

@section('title', 'POS Dashboard')

@section('content')
<div class="max-w-7xl mx-auto bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Modern Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-1.5 py-1.5">
            <div class="flex items-center justify-between">
                <!-- Left Side - Title and Breadcrumb -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-gray-900">Create Invoices</h1>
                            <p class="text-xs text-gray-500">Point of Sale - Create Professional Invoices</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Current Invoice Total -->
                <div class="text-right">
                    <div class="text-sm text-gray-500">Current Invoice</div>
                    <div class="text-sm font-semibold text-gray-900" id="current-invoice-total">$0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Selection Bar -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between">
                <!-- Customer Selection -->
                <div class="flex items-center space-x-4 flex-1">
                    <div class="flex items-center space-x-2 flex-1 max-w-xs">
                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">CUSTOMER:</label>
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="customer-search" 
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

                    <!-- Account Selection -->
                    <div class="flex items-center space-x-2 flex-1 max-w-xs">
                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">ACCOUNT:</label>
                        <select id="account-select" class="block w-full px-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select Account</option>
                        </select>
                    </div>

                    <!-- Template Selection -->
                    <div class="flex items-center space-x-2 flex-1 max-w-sm">
                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">TEMPLATE:</label>
                        <select id="template" class="block w-full px-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="default" selected>Intuit Product Invoice</option>
                            <option value="service">Service Invoice</option>
                            <option value="product">Product Invoice</option>
                            <option value="custom">Custom Template</option>
                        </select>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
    

    <!-- Main Content -->
    <div class="flex max-w-7xl mx-auto">
        <!-- Left Panel - Invoice Form -->
        <div class="flex-1 max-w-7xl mx-auto">
            <div class="mx-auto px-4 py-2 space-y-1">
                <!-- Invoice Header Section -->
                <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-4">
                        <div class="grid grid-cols-3 gap-1.5">
                            <!-- First Column - Invoice Title -->
                            <div class="flex items-start pt-4">
                                <h1 class="text-xl font-bold text-gray-900">Invoice</h1>
                            </div>
                            
                            <!-- Second Column - Date and Invoice Number -->
                            <div class="space-y-1.5">
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">DATE</label>
                                    <input type="date" id="invoice-date" value="{{ date('Y-m-d') }}"
                                        class="w-full px-1.5 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">INVOICE #</label>
                                    <input type="text" id="invoice-number" value="{{ $nextInvoiceNo }}"
                                        class="w-full px-1.5 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Third Column - BILL TO and SHIP TO -->
                            <div class="space-y-1.5">
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">BILL TO</label>
                                    <textarea id="billing-address" rows="3" 
                                              class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none" 
                                              placeholder="Billing address"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-normal text-gray-700">SHIP TO</label>
                                    <textarea id="shipping-address" rows="3" 
                                              class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none" 
                                              placeholder="Shipping address"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="grid grid-cols-6 gap-1.5 mt-1.5">
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">P.O. NUMBER</label>
                                <input type="text" id="po-number" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">TERMS</label>
                                <select id="payment-terms" class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
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
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">SHIP</label>
                                <input type="date" id="ship-date"
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">VIA</label>
                                <input type="text" id="shipping-via" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 mb-0.5">F.O.B.</label>
                                <input type="text" id="fob" placeholder=""
                                       class="w-full px-1.5 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Section -->

                <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-md font-semibold text-gray-900">Items</h3>
                            <button type="button" 
                                    id="addItem"
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </div>
                    <div class="p-1">
                        <!-- Items Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-1 text-left text-sm font-bold text-gray-600 uppercase">QUANTITY</th>
                                        <th class="px-2 py-1 text-left text-sm font-bold text-gray-600 uppercase">ITEM CODE</th>
                                        <th class="px-2 py-1 text-left text-sm font-bold text-gray-600 uppercase">DESCRIPTION</th>
                                        <th class="px-2 py-1 text-left text-sm font-bold text-gray-600 uppercase">PRICE EACH</th>
                                        <th class="px-2 py-1 text-left text-sm font-bold text-gray-600 uppercase">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody id="items-table-body" class="bg-white divide-y divide-gray-100">
                                    <!-- Items will be populated here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Item Search Bar -->
                        <div class="mt-1 p-1 border-t border-gray-200">
                            <input type="text" 
                                   id="item-search" 
                                   class="block w-full px-2 py-1 border border-gray-300 rounded leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                   placeholder="Search items...">
                            
                            <!-- Item Search Results Dropdown -->
                            <div id="item-dropdown" class="hidden absolute z-10 mt-1 w-full bg-white shadow-sm max-h-48 rounded py-1 ring-1 ring-black ring-opacity-5 overflow-auto">
                                <!-- Item options will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section -->
                <div class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-2">
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Left Side -->
                            <div class="space-y-2 grid grid-cols-2 gap-2">
                                <div class="col-span-1">
                                    <label class="block text-xs font-semibold text-gray-700 whitespace-nowrap">ONLINE PAY</label>
                                    <select id="online-payment" class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="0" selected>Off</option>
                                        <option value="1">On</option>
                                    </select>
                                </div>
                                
                                <div class="col-span-1">
                                    <label class="block text-xs font-semibold text-gray-700 whitespace-nowrap">CUSTOMER MESSAGE</label>
                                    <textarea id="customer-message" rows="3" 
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                        placeholder="Message..."></textarea>
                                </div>
                            </div>
                            
                            <!-- Right Side -->
                            <div class="space-y-2">
                                <div class="text-right space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-md font-semibold text-gray-700">TOTAL</span>
                                        <span id="total-amount" class="text-md font-bold text-gray-900">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-semibold text-gray-700">PAYMENTS APPLIED</span>
                                        <span id="payments-applied" class="text-sm font-semibold text-gray-900">0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-gray-300">
                                        <span class="text-md font-bold text-gray-900">BALANCE DUE</span>
                                        <span id="balance-due" class="text-md font-bold text-yellow-600">0.00</span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex justify-end space-x-4 mt-4 pt-1 border-t border-gray-200">
                                    <button id="save-draft-btn" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            disabled>
                                        <svg class="w-3 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Save & Close
                                    </button>
                                    <button id="create-invoice-btn" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            disabled>
                                        <svg class="w-3 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Save & New
                                    </button>
                                    <button id="clear-btn" 
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

        <!-- Right Panel - Transaction History (Collapsible) -->
        <div id="transaction-history-panel" class="w-64 bg-white border-l border-gray-200 transition-all duration-300 overflow-hidden" style="max-height: calc(100vh - 100px);">
            <!-- Toggle Button -->
            <div class="flex items-center justify-between p-2 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Transaction History</h3>
                <button id="toggle-history" class="p-1 hover:bg-gray-200 rounded transition-colors">
                    <svg id="toggle-icon" class="w-4 h-4 text-gray-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button id="tab-name" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border-b-2 border-blue-600">Name</button>
                <button id="tab-transaction" class="flex-1 px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Transaction</button>
            </div>

            <!-- Content -->
            <div class="overflow-y-auto" style="max-height: calc(100vh - 200px);">
                <!-- Tab: Name (Customer Invoice History) -->
                <div id="tab-content-name" class="p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">CUSTOMER INVOICES</h4>
                    <div id="customer-invoices-content" class="space-y-2">
                        <div class="text-center text-gray-500 py-8">
                            <p class="text-sm">No invoices to display</p>
                        </div>
                    </div>
                </div>

                <!-- Tab: Transaction (Item History) -->
                <div id="tab-content-transaction" class="hidden p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">ITEM TRANSACTION HISTORY</h4>
                    <div id="item-transaction-content" class="space-y-2">
                        <div class="text-center text-gray-500 py-8">
                            <p class="text-xs">Select items to view transaction history</p>
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
    let selectedItem = null;
    let cart = [];
    let itemIndex = 0;

    // Load Accounts Receivable accounts
    const accountsReceivable = @json($accountsReceivable);
    const accountSelect = document.getElementById('account-select');
    
    // Populate account dropdown
    accountsReceivable.forEach(account => {
        const option = document.createElement('option');
        option.value = account.id;
        option.textContent = `${account.account_name}${account.account_code ? ' (' + account.account_code + ')' : ''}`;
        accountSelect.appendChild(option);
    });
    
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
            (customer.phone && customer.phone.toLowerCase().includes(query)) ||
            (customer.company && customer.company.toLowerCase().includes(query))
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
            
            loadCustomerInvoices();
            updateButtons();
        }
    });

    clearCustomerBtn.addEventListener('click', function() {
        selectedCustomer = null;
        selectedCustomerDisplay.classList.add('hidden');
        document.getElementById('customer-invoices').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>Select a customer to view invoice history</p>
            </div>
        `;
        updateButtons();
    });

    // Item search functionality
    const itemSearch = document.getElementById('item-search');
    const itemDropdown = document.getElementById('item-dropdown');

    itemSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (query.length < 2) {
            itemDropdown.classList.add('hidden');
            return;
        }

        const filteredItems = items.filter(item => 
            item.item_name.toLowerCase().includes(query) ||
            (item.item_number && item.item_number.toLowerCase().includes(query)) ||
            (item.sales_description && item.sales_description.toLowerCase().includes(query))
        );

        if (filteredItems.length > 0) {
            itemDropdown.innerHTML = filteredItems.map(item => `
                <div class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-item-id="${item.id}">
                    <div class="flex-shrink-0">
                        <div class="w-4 h-4 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                        <div class="text-sm text-gray-500">${item.item_number || ''} - ${parseFloat(item.sales_price).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');
            itemDropdown.classList.remove('hidden');
        } else {
            itemDropdown.classList.add('hidden');
        }
    });

    itemDropdown.addEventListener('click', function(e) {
        const itemElement = e.target.closest('[data-item-id]');
        if (itemElement) {
            const itemId = parseInt(itemElement.dataset.itemId);
            selectedItem = items.find(i => i.id === itemId);
            
            addItemToCart(selectedItem);
            itemSearch.value = '';
            itemDropdown.classList.add('hidden');
        }
    });

    // Add item to cart
    function addItemToCart(item) {
        const existingItemIndex = cart.findIndex(cartItem => cartItem.id === item.id);
        
        if (existingItemIndex > -1) {
            cart[existingItemIndex].quantity += 1;
        } else {
            cart.push({
                id: item.id,
                name: item.item_name,
                code: item.item_number || '',
                description: item.sales_description || item.item_name,
                quantity: 1,
                unit_price: parseFloat(item.sales_price),
                amount: parseFloat(item.sales_price)
            });
        }
        
        updateItemsTable();
        calculateTotals();
        updateButtons();
        loadItemTransactionHistory();
    }

    // Update items table
    function updateItemsTable() {
        const tbody = document.getElementById('items-table-body');
        
        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-2 py-4 text-center text-gray-500 text-xs">
                        No items added yet.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = cart.map((item, index) => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2 py-1 whitespace-nowrap">
                        <input type="number" min="0.01" step="0.01" value="${item.quantity}" 
                               onchange="updateItemQuantity(${index}, this.value)"
                               class="w-16 px-1 py-0.5 border border-gray-300 rounded text-sm text-center focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </td>
                    <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-900">${item.code}</td>
                    <td class="px-2 py-1 whitespace-nowrap">
                        <input type="text" value="${item.description}" 
                               onchange="updateItemDescription(${index}, this.value)"
                               class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </td>
                    <td class="px-2 py-1 whitespace-nowrap">
                        <input type="number" step="0.01" min="0" value="${item.unit_price}" 
                               onchange="updateItemPrice(${index}, this.value)"
                               class="w-20 px-1 py-0.5 border border-gray-300 rounded text-sm text-center focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </td>
                    <td class="px-2 py-1 whitespace-nowrap text-sm font-semibold text-gray-900">${item.amount.toFixed(2)}</td>
                </tr>
            `).join('');
        }
    }

    // Update item quantity
    window.updateItemQuantity = function(index, quantity) {
        cart[index].quantity = parseFloat(quantity);
        cart[index].amount = cart[index].quantity * cart[index].unit_price;
        updateItemsTable();
        calculateTotals();
        loadItemTransactionHistory();
    };

    // Update item description
    window.updateItemDescription = function(index, description) {
        cart[index].description = description;
    };

    // Update item price
    window.updateItemPrice = function(index, price) {
        cart[index].unit_price = parseFloat(price);
        cart[index].amount = cart[index].quantity * cart[index].unit_price;
        updateItemsTable();
        calculateTotals();
    };

    // Remove item
    window.removeItem = function(index) {
        cart.splice(index, 1);
        updateItemsTable();
        calculateTotals();
        updateButtons();
        loadItemTransactionHistory();
    };

    // Calculate totals
    function calculateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + item.amount, 0);
        const taxRate = 0; // Tax rate can be added later if needed
        const taxAmount = subtotal * (taxRate / 100);
        const discountAmount = 0; // Can be implemented later
        const paymentsApplied = 0; // Can be implemented later
        const total = subtotal + taxAmount - discountAmount;
        const balanceDue = total - paymentsApplied;

        document.getElementById('total-amount').textContent = `Rs.${total.toFixed(2)}`;
        document.getElementById('payments-applied').textContent = `Rs.${paymentsApplied.toFixed(2)}`;
        document.getElementById('balance-due').textContent = `Rs.${balanceDue.toFixed(2)}`;
        document.getElementById('current-invoice-total').textContent = `Rs.${balanceDue.toFixed(2)}`;
    }

    // Load customer invoices
    function loadCustomerInvoices() {
        if (!selectedCustomer) return;

        showLoading();
        
        fetch('/pos/customer-invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                customer_id: selectedCustomer.id
            })
        })
        .then(response => response.json())
        .then(data => {
            // Update customer invoices
            const container = document.getElementById('customer-invoices-content');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        <p class="text-xs">No invoices found for this customer</p>
                    </div>
                `;
            } else {
                container.innerHTML = data.map(invoice => `
                    <div class="border border-gray-200 rounded p-2 hover:bg-gray-50 transition-colors cursor-pointer" 
                         onclick="loadInvoiceItems(${invoice.id})">
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <div class="font-semibold text-xs text-gray-900">${invoice.invoice_no}</div>
                                <div class="text-xs text-gray-600">${invoice.date}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-xs text-gray-900">Rs.${parseFloat(invoice.total_amount).toFixed(2)}</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${invoice.items_count} item${invoice.items_count !== 1 ? 's' : ''} • 
                            Balance: Rs.${parseFloat(invoice.balance_due).toFixed(2)}
                        </div>
                    </div>
                `).join('');
            }
            hideLoading();
        })
        .catch(error => {
            console.error('Error loading customer invoices:', error);
            hideLoading();
        });
    }

    // Load invoice items for price history
    window.loadInvoiceItems = function(invoiceId) {
        // This will be implemented to show item prices from the selected invoice
        console.log('Loading items for invoice:', invoiceId);
    };

    // Load item transaction history
    function loadItemTransactionHistory() {
        const container = document.getElementById('item-transaction-content');
        
        if (!selectedCustomer || cart.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <p class="text-xs">Add items to cart to view transaction history</p>
                </div>
            `;
            return;
        }

        // Group items by ID and show transaction history for each
        const uniqueItems = {};
        cart.forEach(item => {
            if (!uniqueItems[item.id]) {
                uniqueItems[item.id] = {
                    ...item,
                    totalQuantity: 0
                };
            }
            uniqueItems[item.id].totalQuantity += item.quantity;
        });

        container.innerHTML = Object.values(uniqueItems).map(item => `
            <div class="border border-gray-200 rounded p-2 hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start mb-1">
                    <div class="flex-1">
                        <div class="font-semibold text-xs text-gray-900">${item.name}</div>
                        <div class="text-xs text-gray-600">Code: ${item.code || 'N/A'}</div>
                        <div class="text-xs text-gray-600 mt-1">
                            <span class="font-semibold">Qty:</span> ${item.totalQuantity} × 
                            <span class="font-semibold">Price:</span> Rs.${item.unit_price.toFixed(2)}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-xs text-gray-900">Rs.${(item.totalQuantity * item.unit_price).toFixed(2)}</div>
                    </div>
                </div>
                <div class="text-xs text-gray-500 italic">
                    Last sold: N/A
                </div>
            </div>
        `).join('');
    }

    // Toggle transaction history panel
    let isHistoryCollapsed = false;
    document.getElementById('toggle-history').addEventListener('click', function() {
        const panel = document.getElementById('transaction-history-panel');
        const icon = document.getElementById('toggle-icon');
        const header = panel.querySelector('.flex.items-center.justify-between'); // Header with button
        const tabs = panel.querySelector('.flex.border-b'); // Tabs
        const content = panel.querySelector('.overflow-y-auto'); // Content
        
        if (isHistoryCollapsed) {
            // Show panel
            panel.style.width = '16rem'; // 64 (w-64)
            icon.style.transform = 'rotate(0deg)';
            if (header) header.style.display = '';
            if (tabs) tabs.style.display = '';
            if (content) content.style.display = '';
            isHistoryCollapsed = false;
        } else {
            // Hide panel
            panel.style.width = '0';
            icon.style.transform = 'rotate(180deg)';
            // Keep header visible but hide tabs and content
            if (tabs) tabs.style.display = 'none';
            if (content) content.style.display = 'none';
            isHistoryCollapsed = true;
        }
    });

    // Tab switching
    document.getElementById('tab-name').addEventListener('click', function() {
        document.getElementById('tab-name').classList.add('border-b-2', 'border-blue-600', 'text-gray-700');
        document.getElementById('tab-name').classList.remove('text-gray-500');
        document.getElementById('tab-transaction').classList.remove('border-b-2', 'border-blue-600', 'text-gray-700');
        document.getElementById('tab-transaction').classList.add('text-gray-500');
        
        // Show Name tab content, hide Transaction tab content
        document.getElementById('tab-content-name').classList.remove('hidden');
        document.getElementById('tab-content-transaction').classList.add('hidden');
    });

    document.getElementById('tab-transaction').addEventListener('click', function() {
        document.getElementById('tab-transaction').classList.add('border-b-2', 'border-blue-600', 'text-gray-700');
        document.getElementById('tab-transaction').classList.remove('text-gray-500');
        document.getElementById('tab-name').classList.remove('border-b-2', 'border-blue-600', 'text-gray-700');
        document.getElementById('tab-name').classList.add('text-gray-500');
        
        // Show Transaction tab content, hide Name tab content
        document.getElementById('tab-content-transaction').classList.remove('hidden');
        document.getElementById('tab-content-name').classList.add('hidden');
        
        // Load item transaction history
        loadItemTransactionHistory();
    });

    // Clear button
    document.getElementById('clear-btn').addEventListener('click', function() {
        cart = [];
        selectedCustomer = null;
        selectedCustomerDisplay.classList.add('hidden');
        document.getElementById('billing-address').value = '';
        document.getElementById('shipping-address').value = '';
        document.getElementById('po-number').value = '';
        document.getElementById('customer-message').value = '';
        if (document.getElementById('memo')) {
            document.getElementById('memo').value = '';
        }
        updateItemsTable();
        calculateTotals();
        updateButtons();
    });

    // Update buttons state
    function updateButtons() {
        const hasCustomer = selectedCustomer !== null;
        const hasItems = cart.length > 0;
        const canCreate = hasCustomer && hasItems;
        
        document.getElementById('save-draft-btn').disabled = !hasCustomer;
        document.getElementById('create-invoice-btn').disabled = !canCreate;
    }

    // Create invoice functionality
    document.getElementById('create-invoice-btn').addEventListener('click', function() {
        if (!selectedCustomer || cart.length === 0) return;

        showLoading();

        // Get invoice number - check if it's an input or text element
        const invoiceNumberEl = document.getElementById('invoice-number');
        const invoiceNo = invoiceNumberEl ? (invoiceNumberEl.value || invoiceNumberEl.textContent || '').trim() : '';
        
        const invoiceData = {
            customer_id: selectedCustomer.id,
            invoice_no: invoiceNo || null, // Let server generate if empty
            date: document.getElementById('invoice-date') ? document.getElementById('invoice-date').value : new Date().toISOString().split('T')[0],
            due_date: document.getElementById('due-date') ? document.getElementById('due-date').value : null,
            po_number: document.getElementById('po-number') ? document.getElementById('po-number').value : '',
            terms: document.getElementById('payment-terms') ? document.getElementById('payment-terms').value : '',
            rep: document.getElementById('representative') ? document.getElementById('representative').value : '',
            template: document.getElementById('template') ? document.getElementById('template').value : 'default',
            ship_date: document.getElementById('ship-date') ? document.getElementById('ship-date').value : null,
            via: document.getElementById('shipping-via') ? document.getElementById('shipping-via').value : '',
            fob: document.getElementById('fob') ? document.getElementById('fob').value : '',
            billing_address: document.getElementById('billing-address') ? document.getElementById('billing-address').value : '',
            shipping_address: document.getElementById('shipping-address') ? document.getElementById('shipping-address').value : '',
            customer_message: document.getElementById('customer-message') ? document.getElementById('customer-message').value : '',
            memo: document.getElementById('memo') ? document.getElementById('memo').value : '',
            is_online_payment_enabled: document.getElementById('online-payment') ? document.getElementById('online-payment').value === '1' : false,
            tax_rate: 0,
            items: cart.map(item => ({
                item_id: item.id,
                description: item.description || item.name || '',
                quantity: parseFloat(item.quantity) || 0,
                unit_price: parseFloat(item.unit_price) || 0
            }))
        };
        
        // Validate items before sending
        if (invoiceData.items.length === 0) {
            alert('Please add at least one item to the invoice');
            hideLoading();
            return;
        }
        
        // Validate that all items have required fields
        const invalidItems = invoiceData.items.filter(item => !item.item_id || !item.quantity || !item.unit_price);
        if (invalidItems.length > 0) {
            alert('Some items are missing required information. Please check all items.');
            hideLoading();
            return;
        }
        
        console.log('Sending invoice data:', invoiceData);

        fetch('/pos/create-invoice', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(invoiceData)
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, it's likely an HTML error page
                return response.text().then(text => {
                    throw new Error('Server returned HTML instead of JSON. This usually means there was a validation error or server error.');
                });
            }
        })
        .then(data => {
            if (data.success) {
                alert(`Invoice ${data.invoice.invoice_no} created successfully!`);
                // Reset form
                cart = [];
                updateItemsTable();
                calculateTotals();
                updateButtons();
                loadCustomerInvoices();
                
                // Optionally redirect to invoice page
                if (data.redirect && confirm('Invoice created successfully! Would you like to view the invoice?')) {
                    window.location.href = data.redirect;
                }
            } else {
                let errorMsg = data.message || 'Unknown error';
                if (data.errors) {
                    const errorList = Object.entries(data.errors)
                        .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                        .join('\n');
                    errorMsg += '\n\nValidation Errors:\n' + errorList;
                }
                console.error('Invoice creation error:', data);
                alert('Error creating invoice:\n' + errorMsg);
            }
            hideLoading();
        })
        .catch(error => {
            console.error('Error creating invoice:', error);
            alert('Error creating invoice: ' + error.message);
            hideLoading();
        });
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
        if (!e.target.closest('#item-search') && !e.target.closest('#item-dropdown')) {
            itemDropdown.classList.add('hidden');
        }
    });

    // Initialize
    updateItemsTable();
    calculateTotals();
    updateButtons();
});
</script>
@endsection 