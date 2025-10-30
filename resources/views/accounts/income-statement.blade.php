@extends('layouts.modern')

@section('title', 'Income Statement')
@section('breadcrumb', 'Income Statement')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Income Statement</h2>
                <p class="text-sm text-gray-500">Revenue and expenses report</p>
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
            <form method="GET" action="{{ route('accounts.reports.income-statement') }}" class="flex flex-wrap gap-3 items-end">
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
                    <a href="{{ route('accounts.reports.income-statement') }}" class="btn-secondary text-sm px-3 py-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow-sm border mb-4 p-4 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Income Statement</h3>
        <p class="text-sm text-gray-600">
            For the period {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </p>
    </div>

    @php
        $revenueTotal = collect($revenueData)->sum(function($account) {
            return $account['amount'] + collect($account['sub_accounts'])->sum('amount');
        });
        $expensesTotal = collect($expensesData)->sum(function($account) {
            return $account['amount'] + collect($account['sub_accounts'])->sum('amount');
        });
        $netIncome = $revenueTotal - $expensesTotal;
    @endphp

    <!-- Revenue Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900">
                <i class="fas fa-arrow-up mr-2 text-green-600"></i>Revenue
            </h4>
        </div>
        @if(count($revenueData) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                A/C Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Description
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($revenueData as $account)
                            @if($account['amount'] > 0 || !empty($account['sub_accounts']))
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2 font-medium text-gray-900">
                                        {{ $account['account_code'] }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-900">
                                        {{ $account['account_name'] }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-medium text-green-700">
                                        @if($account['amount'] > 0)
                                            ${{ number_format($account['amount'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            
                            @foreach($account['sub_accounts'] as $sub)
                                @if($sub['amount'] > 0)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 pl-8 text-gray-700">
                                            {{ $sub['sub_account_code'] }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-900">
                                            {{ $sub['sub_account_name'] }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-green-700">
                                            ${{ number_format($sub['amount'], 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                        
                        <!-- Revenue Total -->
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="2" class="px-3 py-2 text-right text-gray-900">
                                Total Revenue
                            </td>
                            <td class="px-3 py-2 text-right text-xs text-green-700">
                                ${{ number_format($revenueTotal, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-sm">
                No revenue transactions found
            </div>
        @endif
    </div>

    <!-- Expenses Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900">
                <i class="fas fa-arrow-down mr-2 text-red-600"></i>Expenses
            </h4>
        </div>
        @if(count($expensesData) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                A/C Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Description
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($expensesData as $account)
                            @if($account['amount'] > 0 || !empty($account['sub_accounts']))
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2 font-medium text-gray-900">
                                        {{ $account['account_code'] }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-900">
                                        {{ $account['account_name'] }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-medium text-red-700">
                                        @if($account['amount'] > 0)
                                            ${{ number_format($account['amount'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            
                            @foreach($account['sub_accounts'] as $sub)
                                @if($sub['amount'] > 0)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 pl-8 text-gray-700">
                                            {{ $sub['sub_account_code'] }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-900">
                                            {{ $sub['sub_account_name'] }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs text-red-700">
                                            ${{ number_format($sub['amount'], 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                        
                        <!-- Expenses Total -->
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="2" class="px-3 py-2 text-right text-gray-900">
                                Total Expenses
                            </td>
                            <td class="px-3 py-2 text-right text-xs text-red-700">
                                ${{ number_format($expensesTotal, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-sm">
                No expense transactions found
            </div>
        @endif
    </div>

    <!-- Net Income Section -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <tbody>
                    <tr class="{{ $netIncome >= 0 ? 'bg-green-50' : 'bg-red-50' }} font-bold">
                        <td colspan="2" class="px-3 py-3 text-right text-gray-900">
                            {{ $netIncome >= 0 ? 'Net Income' : 'Net Loss' }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs {{ $netIncome >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            ${{ number_format(abs($netIncome), 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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

