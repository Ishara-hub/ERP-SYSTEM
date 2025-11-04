@extends('layouts.modern')

@section('title', 'Sales Trend Report')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center"><a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Dashboard</a></li>
                <li><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a></div></li>
                <li aria-current="page"><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Sales Trend</span></div></li>
            </ol>
        </nav>

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div><h1 class="text-2xl font-bold text-gray-900">Sales Trend Report</h1><p class="mt-1 text-sm text-gray-600">Monthly sales performance</p></div>
                <div class="flex space-x-3">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>Back to Reports</a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg mb-6">
            <form method="GET" action="{{ route('reports.sales-trend') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">From Date</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">To Date</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                    <div class="flex items-end"><button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Apply Filters</button></div>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paid</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($monthlyData as $month)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-gray-900">{{ $month['month_label'] }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $month['invoice_count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($month['total_sales'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">${{ number_format($month['total_paid'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No sales data found for the selected criteria.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr><td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">Totals:</td><td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-900">{{ $monthlyData->sum('invoice_count') }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">${{ number_format($monthlyData->sum('total_sales'), 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-green-600">${{ number_format($monthlyData->sum('total_paid'), 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
