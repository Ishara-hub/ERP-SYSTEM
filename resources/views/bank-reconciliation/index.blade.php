@extends('layouts.modern')

@section('title', 'Bank Reconciliation')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Bank Reconciliation</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Bank Reconciliation</h1>
                    <p class="mt-1 text-sm text-gray-600">Reconcile bank transactions with system records</p>
                </div>
                <div class="space-x-3">
                    @if($selectedAccountId)
                        @if(!session('reconciliation_session'))
                            <button onclick="showBeginReconciliationModal()" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Begin Reconciliation
                            </button>
                        @endif
                        <button onclick="showImportModal()" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Import Statement
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Bank Account Selector -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('bank-reconciliation.index') }}" class="flex items-end space-x-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank Account</label>
                        <select name="bank_account_id" onchange="this.form.submit()" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Bank Account --</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}" {{ $selectedAccountId == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedAccountId)
            @if(session('reconciliation_session'))
                <!-- Show Reconciliation Period -->
                <div class="mb-4 bg-white shadow-sm rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">For period: {{ session('reconciliation_statement_date') }}</h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Hide transactions after the statement's end date</span>
                            </label>
                            <button type="button" onclick="endReconciliation()" class="text-sm text-red-600 hover:text-red-800">
                                End Reconciliation
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Two Column Layout: Payments Left, Deposits Right -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Left Column: Payments / Withdrawals -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 bg-green-100 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-green-900">Checks and Payments</h2>
                    </div>
                    
                    <!-- Bank Withdrawals -->
                    @if($bankWithdrawals->count() > 0)
                        <div class="border-b border-gray-200">
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">
                                                <input type="checkbox" onclick="toggleAllWithdrawals(this)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase cursor-pointer hover:bg-green-100">
                                                DATE <svg class="inline ml-1" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">CHK #</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">PAYEE</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($bankWithdrawals as $transaction)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-3 whitespace-nowrap">
                                                    <input type="checkbox" name="bank_transaction_ids[]" 
                                                           value="{{ $transaction->id }}" 
                                                           class="withdrawal-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                                           onchange="updateReconciliationSummary()">
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->transaction_date->format('m/d/Y') }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->check_number ?? '' }}
                                                </td>
                                                <td class="px-3 py-3 text-sm text-gray-900">
                                                    {{ $transaction->description ?? 'N/A' }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-right">
                                                    {{ number_format($transaction->amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- System Paid Payments -->
                    @if($unreconciledPayments->where('payment_type', 'paid')->count() > 0)
                        <div>
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">
                                                <input type="checkbox" onclick="toggleAllPaidPayments(this)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase cursor-pointer hover:bg-green-100">
                                                DATE <svg class="inline ml-1" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">CHK #</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">PAYEE</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($unreconciledPayments->where('payment_type', 'paid') as $payment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-3 whitespace-nowrap">
                                                    <input type="checkbox" name="payment_ids[]" 
                                                           value="{{ $payment->id }}" 
                                                           class="paid-payment-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                                           onchange="updateReconciliationSummary()">
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->payment_date->format('m/d/Y') }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->check_number ?? '' }}
                                                </td>
                                                <td class="px-3 py-3 text-sm text-gray-900">
                                                    {{ $payment->payee ?? 'N/A' }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-right">
                                                    {{ number_format($payment->amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($bankWithdrawals->count() == 0 && $unreconciledPayments->where('payment_type', 'paid')->count() == 0)
                        <div class="p-6 text-center text-gray-500">
                            No checks or payments found.
                        </div>
                    @endif
                </div>

                <!-- Right Column: Deposits -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 bg-green-100 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-green-900">Deposits and Other Credits</h2>
                    </div>
                    
                    <!-- Bank Deposits -->
                    @if($bankDeposits->count() > 0)
                        <div class="border-b border-gray-200">
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">
                                                <input type="checkbox" onclick="toggleAllDeposits(this)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase cursor-pointer hover:bg-green-100">
                                                DATE <svg class="inline ml-1" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">CHK #</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">MEMO</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">TYPE</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($bankDeposits as $transaction)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-3 whitespace-nowrap">
                                                    <input type="checkbox" name="bank_transaction_ids[]" 
                                                           value="{{ $transaction->id }}" 
                                                           class="deposit-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                                           onchange="updateReconciliationSummary()">
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->transaction_date->format('m/d/Y') }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->check_number ?? '' }}
                                                </td>
                                                <td class="px-3 py-3 text-sm text-gray-900">
                                                    {{ $transaction->description ?? 'N/A' }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ strtoupper(substr($transaction->type, 0, 3)) }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-right">
                                                    {{ number_format($transaction->amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- System Received Payments -->
                    @if($unreconciledPayments->where('payment_type', 'received')->count() > 0)
                        <div>
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">
                                                <input type="checkbox" onclick="toggleAllReceivedPayments(this)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase cursor-pointer hover:bg-green-100">
                                                DATE <svg class="inline ml-1" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">CHK #</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">MEMO</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase">TYPE</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($unreconciledPayments->where('payment_type', 'received') as $payment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-3 whitespace-nowrap">
                                                    <input type="checkbox" name="payment_ids[]" 
                                                           value="{{ $payment->id }}" 
                                                           class="received-payment-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                                           onchange="updateReconciliationSummary()">
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->payment_date->format('m/d/Y') }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->check_number ?? '' }}
                                                </td>
                                                <td class="px-3 py-3 text-sm text-gray-900">
                                                    {{ $payment->notes ?? 'Deposit' }}
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    DEP
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-right">
                                                    {{ number_format($payment->amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($bankDeposits->count() == 0 && $unreconciledPayments->where('payment_type', 'received')->count() == 0)
                        <div class="p-6 text-center text-gray-500">
                            No deposits or credits found.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reconciliation Summary Panel (Only shown during active reconciliation) -->
            @if(session('reconciliation_session'))
            <form action="{{ route('bank-reconciliation.reconcile') }}" method="POST" id="reconcileForm">
                @csrf
                <input type="hidden" name="bank_account_id" value="{{ $selectedAccountId }}">
                <input type="hidden" name="statement_date" value="{{ session('reconciliation_statement_date') }}">
                <input type="hidden" name="ending_balance" value="{{ session('reconciliation_ending_balance') }}">
                <input type="hidden" name="beginning_balance" value="{{ session('reconciliation_beginning_balance') }}">

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Side: Controls -->
                            <div>
                                <div class="flex items-center space-x-4 mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Highlight Marked</span>
                                    </label>
                                    <button type="button" class="text-sm text-gray-600 hover:text-gray-800">Mark All</button>
                                    <button type="button" class="text-sm text-gray-600 hover:text-gray-800">Unmark All</button>
                                    <button type="button" class="text-sm text-gray-600 hover:text-gray-800">Go To</button>
                                </div>
                                <div>
                                    <button type="button" class="text-sm text-gray-600 hover:text-gray-800">Columns to Display...</button>
                                </div>
                            </div>

                            <!-- Right Side: Summary -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Reconciliation Summary</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Beginning Balance:</span>
                                        <span class="font-medium">${{ number_format(session('reconciliation_beginning_balance') ?? 0, 2) }}</span>
                                    </div>
                                    <div class="pl-4 pt-2 space-y-1">
                                        <div class="flex justify-between text-gray-600">
                                            <span><span id="markedDepositsCount">0</span> Deposits and Other Credits:</span>
                                            <span id="markedDepositsAmount">$0.00</span>
                                        </div>
                                        <div class="flex justify-between text-gray-600">
                                            <span><span id="markedPaymentsCount">0</span> Checks and Payments:</span>
                                            <span id="markedPaymentsAmount">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-200">
                                        <span class="text-gray-600">Service Charge:</span>
                                        <span class="font-medium">${{ number_format(session('reconciliation_service_charge') ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Interest Earned:</span>
                                        <span class="font-medium">${{ number_format(session('reconciliation_interest_earned') ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t-2 border-gray-300 pt-2">
                                        <span class="font-semibold">Ending Balance:</span>
                                        <span class="font-semibold">${{ number_format(session('reconciliation_ending_balance') ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Cleared Balance:</span>
                                        <span class="font-medium" id="clearedBalance">$0.00</span>
                                    </div>
                                    <div class="flex justify-between border-t-2 border-gray-300 pt-2">
                                        <span class="font-bold">Difference:</span>
                                        <span class="font-bold text-red-600" id="difference">$0.00</span>
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Modify
                                    </button>
                                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                        Reconcile Now
                                    </button>
                                    <button type="button" onclick="endReconciliation()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Leave
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        @endif
    </div>
</div>

<!-- Begin Reconciliation Modal -->
<div id="beginReconciliationModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="hideBeginReconciliationModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Begin Reconciliation</h3>
                <button onclick="hideBeginReconciliationModal()" class="text-white hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('bank-reconciliation.begin') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_account_id" value="{{ $selectedAccountId }}">
                <div class="bg-white px-6 py-4">
                    <p class="text-sm text-gray-600 mb-6">Select an account to reconcile, and then enter the ending balance from your account statement.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account</label>
                            <select name="bank_account_display" disabled class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                                @foreach($bankAccounts as $account)
                                    @if($account->id == $selectedAccountId)
                                        <option value="{{ $account->id }}" selected>{{ $account->account_code }} - {{ $account->account_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statement Date</label>
                            <input type="date" name="statement_date" required value="{{ now()->toDateString() }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Beginning Balance</label>
                            <div class="flex items-center">
                                <input type="text" readonly value="$0.00" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                                <a href="#" class="ml-2 text-blue-600 hover:text-blue-800 text-sm">What if my beginning balance doesn't match my statement?</a>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ending Balance</label>
                            <input type="number" step="0.01" name="ending_balance" required placeholder="0.00" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Enter any service charge or interest earned.</h4>
                            
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Service Charge</label>
                                    <input type="number" step="0.01" name="service_charge" value="0.00" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" name="service_charge_date" value="{{ now()->toDateString() }}" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Account</label>
                                    <select name="service_charge_account_id" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                        <option value="">-- Select --</option>
                                        <!-- Add account options -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Interest Earned</label>
                                    <input type="number" step="0.01" name="interest_earned" value="0.00" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" name="interest_earned_date" value="{{ now()->toDateString() }}" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Account</label>
                                    <select name="interest_earned_account_id" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm">
                                        <option value="">-- Select --</option>
                                        <!-- Add account options -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Locate Discrepancies
                    </button>
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Undo Last Reconciliation
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Continue
                    </button>
                    <button type="button" onclick="hideBeginReconciliationModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Help
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Statement Modal -->
<div id="importModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="hideImportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <form action="{{ route('bank-reconciliation.store-transactions') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Import Bank Statement
                            </h3>
                            
                            @if(!$selectedAccountId)
                                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                                    Please select a bank account first.
                                </div>
                            @else
                                <input type="hidden" name="bank_account_id" value="{{ $selectedAccountId }}">
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Paste CSV data (Date, Type, Amount, Description, Reference)
                                    </label>
                                    <textarea name="csv_data" rows="10" 
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                              placeholder="2024-01-15, deposit, 1000.00, Customer Payment, INV001&#10;2024-01-16, withdrawal, 500.00, Supplier Payment, PO123"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Format: Date (YYYY-MM-DD), Type (deposit/withdrawal/fee/interest/other), Amount, Description, Reference
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if($selectedAccountId)
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Import
                        </button>
                    @endif
                    <button type="button" onclick="hideImportModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showBeginReconciliationModal() {
        document.getElementById('beginReconciliationModal').classList.remove('hidden');
    }

    function hideBeginReconciliationModal() {
        document.getElementById('beginReconciliationModal').classList.add('hidden');
    }

    function showImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }

    function hideImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    function toggleAllWithdrawals(checkbox) {
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateReconciliationSummary();
    }

    function toggleAllDeposits(checkbox) {
        const checkboxes = document.querySelectorAll('.deposit-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateReconciliationSummary();
    }

    function toggleAllPaidPayments(checkbox) {
        const checkboxes = document.querySelectorAll('.paid-payment-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateReconciliationSummary();
    }

    function toggleAllReceivedPayments(checkbox) {
        const checkboxes = document.querySelectorAll('.received-payment-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateReconciliationSummary();
    }

    function updateReconciliationSummary() {
        // Count and sum marked deposits
        const depositCheckboxes = document.querySelectorAll('.deposit-checkbox:checked, .received-payment-checkbox:checked');
        let depositsTotal = 0;
        depositCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            const amountCell = row.querySelector('td:nth-last-child(1)');
            if (amountCell) {
                const amount = parseFloat(amountCell.textContent.replace(/[^0-9.-]/g, ''));
                depositsTotal += amount;
            }
        });
        
        // Count and sum marked payments/withdrawals
        const paymentCheckboxes = document.querySelectorAll('.withdrawal-checkbox:checked, .paid-payment-checkbox:checked');
        let paymentsTotal = 0;
        paymentCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            const amountCell = row.querySelector('td:nth-last-child(1)');
            if (amountCell) {
                const amount = parseFloat(amountCell.textContent.replace(/[^0-9.-]/g, ''));
                paymentsTotal += amount;
            }
        });
        
        // Update display
        document.getElementById('markedDepositsCount').textContent = depositCheckboxes.length;
        document.getElementById('markedDepositsAmount').textContent = '$' + depositsTotal.toFixed(2);
        document.getElementById('markedPaymentsCount').textContent = paymentCheckboxes.length;
        document.getElementById('markedPaymentsAmount').textContent = '$' + paymentsTotal.toFixed(2);
        
        // Calculate cleared balance and difference
        const beginningBalance = {{ session('reconciliation_beginning_balance') ?? 0 }};
        const serviceCharge = {{ session('reconciliation_service_charge') ?? 0 }};
        const interestEarned = {{ session('reconciliation_interest_earned') ?? 0 }};
        const endingBalance = {{ session('reconciliation_ending_balance') ?? 0 }};
        
        const clearedBalance = beginningBalance + depositsTotal - paymentsTotal - serviceCharge + interestEarned;
        const difference = endingBalance - clearedBalance;
        
        document.getElementById('clearedBalance').textContent = '$' + clearedBalance.toFixed(2);
        const differenceEl = document.getElementById('difference');
        differenceEl.textContent = '$' + difference.toFixed(2);
        if (difference === 0) {
            differenceEl.classList.remove('text-red-600');
            differenceEl.classList.add('text-green-600');
        } else {
            differenceEl.classList.remove('text-green-600');
            differenceEl.classList.add('text-red-600');
        }
    }

    function endReconciliation() {
        if (confirm('Are you sure you want to end this reconciliation? All unmarked transactions will remain unreconciled.')) {
            window.location.href = '{{ route("bank-reconciliation.index") }}';
        }
    }

    // Initialize summary on page load only if reconciliation session is active
    @if(session('reconciliation_session'))
    document.addEventListener('DOMContentLoaded', function() {
        updateReconciliationSummary();
    });
    @endif
</script>
@endpush

@endsection

