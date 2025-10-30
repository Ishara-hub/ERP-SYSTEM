@extends('layouts.modern')

@section('title', 'Chart of Accounts Data')
@section('breadcrumb', 'Chart of Accounts Data')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Chart of Accounts Data</h2>
                <p class="text-sm text-gray-500">Hierarchical view of all accounts with transaction totals</p>
            </div>
            <div class="flex gap-2">
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
            <form method="GET" action="{{ route('accounts.reports.chart-of-accounts-data') }}" class="flex flex-wrap gap-3 items-end">
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
                    <a href="{{ route('accounts.reports.chart-of-accounts-data') }}" class="btn-secondary text-sm px-3 py-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow-sm border mb-4 p-4 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Chart of Accounts Data</h3>
        <p class="text-sm text-gray-600">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </p>
    </div>

    <!-- Chart of Accounts Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        @if(count($groupedData) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Description
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
                        @foreach($groupedData as $categoryId => $category)
                            <!-- Category Header -->
                            <tr class="bg-gray-100">
                                <td colspan="5" class="px-3 py-2 font-bold text-gray-900">
                                    <i class="fas fa-folder mr-2"></i>{{ $category['category_name'] }}
                                </td>
                            </tr>
                            
                            @foreach($category['accounts'] as $accountId => $account)
                                @if(empty($account['sub_accounts']) || !$account['has_sub_accounts'])
                                    <!-- Main Account without sub-accounts -->
                                    <tr class="bg-blue-50 hover:bg-blue-100">
                                        <td class="px-3 py-2 font-medium text-gray-900">
                                            {{ $account['account_code'] }}
                                        </td>
                                        <td class="px-3 py-2 font-medium text-gray-900">
                                            {{ $account['account_name'] }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs font-medium text-green-700">
                                            ${{ number_format($account['debit'], 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs font-medium text-red-700">
                                            ${{ number_format($account['credit'], 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs font-medium text-gray-900">
                                            ${{ number_format($account['balance'], 2) }}
                                        </td>
                                    </tr>
                                @else
                                    <!-- Main Account with sub-accounts -->
                                    <tr class="bg-blue-50">
                                        <td class="px-3 py-2 font-bold text-gray-900">
                                            {{ $account['account_code'] }}
                                        </td>
                                        <td class="px-3 py-2 font-bold text-gray-900">
                                            {{ $account['account_name'] }}
                                        </td>
                                        <td colspan="3" class="px-3 py-2"></td>
                                    </tr>
                                    
                                    @foreach($account['sub_accounts'] as $subAccount)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 pl-8 text-gray-700">
                                                {{ $subAccount['sub_account_code'] }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-900">
                                                <a href="{{ route('accounts.reports.sub-account-details', [
                                                    'account' => $subAccount['sub_account_id'],
                                                    'date_from' => $dateFrom,
                                                    'date_to' => $dateTo
                                                ]) }}" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $subAccount['sub_account_name'] }}
                                                </a>
                                            </td>
                                            <td class="px-3 py-2 text-right text-xs text-green-700">
                                                ${{ number_format($subAccount['debit'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-xs text-red-700">
                                                ${{ number_format($subAccount['credit'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-xs text-gray-900">
                                                ${{ number_format($subAccount['balance'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    <!-- Account Total Row -->
                                    <tr class="bg-gray-50 font-semibold">
                                        <td colspan="2" class="px-3 py-2 text-right text-gray-900">
                                            {{ $account['account_name'] }} Total:
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-green-700">
                                            ${{ number_format($account['total_debit'], 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-red-700">
                                            ${{ number_format($account['total_credit'], 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-gray-900">
                                            ${{ number_format($account['total_balance'], 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            
                            <!-- Category Total Row -->
                            <tr class="bg-gray-200 font-bold">
                                <td colspan="2" class="px-3 py-2 text-right text-gray-900">
                                    {{ $category['category_name'] }} Total:
                                </td>
                                <td class="px-3 py-2 text-right text-xs text-green-700">
                                    ${{ number_format($category['total_debit'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs text-red-700">
                                    ${{ number_format($category['total_credit'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs text-gray-900">
                                    ${{ number_format($category['total_balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No accounts found</h3>
                <p class="mt-1 text-xs text-gray-500">No active accounts available for the selected period.</p>
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
