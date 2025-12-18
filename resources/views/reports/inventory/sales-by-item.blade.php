@extends('layouts.modern')

@section('title', 'Sales by Item')

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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Sales by Item</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sales by Item Detail</h1>
                <p class="mt-1 text-sm text-gray-600">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
            </div>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg mb-6 no-print">
            <div class="p-6">
                <form method="GET" action="{{ route('reports.inventory.sales-by-item') }}" class="flex items-end space-x-4">
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-2xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-3 py-3 text-left">Item</th>
                                <th class="px-3 py-3 text-left">Invoice No</th>
                                <th class="px-3 py-3 text-left">Date</th>
                                <th class="px-3 py-3 text-left">Customer</th>
                                <th class="px-3 py-3 text-center">Qty Sold</th>
                                <th class="px-3 py-3 text-right">Unit Price</th>
                                <th class="px-3 py-3 text-right">Sales Amount</th>
                                <th class="px-3 py-3 text-right text-red-600">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php 
                                $totalSales = 0; 
                                $totalProfit = 0;
                            @endphp
                            @foreach($sales as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap font-medium text-gray-900">{{ $row['item_name'] }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-gray-700">#{{ $row['invoice_no'] }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-gray-700">{{ $row['date']->format('Y-m-d') }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-gray-700">{{ $row['customer'] }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">{{ number_format($row['qty_sold'], 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-gray-900">Rs.{{ number_format($row['unit_price'], 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-gray-900 font-semibold">Rs.{{ number_format($row['sales_amount'], 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right font-bold {{ $row['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rs.{{ number_format($row['gross_profit'], 2) }}
                                    </td>
                                </tr>
                                @php 
                                    $totalSales += $row['sales_amount'];
                                    $totalProfit += $row['gross_profit'];
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold border-t-2 border-gray-300 uppercase tracking-wider">
                                <td colspan="6" class="px-3 py-4 text-sm text-gray-900">Total</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rs.{{ number_format($totalSales, 2) }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-right text-green-700">Rs.{{ number_format($totalProfit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

