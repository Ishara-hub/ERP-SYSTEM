@extends('layouts.modern')

@section('title', 'Record Deposit')

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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Record Deposit</h1>
                            <p class="text-sm text-gray-500">Deposit payments into bank account</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Deposit Total -->
                <div class="text-right">
                    <div class="text-sm text-gray-500">Deposit Total</div>
                    <div class="text-lg font-semibold text-gray-900" id="deposit-total-header">0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Bar -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center space-x-6">
                <!-- Bank Account Selection -->
                <div class="flex-1 max-w-sm">
                    <label class="block text-sm font-semibold text-gray-700 mb-1 uppercase tracking-wider">Deposit To:</label>
                    <select id="bank-account-select" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Select Bank Account...</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} · {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Selection -->
                <div class="flex-1 max-w-xs">
                    <label class="block text-sm font-semibold text-gray-700 mb-1 uppercase tracking-wider">Date:</label>
                    <input type="date" id="deposit-date" value="{{ date('Y-m-d') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto p-4 space-y-4">
        <!-- Undeposited Payments Table Card -->
        <div class="bg-white rounded shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-semibold text-gray-700 uppercase tracking-wider">Select Payments to Deposit</h3>
                </div>
                <div class="border border-gray-200 rounded overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">✓</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received From</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref #</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="payments-table-body" class="bg-white divide-y divide-gray-200">
                            @forelse($undepositedPayments as $payment)
                                <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer payment-row" data-id="{{ $payment->id }}" data-amount="{{ $payment->amount }}">
                                    <td class="px-4 py-2">
                                        <input type="checkbox" class="payment-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $payment->id }}">
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->payment_date->format('m/d/Y') }}</td>
                                    <td class="px-4 py-2 text-sm font-bold text-gray-900">{{ $payment->invoice->customer->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->reference ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 uppercase">{{ $payment->payment_method }}</td>
                                    <td class="px-4 py-2 text-sm font-bold text-gray-900 text-right">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-16 text-center text-gray-400 italic">
                                        No undeposited payments found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="font-semibold text-gray-900">
                                <td colspan="5" class="px-4 py-2 text-right text-sm uppercase tracking-widest text-gray-500">Total Selected</td>
                                <td id="total-selected-amount" class="px-4 py-2 text-right text-sm text-blue-600">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="bg-white rounded shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left Side: Memo -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Memo</label>
                            <textarea id="memo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none" placeholder="Optional deposit memo..."></textarea>
                        </div>
                    </div>

                    <!-- Right Side: Summary & Buttons -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3 shadow-inner">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">Deposit Summary</div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">SELECTED PAYMENTS</span>
                                <span id="summary-count" class="font-bold text-gray-900">0</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                <span class="text-sm font-bold text-gray-900 uppercase tracking-widest">Deposit Subtotal</span>
                                <span id="summary-amount" class="text-lg font-bold text-blue-600">0.00</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" id="btn-clear" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                                Clear Selection
                            </button>
                            <button type="button" id="btn-save" class="inline-flex items-center px-8 py-2 border border-transparent rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none transition-all shadow-md">
                                Record Deposit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-2xl">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-gray-800 font-medium">Recording Deposit...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    const rows = document.querySelectorAll('.payment-row');
    
    // Toggle row selection on click
    rows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const cb = this.querySelector('.payment-checkbox');
                cb.checked = !cb.checked;
                updateTotals();
            }
        });
    });

    // Handle checkbox changes directly
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateTotals);
    });

    function updateTotals() {
        let total = 0;
        let count = 0;
        
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const row = cb.closest('.payment-row');
                total += parseFloat(row.dataset.amount);
                count++;
                row.classList.add('bg-blue-50/50');
            } else {
                cb.closest('.payment-row').classList.remove('bg-blue-50/50');
            }
        });

        const formattedTotal = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('total-selected-amount').textContent = formattedTotal;
        document.getElementById('deposit-total-header').textContent = formattedTotal;
        document.getElementById('summary-amount').textContent = formattedTotal;
        document.getElementById('summary-count').textContent = count;
    }

    document.getElementById('btn-clear').addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        updateTotals();
    });

    document.getElementById('btn-save').addEventListener('click', function() {
        const bankAccountId = document.getElementById('bank-account-select').value;
        const depositDate = document.getElementById('deposit-date').value;
        const memo = document.getElementById('memo').value;
        const selectedPaymentIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        let totalAmount = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) totalAmount += parseFloat(cb.closest('.payment-row').dataset.amount);
        });

        if (!bankAccountId) {
            alert('Please select a bank account');
            return;
        }

        if (selectedPaymentIds.length === 0) {
            alert('Please select at least one payment to deposit');
            return;
        }

        showLoading();

        fetch('{{ route("record-deposit.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                bank_account_id: bankAccountId,
                deposit_date: depositDate,
                memo: memo,
                payment_ids: selectedPaymentIds,
                total_amount: totalAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                alert('Deposit recorded successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error saving deposit:', error);
            alert('Failed to record deposit');
        });
    });

    function showLoading() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }
});
</script>
@endsection

