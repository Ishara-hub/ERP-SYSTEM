@extends('layouts.modern')

@section('title', 'Statement of Cash Flows')

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
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Cash Flow</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Statement of Cash Flows</h1>
                <p class="mt-1 text-sm text-gray-600">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
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
                <form method="GET" action="{{ route('accounts.reports.cash-flow') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" 
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
            <div class="px-8 py-10">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-widest">{{ config('app.name', 'ERP System') }}</h2>
                    <h3 class="text-xl font-semibold text-gray-700 uppercase tracking-wide mt-1">Statement of Cash Flows</h3>
                    <p class="text-md text-gray-500 mt-2 italic">For the period ended {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
                </div>

                <div class="space-y-8">
                    <!-- Operating Activities -->
                    <div>
                        <h4 class="text-md font-bold text-gray-900 border-b-2 border-gray-100 pb-2 uppercase tracking-tight">Cash flows from operating activities</h4>
                        <table class="min-w-full mt-4">
                            <tbody class="divide-y divide-gray-50">
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">Net Income</td>
                                    <td class="py-2 text-sm text-right text-gray-900 font-medium">{{ number_format($netIncome, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-600 pl-4 italic">Adjustments to reconcile net income to net cash:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700 pl-8">Depreciation and amortization</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($depreciation, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700 pl-8">(Increase) Decrease in Accounts Receivable</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($changeInAR, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700 pl-8">(Increase) Decrease in Inventory</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($changeInInventory, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700 pl-8">Increase (Decrease) in Accounts Payable</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($changeInAP, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="py-3 text-sm text-gray-900 uppercase">Net cash provided by operating activities</td>
                                    <td class="py-3 text-sm text-right text-gray-900 border-t border-gray-400">{{ number_format($netCashOperating, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Investing Activities -->
                    <div>
                        <h4 class="text-md font-bold text-gray-900 border-b-2 border-gray-100 pb-2 uppercase tracking-tight">Cash flows from investing activities</h4>
                        <table class="min-w-full mt-4">
                            <tbody class="divide-y divide-gray-50">
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">Purchase/Sale of property and equipment</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($netCashInvesting, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="py-3 text-sm text-gray-900 uppercase">Net cash provided by investing activities</td>
                                    <td class="py-3 text-sm text-right text-gray-900 border-t border-gray-400">{{ number_format($netCashInvesting, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Financing Activities -->
                    <div>
                        <h4 class="text-md font-bold text-gray-900 border-b-2 border-gray-100 pb-2 uppercase tracking-tight">Cash flows from financing activities</h4>
                        <table class="min-w-full mt-4">
                            <tbody class="divide-y divide-gray-50">
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">Proceeds from loans/equity</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($netCashFinancing, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="py-3 text-sm text-gray-900 uppercase">Net cash provided by financing activities</td>
                                    <td class="py-3 text-sm text-right text-gray-900 border-t border-gray-400">{{ number_format($netCashFinancing, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="mt-10 pt-6 border-t-2 border-gray-900">
                        <table class="min-w-full">
                            <tbody class="space-y-2">
                                <tr class="font-bold">
                                    <td class="py-2 text-md text-gray-900 uppercase">Net increase (decrease) in cash</td>
                                    <td class="py-2 text-md text-right text-gray-900">{{ number_format($netChangeInCash, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700 uppercase">Cash balance at beginning of period</td>
                                    <td class="py-2 text-sm text-right text-gray-900">{{ number_format($beginningCash, 2) }}</td>
                                </tr>
                                <tr class="bg-blue-50 font-black border-b-4 border-double border-gray-900">
                                    <td class="py-4 text-lg text-blue-900 uppercase">Cash balance at end of period</td>
                                    <td class="py-4 text-lg text-right text-blue-900">{{ number_format($endingCash, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    .max-w-7xl {
        max-width: 100% !important;
    }
}
</style>
@endsection

