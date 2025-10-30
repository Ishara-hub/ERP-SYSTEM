@extends('layouts.modern')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Payment {{ $payment->payment_number }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Payment details and information
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <a href="{{ route('payments.web.edit', $payment) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('payments.web.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Payments
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Payment Information -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Payment Information</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-4">
                        <!-- First Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Number</label>
                                <div class="text-sm font-medium text-gray-900">{{ $payment->payment_number }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Date</label>
                                <div class="text-sm text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                                <div class="text-sm font-bold text-gray-900">${{ number_format($payment->amount, 2) }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $payment->status_color }}-100 text-{{ $payment->status_color }}-800">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Method</label>
                                <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                            </div>
                            @if($payment->reference)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Reference</label>
                                <div class="text-sm text-gray-900">{{ $payment->reference }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                            @if($payment->bank_name)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Bank Name</label>
                                <div class="text-sm text-gray-900">{{ $payment->bank_name }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                            @if($payment->check_number)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Check Number</label>
                                <div class="text-sm text-gray-900">{{ $payment->check_number }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Purchase Order Information -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">PO Number</label>
                                <div class="text-sm">
                                    <a href="{{ route('purchase-orders.web.show', $payment->purchaseOrder) }}" class="text-blue-600 hover:text-blue-500 font-medium">
                                        {{ $payment->purchaseOrder->po_number }}
                                    </a>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Supplier</label>
                                <div class="text-sm text-gray-900">{{ $payment->purchaseOrder->supplier->name }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Total Amount</label>
                                <div class="text-sm text-gray-900">${{ number_format($payment->purchaseOrder->total_amount, 2) }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Order Date</label>
                                <div class="text-sm text-gray-900">{{ $payment->purchaseOrder->order_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Status</label>
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $payment->purchaseOrder->payment_status_color }}-100 text-{{ $payment->purchaseOrder->payment_status_color }}-800">
                                        {{ ucfirst($payment->purchaseOrder->payment_status) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Total Payments</label>
                                <div class="text-sm text-gray-900">${{ number_format($payment->purchaseOrder->total_payments, 2) }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Balance Due</label>
                                <div class="text-sm font-medium text-red-600">${{ number_format($payment->purchaseOrder->balance_due, 2) }}</div>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($payment->transaction_id || $payment->fee_amount > 0 || $payment->received_by || $payment->notes)
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if($payment->transaction_id)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Transaction ID</label>
                                <div class="text-sm text-gray-900">{{ $payment->transaction_id }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                            @if($payment->fee_amount > 0)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fee Amount</label>
                                <div class="text-sm text-gray-900">${{ number_format($payment->fee_amount, 2) }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                            @if($payment->received_by)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Received By</label>
                                <div class="text-sm text-gray-900">{{ $payment->received_by }}</div>
                            </div>
                            @else
                            <div></div>
                            @endif
                            <div></div>
                        </div>
                        
                        @if($payment->notes)
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                            <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ $payment->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-4 space-y-3">
                        <a href="{{ route('payments.web.edit', $payment) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Payment
                        </a>
                        <a href="{{ route('purchase-orders.web.show', $payment->purchaseOrder) }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Purchase Order
                        </a>
                        <form method="POST" action="{{ route('payments.web.destroy', $payment) }}" onsubmit="return confirm('Are you sure you want to delete this payment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Payment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Payment Summary</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Payment Amount:</span>
                            <span class="text-sm font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</span>
                        </div>
                        @if($payment->fee_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Fee Amount:</span>
                            <span class="text-sm font-medium text-gray-900">-${{ number_format($payment->fee_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <span class="text-sm font-medium text-gray-900">Net Amount:</span>
                            <span class="text-sm font-bold text-gray-900">${{ number_format($payment->net_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


