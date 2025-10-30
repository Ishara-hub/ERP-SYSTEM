@extends('layouts.modern')

@section('title', 'Payment Details')
@section('breadcrumb', 'Payment Details')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payment Details</h1>
                <p class="mt-1 text-sm text-gray-600">Payment #{{ $payment->payment_number ?? $payment->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('bills.pay-bill.voucher', $payment) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Voucher
                </a>
                <a href="{{ route('bills.pay-bill.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Payment Information</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Payment Number</label>
                    <p class="text-sm text-gray-900">{{ $payment->payment_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Payment Date</label>
                    <p class="text-sm text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Supplier</label>
                    <p class="text-sm text-gray-900">{{ $payment->supplier ? $payment->supplier->name : 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Payment Method</label>
                    <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Bank Account</label>
                    <p class="text-sm text-gray-900">{{ $payment->bankAccount ? $payment->bankAccount->account_name : 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Amount</label>
                    <p class="text-sm font-semibold text-gray-900 text-lg">${{ number_format($payment->amount, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Reference</label>
                    <p class="text-sm text-gray-900">{{ $payment->reference ?? 'N/A' }}</p>
                </div>
                @if($payment->notes)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                    <p class="text-sm text-gray-900">{{ $payment->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment History -->
    @if($payment->bill)
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Bill Information</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Bill Number</label>
                    <p class="text-sm text-gray-900">{{ $payment->bill->bill_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Bill Date</label>
                    <p class="text-sm text-gray-900">{{ $payment->bill->bill_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Amount</label>
                    <p class="text-sm text-gray-900">${{ number_format($payment->bill->total_amount, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Balance Due</label>
                    <p class="text-sm text-gray-900">${{ number_format($payment->bill->balance_due, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

