@extends('layouts.modern')

@section('title', 'Pay Bill')
@section('breadcrumb', 'Pay Bill')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pay Bill</h1>
                <p class="mt-1 text-sm text-gray-600">Record payment for a supplier bill</p>
            </div>
            <a href="{{ route('bills.pay-bill.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('bills.pay-bill.create') }}" class="bg-white rounded-lg shadow-sm border mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_id" onchange="this.form.submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ (request('supplier_id') == $supplier->id) ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Liability Account</label>
                <select name="liability_account_id" onchange="this.form.submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Liability Account</option>
                    @foreach($liabilityAccounts as $account)
                        <option value="{{ $account->id }}" {{ (request('liability_account_id') == $account->id) ? 'selected' : '' }}>
                            {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <!-- Payment Form -->
    @if($selectedSupplier && $selectedLiabilityAccount)
    <form method="POST" action="{{ route('bills.pay-bill.store') }}" class="bg-white rounded-lg shadow-sm border" id="payment-form">
        @csrf
        <input type="hidden" name="supplier_id" value="{{ $selectedSupplier->id }}">
        <input type="hidden" name="liability_account_id" value="{{ $selectedLiabilityAccount->id }}">
        <div class="p-6 space-y-6">
            <!-- Payment Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                        <select name="payment_method" required class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
                        <select name="bank_account_id" required class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Bank Account</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input type="text" name="reference" placeholder="Optional reference" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Unpaid Bills -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Unpaid Bills</h3>
                @if($unpaidBills->count() > 0)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bill #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($unpaidBills as $bill)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="checkbox" name="selected_bills[]" value="{{ $bill->id }}" data-total="{{ $bill->balance_due }}" class="bill-checkbox">
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $bill->bill_number }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $bill->bill_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $bill->due_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">${{ number_format($bill->total_amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">${{ number_format($bill->total_payments, 2) }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">${{ number_format($bill->balance_due, 2) }}</td>
                                        <td class="px-4 py-2">
                                            <input type="number" name="payment_amount[{{ $bill->id }}]" step="0.01" min="0" max="{{ $bill->balance_due }}" placeholder="0.00" class="w-24 px-2 py-1 border border-gray-300 rounded text-sm bill-payment">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        No unpaid bills found for this supplier and liability account combination.
                    </div>
                @endif
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" placeholder="Optional notes..." class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Record Payment
                </button>
            </div>
        </div>
    </form>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">Please select a supplier and liability account to view unpaid bills.</p>
        </div>
    @endif
</div>

<script>
// Auto-fill payment amount with balance due when checkbox is clicked
document.querySelectorAll('.bill-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        const paymentInput = row.querySelector('.bill-payment');
        const total = this.getAttribute('data-total');
        
        if (this.checked) {
            paymentInput.value = total;
        } else {
            paymentInput.value = '';
        }
    });
});

// Debug form submission
document.getElementById('payment-form').addEventListener('submit', function(e) {
    console.log('Form submitted');
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log(key, ':', value);
    }
});
</script>
@endsection
