@extends('layouts.modern')

@section('title', 'New Journal Entry')

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
                        <a href="{{ route('journal-entries.web.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Journal Entries</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">New Journal Entry</h1>
                    <p class="mt-1 text-sm text-gray-600">Create a manual journal entry with debit and credit</p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Journal Entry Form -->
        <form method="POST" action="{{ route('journal-entries.web.store') }}" id="journalForm">
            @csrf
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Journal Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input type="date" id="transaction_date" name="transaction_date" required 
                                   value="{{ old('transaction_date', date('Y-m-d')) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                            <input type="text" id="reference" name="reference" 
                                   value="{{ old('reference') }}"
                                   placeholder="Auto-generated if empty"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <input type="text" id="description" name="description" required 
                                   value="{{ old('description') }}"
                                   placeholder="Enter journal description"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal Entry Lines -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Journal Entries</h2>
                    <button type="button" id="addEntryBtn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Line
                    </button>
                </div>
                <div class="p-6">
                    <div id="entriesContainer">
                        <!-- Entry Row 1 -->
                        <div class="entry-row border-b border-gray-200 pb-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account *</label>
                                    <select name="account_id[]" class="account-select block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Debit</label>
                                    <input type="number" name="debit[]" step="0.01" min="0" 
                                           class="debit-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Credit</label>
                                    <input type="number" name="credit[]" step="0.01" min="0" 
                                           class="credit-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" class="remove-entry-btn w-full inline-flex items-center justify-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" name="entry_desc[]" placeholder="Line description (optional)" 
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Totals Summary -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-6">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Total Debit:</span>
                                    <span id="totalDebit" class="ml-2 text-lg font-bold text-gray-900">$0.00</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Total Credit:</span>
                                    <span id="totalCredit" class="ml-2 text-lg font-bold text-gray-900">$0.00</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Difference:</span>
                                    <span id="difference" class="ml-2 text-lg font-bold text-red-600">$0.00</span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500" id="statusMessage">
                                <span class="inline-flex items-center">
                                    <svg class="animate-spin h-4 w-4 text-yellow-500 mr-2 hidden" id="loadingSpinner" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Debits must equal credits</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('journal-entries.web.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" disabled
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Post Journal Entry
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const accountSelect = `@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>@endforeach`;

    // Add new entry row
    $('#addEntryBtn').click(function() {
        const newRow = $(`
            <div class="entry-row border-b border-gray-200 pb-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Account *</label>
                        <select name="account_id[]" class="account-select block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Account</option>
                            ${accountSelect}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Debit</label>
                        <input type="number" name="debit[]" step="0.01" min="0" 
                               class="debit-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Credit</label>
                        <input type="number" name="credit[]" step="0.01" min="0" 
                               class="credit-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-entry-btn w-full inline-flex items-center justify-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <input type="text" name="entry_desc[]" placeholder="Line description (optional)" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
            </div>
        `);
        $('#entriesContainer').append(newRow);
    });

    // Remove entry row
    $(document).on('click', '.remove-entry-btn', function() {
        if ($('.entry-row').length > 1) {
            $(this).closest('.entry-row').remove();
            calculateTotals();
        } else {
            alert('At least one entry line is required.');
        }
    });

    // Calculate totals
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        $('.entry-row').each(function() {
            const debit = parseFloat($(this).find('.debit-input').val()) || 0;
            const credit = parseFloat($(this).find('.credit-input').val()) || 0;
            totalDebit += debit;
            totalCredit += credit;
        });

        $('#totalDebit').text('$' + totalDebit.toFixed(2));
        $('#totalCredit').text('$' + totalCredit.toFixed(2));
        
        const difference = Math.abs(totalDebit - totalCredit);
        const differenceEl = $('#difference');
        differenceEl.text('$' + difference.toFixed(2));

        if (totalDebit.toFixed(2) === totalCredit.toFixed(2) && totalDebit > 0) {
            differenceEl.removeClass('text-red-600').addClass('text-green-600');
            $('#submitBtn').prop('disabled', false);
            $('#statusMessage span:last-child').text('✓ Balanced').removeClass('text-gray-500').addClass('text-green-600');
        } else {
            differenceEl.removeClass('text-green-600').addClass('text-red-600');
            $('#submitBtn').prop('disabled', true);
            $('#statusMessage span:last-child').text('Debits must equal credits').removeClass('text-green-600').addClass('text-gray-500');
        }
    }

    // Prevent both debit and credit being entered
    $(document).on('input', '.debit-input', function() {
        if ($(this).val() > 0) {
            $(this).closest('.entry-row').find('.credit-input').val('');
        }
        calculateTotals();
    });

    $(document).on('input', '.credit-input', function() {
        if ($(this).val() > 0) {
            $(this).closest('.entry-row').find('.debit-input').val('');
        }
        calculateTotals();
    });

    // Initial calculation
    calculateTotals();
});
</script>
@endpush

@endsection

