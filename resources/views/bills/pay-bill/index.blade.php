@extends('layouts.modern')

@section('title', 'Bill Payments')
@section('breadcrumb', 'Bill Payments')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bill Payments</h1>
                <p class="mt-1 text-sm text-gray-600">View all payments made to bills</p>
            </div>
            <a href="{{ route('bills.pay-bill.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Pay Bill
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-4 py-3">
            <form method="GET" action="{{ route('bills.pay-bill.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by reference..." class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <select name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Status</option>
                            <option value="pending" {{ isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ isset($filters['status']) && $filters['status'] === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ isset($filters['status']) && $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <select name="payment_method" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Methods</option>
                            <option value="cash" {{ isset($filters['payment_method']) && $filters['payment_method'] === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="check" {{ isset($filters['payment_method']) && $filters['payment_method'] === 'check' ? 'selected' : '' }}>Check</option>
                            <option value="bank_transfer" {{ isset($filters['payment_method']) && $filters['payment_method'] === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="credit_card" {{ isset($filters['payment_method']) && $filters['payment_method'] === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" placeholder="From date" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" placeholder="To date" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Filter
                    </button>
                    <a href="{{ route('bills.pay-bill.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->payment_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($payment->bill)
                                <a href="{{ route('bills.enter-bill.show', $payment->bill) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $payment->bill->bill_number }}
                                </a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($payment->bill && $payment->bill->supplier)
                                {{ $payment->bill->supplier->name }}
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->reference ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($payment->status === 'completed') bg-green-100 text-green-800
                                @elseif($payment->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No payments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
