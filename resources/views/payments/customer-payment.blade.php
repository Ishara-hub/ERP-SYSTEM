@extends('layouts.modern')

@section('title', 'Customer Payment')

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
                            <h1 class="text-xl font-bold text-gray-900">Customer Payment</h1>
                            <p class="text-sm text-gray-500">Receive and Apply Customer Payments</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Customer Balance -->
                <div class="text-right">
                    <div class="text-sm text-gray-500 uppercase tracking-wider">Customer Balance</div>
                    <div class="text-lg font-semibold text-gray-900" id="customer-balance-header">0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto p-2 space-y-2">
        <!-- Unified Payment Header Card (Matches create.blade.php style) -->
        <div class="bg-white rounded shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4">
                <div class="grid grid-cols-3 gap-4">
                    <!-- Left Column - Title -->
                    <div class="flex items-start pt-4">
                        <h1 class="text-xl font-bold text-gray-900">Receive Payment</h1>
                    </div>

                    <!-- Middle Column - Payment Info -->
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-normal text-gray-700 uppercase">Payment Amount</label>
                            <input type="number" id="payment-amount" step="0.01" min="0" value="0.00" readonly
                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm bg-gray-50 font-bold text-gray-900 text-right focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-normal text-gray-700 uppercase">Date</label>
                                <input type="date" id="payment-date" value="{{ date('Y-m-d') }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-700 uppercase">Reference #</label>
                                <input type="text" id="reference-number" placeholder="Optional"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Payment Method -->
                    <div class="space-y-2">
                        <label class="block text-xs font-normal text-gray-700 uppercase">Payment Method</label>
                        <div class="flex items-center space-x-1">
                            <button type="button" data-method="cash" class="payment-method-btn active flex-1 border border-gray-300 rounded-md p-2 text-center hover:bg-gray-50 transition-all flex flex-col items-center justify-center">
                                <span class="text-lg">💵</span>
                                <span class="text-[9px] font-bold text-gray-700">CASH</span>
                            </button>
                            <button type="button" data-method="check" class="payment-method-btn flex-1 border border-gray-300 rounded-md p-2 text-center hover:bg-gray-50 transition-all flex flex-col items-center justify-center">
                                <span class="text-lg">📝</span>
                                <span class="text-[9px] font-bold text-gray-700">CHECK</span>
                            </button>
                            <button type="button" data-method="credit_debit" class="payment-method-btn flex-1 border border-gray-300 rounded-md p-2 text-center hover:bg-gray-50 transition-all flex flex-col items-center justify-center">
                                <span class="text-lg">💳</span>
                                <span class="text-[9px] font-bold text-gray-700">CARD</span>
                            </button>
                            <button type="button" data-method="e_check" class="payment-method-btn flex-1 border border-gray-300 rounded-md p-2 text-center hover:bg-gray-50 transition-all flex flex-col items-center justify-center">
                                <span class="text-lg">🏦</span>
                                <span class="text-[9px] font-bold text-gray-700">TRANSFER</span>
                            </button>
                        </div>
                        <input type="hidden" id="payment-method" value="cash">
                    </div>
                </div>

                <!-- Second Row - Selection Fields -->
                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-50">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Customer</label>
                        <select id="customer-select" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">A/R Account</label>
                        <select id="ar-account" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @foreach($arAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} · {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Deposit To</label>
                        <select id="deposit-to" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @foreach($depositAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} · {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Section -->
        <div class="bg-white rounded shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-semibold text-gray-700 uppercase tracking-wider">Outstanding Invoices</h3>
                </div>
                <div class="border border-gray-200 rounded overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">✓</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Original Amt</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount Due</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-40">Payment</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 italic">
                                    Select a customer to view unpaid invoices
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="font-semibold text-gray-900">
                                <td colspan="3" class="px-4 py-2 text-right text-sm">Totals</td>
                                <td id="total-original" class="px-4 py-2 text-right text-sm">0.00</td>
                                <td id="total-due" class="px-4 py-2 text-right text-sm">0.00</td>
                                <td id="total-payment" class="px-4 py-2 text-right text-sm text-blue-600">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="bg-white rounded shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4">
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left: Memo -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Memo</label>
                        <textarea id="memo" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none" placeholder="Add a memo..."></textarea>
                    </div>

                    <!-- Right: Summary & Buttons -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2 border border-gray-100 shadow-inner">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">Amounts Summary</div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">AMOUNT DUE</span>
                                <span id="summary-amount-due" class="font-bold text-gray-900">0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">APPLIED</span>
                                <span id="summary-applied" class="font-bold text-blue-600">0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-sm pt-2 border-t border-gray-200">
                                <span class="text-sm font-bold text-gray-900 uppercase">Remaining</span>
                                <span id="summary-remaining" class="font-bold text-gray-900">0.00</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-2">
                            <button type="button" id="btn-clear" class="inline-flex items-center px-4 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                                Clear
                            </button>
                            <button type="button" id="btn-save-new" class="inline-flex items-center px-6 py-1.5 border border-transparent rounded text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all shadow-sm">
                                Save & New
                            </button>
                            <button type="button" id="btn-save-close" class="inline-flex items-center px-6 py-1.5 border border-transparent rounded text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition-all shadow-sm">
                                Save & Close
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
        <span class="text-gray-800 font-medium">Processing Payment...</span>
    </div>
</div>

<style>
    .payment-method-btn.active {
        background-color: #ebf5ff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .payment-method-btn.active span:last-child {
        color: #2563eb;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let invoices = [];
    let selectedMethod = 'cash';

    // Payment method selection
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedMethod = this.dataset.method;
            document.getElementById('payment-method').value = selectedMethod;
        });
    });

    // Customer selection
    document.getElementById('customer-select').addEventListener('change', function() {
        const customerId = this.value;
        if (!customerId) {
            clearInvoiceTable();
            return;
        }
        loadCustomerInvoices(customerId);
    });

    function loadCustomerInvoices(customerId) {
        showLoading('Loading Invoices...');
        fetch('{{ route("customer-payment.invoices") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ customer_id: customerId })
        })
        .then(response => response.json())
        .then(data => {
            invoices = data.invoices;
            document.getElementById('customer-balance-header').textContent = formatNumber(data.balance);
            renderInvoiceTable();
            hideLoading();
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            hideLoading();
        });
    }

    function clearInvoiceTable() {
        invoices = [];
        document.getElementById('customer-balance-header').textContent = '0.00';
        document.getElementById('invoice-table-body').innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-16 text-center text-gray-400 italic">
                    Select a customer to view unpaid invoices
                </td>
            </tr>
        `;
        updateTotals();
    }

    function renderInvoiceTable() {
        const tbody = document.getElementById('invoice-table-body');
        if (invoices.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-16 text-center text-gray-400 italic font-medium">No unpaid invoices found for this customer</td></tr>`;
            updateTotals();
            return;
        }

        tbody.innerHTML = invoices.map((inv, index) => `
            <tr class="hover:bg-blue-50/30 transition-colors">
                <td class="px-4 py-2">
                    <input type="checkbox" class="invoice-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                        data-index="${index}" data-invoice-id="${inv.id}">
                </td>
                <td class="px-4 py-2 text-sm text-gray-600">${formatDate(inv.date)}</td>
                <td class="px-4 py-2 text-sm font-bold text-gray-900">${inv.invoice_no}</td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right">${formatNumber(inv.total_amount)}</td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right font-medium">${formatNumber(inv.balance_due)}</td>
                <td class="px-4 py-2">
                    <input type="number" step="0.01" min="0" max="${parseFloat(inv.balance_due)}" value="0.00"
                        class="payment-input block w-full px-2 py-1 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white"
                        data-index="${index}" data-max="${parseFloat(inv.balance_due)}">
                </td>
            </tr>
        `).join('');

        // Event Listeners
        document.querySelectorAll('.invoice-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const idx = this.dataset.index;
                const input = document.querySelector(`.payment-input[data-index="${idx}"]`);
                if (this.checked) {
                    const balanceDue = parseFloat(invoices[idx].balance_due) || 0;
                    input.value = balanceDue.toFixed(2);
                } else {
                    input.value = '0.00';
                }
                updateTotals();
            });
        });

        document.querySelectorAll('.payment-input').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.dataset.max);
                let val = parseFloat(this.value) || 0;
                if (val > max) {
                    val = max;
                    this.value = max.toFixed(2);
                }
                const idx = this.dataset.index;
                document.querySelector(`.invoice-checkbox[data-index="${idx}"]`).checked = val > 0;
                updateTotals();
            });
        });

        updateTotals();
    }

    function updateTotals() {
        let totalOrig = 0, totalDue = 0, totalPay = 0;
        invoices.forEach((inv, i) => {
            totalOrig += parseFloat(inv.total_amount) || 0;
            totalDue += parseFloat(inv.balance_due) || 0;
            const input = document.querySelector(`.payment-input[data-index="${i}"]`);
            if (input) totalPay += parseFloat(input.value) || 0;
        });

        document.getElementById('total-original').textContent = formatNumber(totalOrig);
        document.getElementById('total-due').textContent = formatNumber(totalDue);
        document.getElementById('total-payment').textContent = formatNumber(totalPay);
        document.getElementById('payment-amount').value = totalPay.toFixed(2);

        document.getElementById('summary-amount-due').textContent = formatNumber(totalDue);
        document.getElementById('summary-applied').textContent = formatNumber(totalPay);
        document.getElementById('summary-remaining').textContent = formatNumber(totalDue - totalPay);
    }

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return (d.getMonth() + 1).toString().padStart(2, '0') + '/' + d.getDate().toString().padStart(2, '0') + '/' + d.getFullYear();
    }

    function showLoading(msg = 'Processing...') {
        document.querySelector('#loading-overlay span').textContent = msg;
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }

    function savePayment(redirectToNew = false) {
        const customerId = document.getElementById('customer-select').value;
        const total = parseFloat(document.getElementById('payment-amount').value);
        const selected = [];
        document.querySelectorAll('.payment-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (val > 0) {
                selected.push({ invoice_id: invoices[input.dataset.index].id, payment_amount: val });
            }
        });

        if (!customerId) {
            alert('Please select a customer first');
            return;
        }

        if (selected.length === 0) {
            alert('Please select at least one invoice and enter a payment amount');
            return;
        }

        showLoading('Saving Payment...');
        fetch('{{ route("customer-payment.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: customerId,
                payment_amount: total,
                payment_date: document.getElementById('payment-date').value,
                payment_method: selectedMethod,
                reference_number: document.getElementById('reference-number').value,
                ar_account_id: document.getElementById('ar-account').value,
                deposit_to_account_id: document.getElementById('deposit-to').value,
                memo: document.getElementById('memo').value,
                invoices: selected
            })
        })
        .then(response => response.json())
        .then(result => {
            hideLoading();
            if (result.success) {
                alert('Payment recorded successfully!');
                if (redirectToNew) location.reload();
                else window.location.href = '{{ route("home") }}';
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error saving payment:', error);
            alert('Failed to save payment');
        });
    }

    document.getElementById('btn-save-close').addEventListener('click', () => savePayment(false));
    document.getElementById('btn-save-new').addEventListener('click', () => savePayment(true));
    document.getElementById('btn-clear').addEventListener('click', () => {
        if (confirm('Clear all data?')) location.reload();
    });
});
</script>
@endsection
