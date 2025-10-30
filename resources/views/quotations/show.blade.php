@extends('layouts.modern')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Quotation #{{ $quotation->quotation_number }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Created on {{ $quotation->created_at->format('M d, Y \a\t g:i A') }}
                </p>
            </div>
            <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                <a href="{{ route('quotations.print', $quotation) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print PDF
                </a>
                <a href="{{ route('quotations.edit', $quotation) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('quotations.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- Status Badge and Actions -->
        <div class="mt-4 flex items-center justify-between">
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                @if($quotation->status === 'draft') bg-gray-100 text-gray-800
                @elseif($quotation->status === 'sent') bg-blue-100 text-blue-800
                @elseif($quotation->status === 'accepted') bg-green-100 text-green-800
                @elseif($quotation->status === 'rejected') bg-red-100 text-red-800
                @elseif($quotation->status === 'expired') bg-yellow-100 text-yellow-800
                @endif">
                {{ ucfirst($quotation->status) }}
            </span>

            @if($quotation->status === 'draft')
                <form action="{{ route('quotations.send', $quotation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Send Quotation
                    </button>
                </form>
            @endif
        </div>

        <!-- Quotation Details -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Customer Information -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer->email ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer->phone ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Quotation Information -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quotation Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Quotation Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->quotation_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->payment_terms ?? 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Shipping Method</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->shipping_method ?? 'Not specified' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Quotation Summary -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quotation Summary</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
                            <dd class="text-sm text-gray-900">${{ number_format($quotation->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Tax</dt>
                            <dd class="text-sm text-gray-900">${{ number_format($quotation->tax_amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Discount</dt>
                            <dd class="text-sm text-gray-900">-${{ number_format($quotation->discount_amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Shipping</dt>
                            <dd class="text-sm text-gray-900">${{ number_format($quotation->shipping_amount, 2) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-medium text-gray-900">${{ number_format($quotation->total_amount, 2) }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Quotation Items -->
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quotation Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tax Rate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Discount Rate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($quotation->lineItems as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->item->item_name ?? 'N/A' }}
                                        @if($item->item)
                                            <br><span class="text-gray-500 text-xs">({{ $item->item->item_number }})</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $item->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->tax_rate, 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->discount_rate, 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($item->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        @if($quotation->shipping_address || $quotation->billing_address)
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                @if($quotation->shipping_address)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h3>
                            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $quotation->shipping_address }}</div>
                        </div>
                    </div>
                @endif

                @if($quotation->billing_address)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Address</h3>
                            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $quotation->billing_address }}</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Notes and Terms -->
        @if($quotation->notes || $quotation->terms_conditions)
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                @if($quotation->notes)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $quotation->notes }}</div>
                        </div>
                    </div>
                @endif

                @if($quotation->terms_conditions)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Terms & Conditions</h3>
                            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $quotation->terms_conditions }}</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
