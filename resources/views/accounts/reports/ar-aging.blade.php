@extends('layouts.modern')

@section('title', 'A/R Aging Summary')

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
                        <a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">A/R Aging</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Accounts Receivable Aging Summary</h1>
                <p class="mt-1 text-sm text-gray-600">As of {{ \Carbon\Carbon::parse($dateAsOf)->format('M d, Y') }}</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg mb-6 no-print">
            <div class="p-6">
                <form method="GET" action="{{ route('accounts.reports.ar-aging') }}" class="flex items-end space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">As of Date</label>
                        <input type="date" name="as_of" value="{{ $dateAsOf }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-gray-900 uppercase tracking-wider">{{ config('app.name', 'ERP System') }}</h2>
                    <h3 class="text-lg font-semibold text-gray-700 uppercase tracking-tight">A/R Aging Summary</h3>
                    <p class="text-sm text-gray-500">As of {{ \Carbon\Carbon::parse($dateAsOf)->format('F d, Y') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Current</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">1 - 30</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">31 - 60</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">61 - 90</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">91 and Over</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($agingData as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['customer_name'] }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ $row['current'] > 0 ? number_format($row['current'], 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ $row['1_30'] > 0 ? number_format($row['1_30'], 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ $row['31_60'] > 0 ? number_format($row['31_60'], 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ $row['61_90'] > 0 ? number_format($row['61_90'], 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ $row['over90'] > 0 ? number_format($row['over90'], 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                                        {{ number_format($row['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                                <td class="px-4 py-4 text-sm text-gray-900 uppercase tracking-wider">Total</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['current'], 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['1_30'], 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['31_60'], 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['61_90'], 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['over90'], 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($totals['total'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background-color: white !important;
    }
    .shadow-lg {
        box-shadow: none !important;
    }
}
</style>
@endsection

