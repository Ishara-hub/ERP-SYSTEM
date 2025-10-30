@extends('layouts.modern')

@section('title', 'Write Check')

@section('content')
<div class="min-h-screen max-w-7xl mx-auto bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Top Toolbar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-2 py-1">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-1">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Write Check</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Form -->
    <div class="max-w-7xl mx-auto px-2 py-2">
        <form id="write-check-form" action="{{ route('accounts.write-check.store') }}" method="POST">
            @csrf
            
            <!-- Error Messages Display -->
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Please correct the following errors:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Hidden fields for print_later and pay_online -->
            <input type="hidden" id="print-later" name="print_later" value="0">
            <input type="hidden" id="pay-online" name="pay_online" value="0">
            
            <!-- Bank Account Selection -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2 mb-2">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">BANK ACCOUNT <span class="text-red-500">*</span></label>
                            <select id="bank-account-select" 
                                    name="bank_account_id"
                                    class="flex-1 min-w-[250px] px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bank_account_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select Bank Account</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                            data-balance="{{ $account->current_balance ?? 0 }}"
                                            {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_code }} - {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">ENDING BALANCE:</label>
                            <div class="text-lg font-bold text-gray-900 min-w-[100px] text-right" id="ending-balance">$0.00</div>
                        </div>
                    </div>
                    @error('bank_account_id')
                        <p class="text-sm text-red-600 whitespace-nowrap">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Check Visual Area with Green Background -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 border-b- border-gray-300 max-w-3xl style="background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255, 255, 255, 0.1) 35px, rgba(255,255,255,.1) 70px);">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Left Side - Payee Info -->
                        <div class="space-y-2">
                            <div class="flex justify-end items-start">
                                <label class="block text-xs font-normal text-gray-800 mb-1">PAY TO THE ORDER OF</label>
                                <select id="pay-to-select"
                                        name="pay_to"
                                        class="block w-full px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pay_to') border-red-500 @enderror"
                                        required>
                                    <option value="">Select Vendor/Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->name }}" 
                                                data-address="{{ $supplier->address ?? '' }}" 
                                                data-id="{{ $supplier->id }}"
                                                {{ old('pay_to') == $supplier->name ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pay_to')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-start items-start">
                                <label class="block text-xs font-normal text-gray-800 mb-3 mr-2">ADDRESS</label>
                                <textarea id="pay-to-address" 
                                          name="pay_to_address"
                                          rows="4"
                                          class="block w-60 px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('pay_to_address') border-red-500 @enderror">{{ old('pay_to_address') }}</textarea>
                                @error('pay_to_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-gray-800 mb-1">MEMO</label>
                                <input type="text" 
                                       id="check-memo"
                                       name="memo"
                                       value="{{ old('memo') }}"
                                       class="block w-full px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('memo') border-red-500 @enderror">
                                @error('memo')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Side - Check Details -->
                        <div class="space-y-3 flex flex-col">
                            <div class="flex justify-end items-start space-x-4">
                                <div class="flex justify-end items-center">
                                    <label class="block text-xs font-normal text-gray-800 mb-3 mr-2">NO.</label>
                                    <input type="text" 
                                           id="check-number"
                                           name="check_number"
                                           value="{{ old('check_number', $nextCheckNumber) }}"
                                           class="block w-40 px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm font-normal text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('check_number') border-red-500 @enderror"
                                           required>
                                    @error('check_number')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <div class="flex justify-end items-center">
                                    <label class="block text-xs font-normal text-gray-800 mb-3 mr-2">DATE</label>
                                    <input type="date" 
                                           id="check-date"
                                           name="check_date"
                                           value="{{ old('check_date', date('Y-m-d')) }}"
                                           class="block w-40 px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm font-normal text-right  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('check_date') border-red-500 @enderror"
                                           required>
                                    @error('check_date')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <div class="flex justify-end items-center">
                                    <label class="block text-xs font-normal text-gray-800 mr-2 mb-3">$</label>
                                    <input type="number" 
                                           id="check-amount"
                                           name="amount"
                                           step="0.01"
                                           min="0.01"
                                           value="{{ old('amount', 0) }}"
                                           class="block w-40 px-2 py-1.5 border border-gray-400 rounded-lg bg-white text-sm font-normal text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-500 @enderror"
                                           required>
                                    @error('amount')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <div class="flex justify-end items-center">
                                    <label class="block text-xs font-normal text-gray-800 mr-2 mb-3">DOLLARS</label>
                                    <div class="px-4 py-1.5 border-1 border-gray-400 rounded-lg bg-white text-sm font-normal text-gray-700 min-h-[2.5rem] flex items-center" id="amount-in-words">
                                        Zero dollars and 00/100
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Expenses/Items Tabs -->
                <div class="border-t border-gray-200 p-2 mb-2">
                    <div class="border-b border-gray-200">
                        <nav class="flex" aria-label="Tabs">
                            <button type="button" 
                                    id="expenses-tab"
                                    class="expense-tab active px-6 py-3 text-sm font-medium border-b-2 text-blue-600 border-blue-600">
                                Expenses <span class="ml-2 text-gray-500" id="expenses-total">0.00</span>
                            </button>
                            <button type="button" 
                                    id="items-tab"
                                    class="expense-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Items <span class="ml-2 text-gray-500" id="items-total">0.00</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Expenses Table -->
                    <div id="expenses-section" class="expense-section p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-1 py-0.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">ACCOUNT</th>
                                        <th class="px-1 py-0.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">AMOUNT</th>
                                        <th class="px-1 py-0.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">MEMO</th>
                                        <th class="px-1 py-0.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">CU:JOB</th>
                                        <th class="px-1 py-0.5 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody id="expenses-tbody" class="bg-white divide-y divide-gray-200">
                                    <tr class="expense-row">
                                        <td class="px-1 py-1.5">
                                            <select name="expenses[0][account_id]" 
                                                    class="expense-account-select block w-60 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    required>
                                                <option value="">Select Account</option>
                                                @foreach($expenseAccounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->account_code }} - {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-1 py-0.5">
                                            <input type="number" 
                                                   name="expenses[0][amount]"
                                                   step="0.01"
                                                   min="0.01"
                                                   class="expense-amount-input block w-32 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00"
                                                   required>
                                        </td>
                                        <td class="px-1 py-0.5">
                                            <input type="text" 
                                                   name="expenses[0][memo]"
                                                   class="block w-80 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Memo">
                                        </td>
                                        <td class="px-1 py-0.5">
                                            <select name="expenses[0][customer_id]" 
                                                    class="expense-customer-select block w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">--</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">
                                                        {{ $customer->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-1 py-0.5 text-center">
                                            <button type="button" 
                                                    class="remove-expense-btn text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    disabled>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" 
                                id="add-expense-btn"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Expense Line
                        </button>
                    </div>

                    <!-- Items Section (Placeholder) -->
                    <div id="items-section" class="expense-section hidden p-6">
                        <div class="text-center py-8 text-gray-500">
                            <p>Items functionality coming soon</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button type="button" id="save-close-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save & Close
                    </button>
                    <button type="submit" id="save-new-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save & New
                    </button>
                    <button type="button" id="clear-form-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Clear
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const suppliers = @json($suppliers);
    let expenseRowIndex = 0;
    
    // Update ending balance when bank account changes
    document.getElementById('bank-account-select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const balance = selectedOption.getAttribute('data-balance') || 0;
        document.getElementById('ending-balance').textContent = '$' + parseFloat(balance).toFixed(2);
    });

    // Auto-fill address when supplier is selected
    document.getElementById('pay-to-select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const address = selectedOption.getAttribute('data-address') || '';
        document.getElementById('pay-to-address').value = address;
    });

    // Convert amount to words
    function numberToWords(amount) {
        if (amount === 0) return 'Zero dollars and 00/100';
        
        const wholeNumber = Math.floor(amount);
        const cents = Math.round((amount - wholeNumber) * 100);
        
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 
                     'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 
                     'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        function convertHundreds(num) {
            if (num === 0) return '';
            if (num < 20) return ones[num];
            if (num < 100) {
                const ten = Math.floor(num / 10);
                const one = num % 10;
                return tens[ten] + (one > 0 ? '-' + ones[one] : '');
            }
            const hundred = Math.floor(num / 100);
            const remainder = num % 100;
            return ones[hundred] + ' Hundred' + (remainder > 0 ? ' ' + convertHundreds(remainder) : '');
        }
        
        function convertThousands(num) {
            if (num < 1000) return convertHundreds(num);
            const thousand = Math.floor(num / 1000);
            const remainder = num % 1000;
            return convertThousands(thousand) + ' Thousand' + (remainder > 0 ? ' ' + convertHundreds(remainder) : '');
        }
        
        function convertMillions(num) {
            if (num < 1000000) return convertThousands(num);
            const million = Math.floor(num / 1000000);
            const remainder = num % 1000000;
            return convertHundreds(million) + ' Million' + (remainder > 0 ? ' ' + convertThousands(remainder) : '');
        }
        
        const dollarsText = wholeNumber > 0 ? convertMillions(wholeNumber) + ' Dollar' + (wholeNumber !== 1 ? 's' : '') : 'Zero Dollars';
        const centsText = cents.toString().padStart(2, '0');
        
        return dollarsText + ' and ' + centsText + '/100';
    }

    // Update amount in words when manually changed
    document.getElementById('check-amount').addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        document.getElementById('amount-in-words').textContent = numberToWords(amount);
    });

    // Tab switching
    document.getElementById('expenses-tab').addEventListener('click', function() {
        document.getElementById('expenses-section').classList.remove('hidden');
        document.getElementById('items-section').classList.add('hidden');
        this.classList.add('active', 'text-blue-600', 'border-blue-600');
        this.classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('items-tab').classList.remove('active', 'text-blue-600', 'border-blue-600');
        document.getElementById('items-tab').classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('items-tab').addEventListener('click', function() {
        document.getElementById('items-section').classList.remove('hidden');
        document.getElementById('expenses-section').classList.add('hidden');
        this.classList.add('active', 'text-blue-600', 'border-blue-600');
        this.classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('expenses-tab').classList.remove('active', 'text-blue-600', 'border-blue-600');
        document.getElementById('expenses-tab').classList.add('border-transparent', 'text-gray-500');
    });

    // Add expense row
    document.getElementById('add-expense-btn').addEventListener('click', function() {
        expenseRowIndex++;
        const tbody = document.getElementById('expenses-tbody');
        const firstRow = tbody.querySelector('.expense-row').cloneNode(true);
        
        // Update input names
        firstRow.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', '[' + expenseRowIndex + ']'));
                input.value = '';
                if (input.type === 'checkbox') input.checked = false;
            }
        });
        
        // Enable remove button
        const removeBtn = firstRow.querySelector('.remove-expense-btn');
        removeBtn.disabled = false;
        
        tbody.appendChild(firstRow);
        updateRemoveButtons();
    });

    // Remove expense row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-expense-btn') && !e.target.closest('.remove-expense-btn').disabled) {
            const row = e.target.closest('.expense-row');
            const tbody = document.getElementById('expenses-tbody');
            
            if (tbody.children.length > 1) {
                row.remove();
                updateRemoveButtons();
                calculateExpenseTotal();
            }
        }
    });

    // Update remove buttons state
    function updateRemoveButtons() {
        const tbody = document.getElementById('expenses-tbody');
        const rows = tbody.querySelectorAll('.expense-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-expense-btn');
            removeBtn.disabled = rows.length === 1;
        });
    }

    // Calculate expense total
    function calculateExpenseTotal() {
        let total = 0;
        document.querySelectorAll('.expense-amount-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('expenses-total').textContent = '$' + total.toFixed(2);
        
        // Auto-fill check amount with expense total
        const checkAmountInput = document.getElementById('check-amount');
        if (total > 0) {
            checkAmountInput.value = total.toFixed(2);
            // Update amount in words
            document.getElementById('amount-in-words').textContent = numberToWords(total);
        } else {
            checkAmountInput.value = '0.00';
            document.getElementById('amount-in-words').textContent = 'Zero dollars and 00/100';
        }
    }

    // Update total when expense amounts change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('expense-amount-input')) {
            calculateExpenseTotal();
        }
    });

    // Sync toolbar checkboxes with hidden form fields (if they exist)
    const toolbarPrintLater = document.getElementById('toolbar-print-later');
    if (toolbarPrintLater) {
        toolbarPrintLater.addEventListener('change', function() {
            document.getElementById('print-later').value = this.checked ? '1' : '0';
        });
    }
    
    const toolbarPayOnline = document.getElementById('toolbar-pay-online');
    if (toolbarPayOnline) {
        toolbarPayOnline.addEventListener('change', function() {
            document.getElementById('pay-online').value = this.checked ? '1' : '0';
        });
    }

    // Toolbar button handlers (if they exist)
    const toolbarSaveBtn = document.getElementById('toolbar-save-btn');
    if (toolbarSaveBtn) {
        toolbarSaveBtn.addEventListener('click', function() {
            document.getElementById('save-new-btn').click();
        });
    }

    // Clear Splits button (if it exists)
    const clearSplitsBtn = document.getElementById('clear-splits-btn');
    if (clearSplitsBtn) {
        clearSplitsBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all expense lines?')) {
                const tbody = document.getElementById('expenses-tbody');
                const firstRow = tbody.querySelector('.expense-row').cloneNode(true);
                firstRow.querySelectorAll('input, select').forEach(input => {
                    if (input.type !== 'checkbox' || input.value === '1') {
                        input.value = '';
                        if (input.type === 'checkbox') input.checked = false;
                    }
                });
                tbody.innerHTML = '';
                tbody.appendChild(firstRow);
                updateRemoveButtons();
                calculateExpenseTotal();
            }
        });
    }

    // Recalculate button (if it exists)
    const recalculateBtn = document.getElementById('recalculate-btn');
    if (recalculateBtn) {
        recalculateBtn.addEventListener('click', function() {
            calculateExpenseTotal();
        });
    }

    // Clear form button
    document.getElementById('clear-form-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all form data?')) {
            document.getElementById('write-check-form').reset();
            document.getElementById('pay-to-address').value = '';
            document.getElementById('amount-in-words').textContent = 'Zero dollars and 00/100';
            document.getElementById('ending-balance').textContent = '$0.00';
            const tbody = document.getElementById('expenses-tbody');
            const firstRow = tbody.querySelector('.expense-row').cloneNode(true);
            firstRow.querySelectorAll('input, select').forEach(input => {
                if (input.type !== 'checkbox' || input.value === '1') {
                    input.value = '';
                    if (input.type === 'checkbox') input.checked = false;
                }
            });
            tbody.innerHTML = '';
            tbody.appendChild(firstRow);
            updateRemoveButtons();
            calculateExpenseTotal();
        }
    });

    // Save & Close button
    document.getElementById('save-close-btn').addEventListener('click', function() {
        const form = document.getElementById('write-check-form');
        // Recalculate to ensure amounts are synced
        calculateExpenseTotal();
        
        if (form.checkValidity()) {
            // Create a hidden input to indicate save & close
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'save_and_close';
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);
            form.submit();
        }
    });

    // Form submission (Save & New)
    document.getElementById('write-check-form').addEventListener('submit', function(e) {
        // Ensure amounts are calculated before submit
        calculateExpenseTotal();
        
        // Validate bank account
        const bankAccount = document.getElementById('bank-account-select').value;
        if (!bankAccount) {
            e.preventDefault();
            alert('Please select a bank account.');
            document.getElementById('bank-account-select').focus();
            return false;
        }
        
        // Validate required fields
        const payTo = document.getElementById('pay-to-select').value;
        if (!payTo) {
            e.preventDefault();
            alert('Please select a vendor/supplier to pay.');
            document.getElementById('pay-to-select').focus();
            return false;
        }
        
        // Validate check number
        const checkNumber = document.getElementById('check-number').value;
        if (!checkNumber) {
            e.preventDefault();
            alert('Please enter a check number.');
            document.getElementById('check-number').focus();
            return false;
        }
        
        // Validate check amount
        const checkAmount = parseFloat(document.getElementById('check-amount').value) || 0;
        if (checkAmount <= 0) {
            e.preventDefault();
            alert('Check amount must be greater than zero.');
            document.getElementById('check-amount').focus();
            return false;
        }
        
        let hasExpenses = false;
        let expenseTotal = 0;
        document.querySelectorAll('.expense-account-select').forEach((select, index) => {
            if (select.value) {
                hasExpenses = true;
                const amountInput = select.closest('tr').querySelector('.expense-amount-input');
                if (amountInput && amountInput.value) {
                    expenseTotal += parseFloat(amountInput.value) || 0;
                }
            }
        });
        
        if (!hasExpenses) {
            e.preventDefault();
            alert('Please add at least one expense line with an account selected.');
            return false;
        }

        // Show loading
        const saveBtn = document.getElementById('save-new-btn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';
        }
    });

    // Initialize
    updateRemoveButtons();
    calculateExpenseTotal();
});
</script>
@endpush
@endsection
