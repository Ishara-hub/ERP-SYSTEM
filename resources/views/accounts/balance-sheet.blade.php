@extends('layouts.modern')

@section('title', 'Balance Sheet')
@section('breadcrumb', 'Balance Sheet')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Balance Sheet</h2>
                <p class="text-sm text-gray-500">Assets, Liabilities, and Equity statement</p>
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
            <form method="GET" action="{{ route('accounts.reports.balance-sheet') }}" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-40">
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">As at Date</label>
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
                    <a href="{{ route('accounts.reports.balance-sheet') }}" class="btn-secondary text-sm px-3 py-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow-sm border mb-4 p-4 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Balance Sheet</h3>
        <p class="text-sm text-gray-600">
            As at {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </p>
        <div class="mt-2 text-xs text-blue-600 bg-blue-50 p-2 rounded">
            <strong>Note:</strong> Assets show debit balances, Liability & Equity show credit balances as positive amounts.
            Net Profit increases Equity (credit), Net Loss decreases Equity (debit).
        </div>
    </div>

    <!-- Balance Sheet Table -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        @if(count($groupedData) > 0)
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
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($groupedData as $type => $accounts)
                            <!-- Category Header -->
                            <tr class="bg-gray-100">
                                <td colspan="4" class="px-3 py-2 font-bold text-gray-900">
                                    <i class="fas fa-folder mr-2"></i>{{ $type }}
                                </td>
                            </tr>
                            
                            @foreach($accounts as $mainAccountName => $data)
                                <!-- Main Account -->
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2 font-bold text-gray-900">
                                        {{ $data['account_code'] }}
                                    </td>
                                    <td class="px-3 py-2 font-bold text-gray-900">
                                        {{ $mainAccountName }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs">
                                        @if(isset($data['balance']) && $data['balance'] != 0)
                                            ${{ number_format($data['balance'], 2) }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-bold">
                                        @if(isset($data['total_balance']) && $data['total_balance'] != 0)
                                            ${{ number_format($data['total_balance'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                                
                                @foreach($data['sub_accounts'] as $sub)
                                    <!-- Sub Account -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 pl-8 text-gray-700">
                                            {{ $sub['sub_account_code'] }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-900">
                                            {{ $sub['sub_account_name'] }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs">
                                            @if($sub['balance'] != 0)
                                                ${{ number_format($sub['balance'], 2) }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs">
                                            ${{ number_format($sub['balance'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No balance sheet data found</h3>
                <p class="mt-1 text-xs text-gray-500">No accounts available for balance sheet.</p>
            </div>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Category Totals -->
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="border-b border-gray-200 pb-2 mb-3">
                <h4 class="text-sm font-semibold text-gray-900">
                    <i class="fas fa-calculator mr-2"></i>Category Totals
                </h4>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-600 mb-1">Total Assets</div>
                    <div class="text-lg font-bold text-green-700">${{ number_format($categoryTotals['Assets'], 2) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">Total Liability</div>
                    <div class="text-lg font-bold text-red-700">${{ number_format($categoryTotals['Liability'], 2) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">Total Equity</div>
                    <div class="text-lg font-bold text-blue-700">${{ number_format($categoryTotals['Equity'], 2) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-1">Net Profit/(Loss)</div>
                    <div class="text-lg font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        ${{ number_format($netProfit, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Equation -->
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="border-b border-gray-200 pb-2 mb-3">
                <h4 class="text-sm font-semibold text-gray-900">
                    <i class="fas fa-balance-scale mr-2"></i>Balance Sheet Equation
                </h4>
            </div>
            <div class="space-y-2">
                <div class="text-sm text-gray-700">
                    <strong>Assets = Liability + Equity</strong>
                </div>
                <div class="text-sm">
                    <strong class="text-green-700">${{ number_format($categoryTotals['Assets'], 2) }}</strong> = 
                    <strong class="text-red-700">${{ number_format($categoryTotals['Liability'], 2) }}</strong> + 
                    <strong class="text-blue-700">${{ number_format($categoryTotals['Equity'], 2) }}</strong>
                </div>
                @if(abs($balanceSheetEquation) < 0.01)
                    <div class="mt-3 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-700">
                        <i class="fas fa-check-circle mr-1"></i>Balance Sheet is Balanced ✓
                    </div>
                @else
                    <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Balance Sheet Difference: ${{ number_format($balanceSheetEquation, 2) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- P&L Summary -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="border-b border-gray-200 pb-2 mb-3">
            <h4 class="text-sm font-semibold text-gray-900">
                <i class="fas fa-chart-line mr-2"></i>Profit & Loss Summary
            </h4>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <div class="text-xs text-gray-600 mb-1">Total Income</div>
                <div class="text-lg font-bold text-green-700">${{ number_format($totalIncome, 2) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-600 mb-1">Total Expenses</div>
                <div class="text-lg font-bold text-red-700">${{ number_format($totalExpenses, 2) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-600 mb-1">Net Profit/(Loss)</div>
                <div class="text-lg font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format($netProfit, 2) }}
                </div>
            </div>
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

