@extends('layouts.modern')

@section('title', 'Account Transaction Details')
@section('breadcrumb', 'Account Transaction Details')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Account Transaction Details</h2>
                <p class="text-sm text-gray-500">Detailed transaction listing for account</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('accounts.reports.chart-of-accounts-data', [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]) }}" 
                   class="btn-outline text-sm px-3 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Chart
                </a>
                <button onclick="window.print()" class="btn-outline text-sm px-3 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Compact Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        <div class="p-4">
            <form method="GET" action="{{ route('accounts.reports.sub-account-details', $account->id) }}" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-40">
                    <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from"
                           value="{{ $dateFrom }}"
                           class="form-control text-sm py-2">
                </div>
                
                <div class="min-w-40">
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to"
                           value="{{ $dateTo }}"
                           class="form-control text-sm py-2">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('accounts.reports.sub-account-details', $account->id) }}" class="btn-secondary text-sm px-3 py-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-xs font-medium text-gray-600 mb-1">Account:</div>
                <div class="text-sm font-semibold text-gray-900">
                    {{ $account->account_code }} - {{ $account->account_name }}
                </div>
            </div>
            @if($account->parent)
            <div>
                <div class="text-xs font-medium text-gray-600 mb-1">Parent Account:</div>
                <div class="text-sm text-gray-900">
                    {{ $account->parent->account_code }} - {{ $account->parent->account_name }}
                </div>
            </div>
            @endif
            <div>
                <div class="text-xs font-medium text-gray-600 mb-1">Account Type:</div>
                <div class="text-sm text-gray-900">{{ $account->account_type }}</div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-600 mb-1">Period:</div>
                <div class="text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                </div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-600 mb-1">Net Balance:</div>
                <div class="text-sm font-semibold {{ $netBalance >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format(abs($netBalance), 2) }}
                    <span class="text-xs">({{ $netBalance >= 0 ? 'Debit' : 'Credit' }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900">Transactions</h3>
            <span class="text-xs text-gray-500">{{ count($transactions) }} entries</span>
        </div>
        
        @if(count($transactions) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Counter Account
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs text-gray-900">{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs text-gray-900">{{ Str::limit($transaction['description'], 50) }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-mono text-gray-600">{{ $transaction['reference'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs text-gray-600">{{ Str::limit($transaction['counter_account'], 40) }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    @if($transaction['debit'] > 0)
                                        <div class="text-xs font-medium text-green-700">${{ number_format($transaction['debit'], 2) }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    @if($transaction['credit'] > 0)
                                        <div class="text-xs font-medium text-red-700">${{ number_format($transaction['credit'], 2) }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    <div class="text-xs font-medium text-gray-900">${{ number_format($transaction['balance'], 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-right text-xs font-semibold text-gray-900">
                                Totals:
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-green-700">
                                ${{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-red-700">
                                ${{ number_format($totalCredit, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-gray-900">
                                ${{ number_format($netBalance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions found</h3>
                <p class="mt-1 text-xs text-gray-500">No transactions found for the selected period.</p>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
    }
</style>
@endpush
@endsection
