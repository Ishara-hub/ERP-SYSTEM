@extends('layouts.modern')

@section('title', 'Invoice Details')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $invoice->invoice_no }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoice {{ $invoice->invoice_no }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Invoice details and information</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('invoices.web.edit', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('invoices.web.print', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" target="_blank">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Invoice Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Information -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Invoice Information</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_no }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->date->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'sent' => 'bg-blue-100 text-blue-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            'partial' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        $statusColor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </dd>
                            </div>
                            @if($invoice->po_number)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">PO Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->po_number }}</dd>
                                </div>
                            @endif
                            @if($invoice->terms)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Terms</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->terms }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Customer Information</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $invoice->customer->name }}</h4>
                                @if($invoice->customer->email)
                                    <p class="text-sm text-gray-600">{{ $invoice->customer->email }}</p>
                                @endif
                                @if($invoice->customer->phone)
                                    <p class="text-sm text-gray-600">{{ $invoice->customer->phone }}</p>
                                @endif
                            </div>
                            <div>
                                @if($invoice->customer->address)
                                    <p class="text-sm text-gray-600">{{ $invoice->customer->address }}</p>
                                @endif
                                @if($invoice->customer->company)
                                    <p class="text-sm text-gray-600">{{ $invoice->customer->company }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                    </div>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->lineItems as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->item ? $item->item->item_name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->tax_rate }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->amount + $item->tax_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Totals -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Invoice Totals</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-600">Subtotal:</dt>
                                <dd class="font-medium">${{ number_format($invoice->subtotal, 2) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-600">Tax:</dt>
                                <dd class="font-medium">${{ number_format($invoice->tax_amount, 2) }}</dd>
                            </div>
                            @if($invoice->shipping_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-600">Shipping:</dt>
                                    <dd class="font-medium">${{ number_format($invoice->shipping_amount, 2) }}</dd>
                                </div>
                            @endif
                            @if($invoice->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-600">Discount:</dt>
                                    <dd class="font-medium">-${{ number_format($invoice->discount_amount, 2) }}</dd>
                                </div>
                            @endif
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-base font-semibold">
                                    <dt>Total:</dt>
                                    <dd>${{ number_format($invoice->total_amount, 2) }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($invoice->rep || $invoice->ship_date || $invoice->via || $invoice->fob)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                @if($invoice->rep)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Sales Rep</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $invoice->rep }}</dd>
                                    </div>
                                @endif
                                @if($invoice->ship_date)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Ship Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $invoice->ship_date->format('M d, Y') }}</dd>
                                    </div>
                                @endif
                                @if($invoice->via)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Ship Via</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $invoice->via }}</dd>
                                    </div>
                                @endif
                                @if($invoice->fob)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">FOB</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $invoice->fob }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                <!-- Messages -->
                @if($invoice->customer_message || $invoice->memo)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Messages</h3>
                        </div>
                        <div class="px-6 py-4">
                            @if($invoice->customer_message)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Customer Message</h4>
                                    <p class="text-sm text-gray-600">{{ $invoice->customer_message }}</p>
                                </div>
                            @endif
                            @if($invoice->memo)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Internal Notes</h4>
                                    <p class="text-sm text-gray-600">{{ $invoice->memo }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <a href="{{ route('invoices.web.edit', $invoice) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Invoice
                        </a>
                        <a href="{{ route('invoices.web.print', $invoice) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" target="_blank">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Invoice
                        </a>
                        @if($invoice->status !== 'paid')
                            <form method="POST" action="{{ route('invoices.web.mark-paid', $invoice) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        onclick="return confirm('Are you sure you want to mark this invoice as paid?')">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Mark as Paid
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection