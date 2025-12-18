@extends('layouts.modern')

@section('title', 'Inventory Valuation Detail')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Inventory Valuation Detail</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventory Valuation Detail</h1>
                <p class="mt-1 text-sm text-gray-600">Transactions from {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
            </div>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg mb-6 no-print">
            <div class="p-6">
                <form method="GET" action="{{ route('reports.inventory.valuation-detail') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                        <select name="item_id" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Items</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>{{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
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
                    <h3 class="text-lg font-semibold text-gray-700 uppercase">Inventory Valuation Detail</h3>
                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50 text-2xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-3 py-3 text-left">Date</th>
                                <th class="px-3 py-3 text-left">Type</th>
                                <th class="px-3 py-3 text-left">Ref No</th>
                                <th class="px-3 py-3 text-left">Item</th>
                                <th class="px-3 py-3 text-center">In</th>
                                <th class="px-3 py-3 text-center">Out</th>
                                <th class="px-3 py-3 text-right">Rate</th>
                                <th class="px-3 py-3 text-right">Amount</th>
                                <th class="px-3 py-3 text-center">Running Qty</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php 
                                $runningQty = 0; 
                                $runningValue = 0;
                            @endphp
                            @foreach($movements as $m)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-gray-900">{{ $m->transaction_date->format('Y-m-d') }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-gray-700">{{ ucfirst($m->type) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-gray-700">#{{ $m->source_document_id }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-gray-900">{{ optional($m->item)->item_name }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-center text-green-600">{{ $m->quantity > 0 ? number_format($m->quantity, 2) : '-' }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-center text-red-600">{{ $m->quantity < 0 ? number_format(abs($m->quantity), 2) : '-' }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-right text-gray-900">Rs.{{ number_format(optional($m->item)->cost, 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-right text-gray-900">Rs.{{ number_format(abs($m->quantity * optional($m->item)->cost), 2) }}</td>
                                    @php $runningQty += $m->quantity; @endphp
                                    <td class="px-3 py-4 whitespace-nowrap text-xs text-center font-bold text-gray-900">{{ number_format($runningQty, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: white !important; }
    .shadow-lg { box-shadow: none !important; }
}
</style>
@endsection

