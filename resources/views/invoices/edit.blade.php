@extends('layouts.modern')

@section('title', 'Edit Invoice')

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
                        <a href="{{ route('invoices.web.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Invoices
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('invoices.web.show', $invoice) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            {{ $invoice->invoice_no }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Invoice {{ $invoice->invoice_no }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Update invoice details and line items</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('invoices.web.show', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </a>
                    <a href="{{ route('invoices.web.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Invoices
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <form action="{{ route('invoices.web.update', $invoice) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
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

                <!-- Customer Selection -->
                <div class="mb-6">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_id" id="customer_id" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" 
                                    {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} - {{ $customer->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Invoice Details -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Invoice Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" 
                               value="{{ old('date', $invoice->date->format('Y-m-d')) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="due_date" 
                               value="{{ old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">PO Number</label>
                        <input type="text" name="po_number" value="{{ old('po_number', $invoice->po_number) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Terms</label>
                        <input type="text" name="terms" value="{{ old('terms', $invoice->terms) }}" placeholder="e.g., Net 30"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sales Rep</label>
                        <input type="text" name="rep" value="{{ old('rep', $invoice->rep) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                        <select name="template" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="default" {{ old('template', $invoice->template) == 'default' ? 'selected' : '' }}>Default</option>
                            <option value="modern" {{ old('template', $invoice->template) == 'modern' ? 'selected' : '' }}>Modern</option>
                            <option value="classic" {{ old('template', $invoice->template) == 'classic' ? 'selected' : '' }}>Classic</option>
                            <option value="minimal" {{ old('template', $invoice->template) == 'minimal' ? 'selected' : '' }}>Minimal</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ship Date</label>
                        <input type="date" name="ship_date" value="{{ old('ship_date', $invoice->ship_date ? $invoice->ship_date->format('Y-m-d') : '') }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ship Via</label>
                        <input type="text" name="via" value="{{ old('via', $invoice->via) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">FOB</label>
                        <input type="text" name="fob" value="{{ old('fob', $invoice->fob) }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Address</label>
                        <textarea name="billing_address" rows="3" placeholder="Enter billing address"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('billing_address', $invoice->billing_address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address</label>
                        <textarea name="shipping_address" rows="3" placeholder="Enter shipping address"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('shipping_address', $invoice->shipping_address) }}</textarea>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Message</label>
                    <textarea name="customer_message" rows="2" placeholder="Message to appear on invoice"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('customer_message', $invoice->customer_message) }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Internal Notes</label>
                    <textarea name="memo" rows="3" placeholder="Internal memo (not visible to customer)"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('memo', $invoice->memo) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
                        <input type="number" name="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount) }}" 
                               step="0.01" min="0" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Amount</label>
                        <input type="number" name="shipping_amount" value="{{ old('shipping_amount', $invoice->shipping_amount) }}" 
                               step="0.01" min="0" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_online_payment_enabled" name="is_online_payment_enabled" value="1" 
                               {{ old('is_online_payment_enabled', $invoice->is_online_payment_enabled) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_online_payment_enabled" class="ml-2 block text-sm text-gray-900">
                            Enable Online Payment
                        </label>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                    
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-1/4 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="w-1/3 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="w-16 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="w-24 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="w-16 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                    <th class="w-24 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="w-12 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->lineItems as $index => $lineItem)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="line_items[{{ $index }}][item_id]">
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-price="{{ $item->sales_price }}"
                                                            {{ $lineItem->item_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->item_name }} - ${{ number_format($item->sales_price, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="line_items[{{ $index }}][description]" placeholder="Description" required
                                                   value="{{ $lineItem->description }}"
                                                   class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="line_items[{{ $index }}][quantity]" min="0.01" step="0.01" value="{{ $lineItem->quantity }}" required
                                                   class="quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="line_items[{{ $index }}][unit_price]" step="0.01" min="0" value="{{ $lineItem->unit_price }}" required
                                                   class="unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="line_items[{{ $index }}][tax_rate]" step="0.01" min="0" max="100" value="{{ $lineItem->tax_rate }}"
                                                   class="tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="line_items[{{ $index }}][line_total]" step="0.01" min="0" readonly
                                                   value="{{ $lineItem->amount + $lineItem->tax_amount }}"
                                                   class="line-total block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm text-center">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-900 remove-item">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
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
                                    <span id="subtotal" class="font-medium">${{ number_format($invoice->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span id="taxAmount" class="font-medium">${{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Shipping:</span>
                                    <span id="shippingAmount" class="font-medium">${{ number_format($invoice->shipping_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span id="discountAmount" class="font-medium">-${{ number_format($invoice->discount_amount, 2) }}</span>
                                </div>
                                <hr class="border-gray-300">
                                <div class="flex justify-between text-base font-semibold">
                                    <span>Total:</span>
                                    <span id="total">${{ number_format($invoice->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('invoices.web.show', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $invoice->lineItems->count() }};

    // Add item row
    document.getElementById('addItem').addEventListener('click', function() {
        const newRow = `
            <tr>
                <td class="px-3 py-2">
                    <select class="form-select item-select block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" name="line_items[${itemIndex}][item_id]">
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->sales_price }}">
                                {{ $item->item_name }} - ${{ number_format($item->sales_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="line_items[${itemIndex}][description]" placeholder="Description" required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="line_items[${itemIndex}][quantity]" min="0.01" step="0.01" value="1" required
                           class="quantity block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="line_items[${itemIndex}][unit_price]" step="0.01" min="0" required
                           class="unit-price block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="line_items[${itemIndex}][tax_rate]" step="0.01" min="0" max="100" value="0"
                           class="tax-rate block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="line_items[${itemIndex}][line_total]" step="0.01" min="0" readonly
                           class="line-total block w-full px-1 py-1.5 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm text-center">
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

    // Item selection change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-select')) {
            const price = e.target.options[e.target.selectedIndex].dataset.price;
            const row = e.target.closest('tr');
            row.querySelector('.unit-price').value = price;
            calculateLineTotal(row);
        }
    });

    // Quantity, unit price, or tax rate change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price') || e.target.classList.contains('tax-rate')) {
            calculateLineTotal(e.target.closest('tr'));
        }
        
        // Handle discount and shipping amount changes
        if (e.target.name === 'discount_amount' || e.target.name === 'shipping_amount') {
            calculateTotal();
        }
    });

    function calculateLineTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax-rate').value) || 0;
        
        const amount = quantity * unitPrice;
        const taxAmount = amount * (taxRate / 100);
        const lineTotal = amount + taxAmount;
        
        row.querySelector('.line-total').value = lineTotal.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        let taxAmount = 0;
        
        // Calculate from line totals
        document.querySelectorAll('.line-total').forEach(function(element) {
            const lineTotal = parseFloat(element.value) || 0;
            subtotal += lineTotal;
        });
        
        // Calculate tax from line items
        document.querySelectorAll('#itemsTableBody tr').forEach(function(row) {
            const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
            const taxRate = parseFloat(row.querySelector('.tax-rate')?.value) || 0;
            
            const amount = quantity * unitPrice;
            const lineTaxAmount = amount * (taxRate / 100);
            taxAmount += lineTaxAmount;
        });
        
        // Get discount and shipping amounts
        const discountAmount = parseFloat(document.querySelector('input[name="discount_amount"]')?.value) || 0;
        const shippingAmount = parseFloat(document.querySelector('input[name="shipping_amount"]')?.value) || 0;
        
        // Calculate final total
        const finalTotal = subtotal + taxAmount + shippingAmount - discountAmount;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = '$' + taxAmount.toFixed(2);
        document.getElementById('shippingAmount').textContent = '$' + shippingAmount.toFixed(2);
        document.getElementById('discountAmount').textContent = '-$' + discountAmount.toFixed(2);
        document.getElementById('total').textContent = '$' + finalTotal.toFixed(2);
    }

    // Initial calculation
    calculateTotal();
});
</script>
@endsection