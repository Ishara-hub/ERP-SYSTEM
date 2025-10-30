@extends('layouts.modern')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bill Details</h1>
                <p class="mt-2 text-gray-600">Bill #{{ $bill->bill_number }}</p>
            </div>
            <div class="flex space-x-3">
                @if($bill->status === 'draft')
                    <a href="{{ route('bills.enter-bill.edit', $bill) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Bill
                    </a>
                @endif
                @if($bill->canBePaid())
                    <a href="{{ route('bills.pay-bill.create', ['bill_id' => $bill->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Pay Bill
                    </a>
                @endif
                <a href="{{ route('bills.enter-bill.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Bills
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Bill Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Bill Information</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Supplier</label>
                                <p class="text-sm text-gray-900">{{ $bill->supplier ? $bill->supplier->name : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Liability Account</label>
                                <p class="text-sm text-gray-900">{{ $bill->liabilityAccount ? $bill->liabilityAccount->account_name . ' (' . $bill->liabilityAccount->account_code . ')' : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Bill Date</label>
                                <p class="text-sm text-gray-900">{{ $bill->bill_date ? $bill->bill_date->format('M d, Y') : 'Not set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Due Date</label>
                                <p class="text-sm text-gray-900">{{ $bill->due_date ? $bill->due_date->format('M d, Y') : 'Not set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Reference</label>
                                <p class="text-sm text-gray-900">{{ $bill->reference ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $bill->status_color }}-100 text-{{ $bill->status_color }}-800">
                                    {{ ucfirst($bill->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Bill Items ({{ $bill->items->count() }})</h3>
                        @if($bill->items->count() > 0)
                            <p class="text-xs text-green-600 mt-1">✓ Items loaded successfully</p>
                        @else
                            <p class="text-xs text-red-600 mt-1">⚠ No items found</p>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense Account</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Amount</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($bill->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->expenseAccount ? $item->expenseAccount->account_name : 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($item->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($item->tax_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($bill->memo || $bill->terms)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            @if($bill->memo)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Memo</label>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $bill->memo }}</p>
                                </div>
                            @endif
                            @if($bill->terms)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Terms</label>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $bill->terms }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Financial Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Financial Summary</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span class="text-sm text-gray-900">${{ number_format($bill->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Tax Amount:</span>
                            <span class="text-sm text-gray-900">${{ number_format($bill->tax_amount, 2) }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                                <span class="text-lg font-bold text-gray-900">${{ number_format($bill->total_amount, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Paid Amount:</span>
                            <span class="text-sm text-green-600">${{ number_format($bill->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Balance Due:</span>
                            <span class="text-sm font-bold {{ $bill->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ${{ number_format($bill->balance_due, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                @if($bill->payments->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="space-y-3">
                                @foreach($bill->payments as $payment)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Bill Details -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Bill Details</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Created:</span>
                            <span class="text-sm text-gray-900">{{ $bill->created_at ? $bill->created_at->format('M d, Y g:i A') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Created By:</span>
                            <span class="text-sm text-gray-900">{{ $bill->createdBy ? $bill->createdBy->name : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated:</span>
                            <span class="text-sm text-gray-900">{{ $bill->updated_at ? $bill->updated_at->format('M d, Y g:i A') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


