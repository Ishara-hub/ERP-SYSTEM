@extends('layouts.modern')

@section('title', 'Item Profitability Report')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center"><a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Dashboard</a></li>
                <li><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a></div></li>
                <li aria-current="page"><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Item Profitability</span></div></li>
            </ol>
        </nav>

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div><h1 class="text-2xl font-bold text-gray-900">Item Profitability Report</h1><p class="mt-1 text-sm text-gray-600">Profit margins by product</p></div>
                <div class="flex space-x-3">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>Back to Reports</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6"><div class="flex items-center"><div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Items</p><p class="text-2xl font-semibold text-gray-900">{{ $itemProfits->count() }}</p></div></div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="flex items-center"><div class="flex-shrink-0 bg-green-100 rounded-md p-3"><svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Revenue</p><p class="text-2xl font-semibold text-gray-900">${{ number_format($itemProfits->sum('total_sales_revenue'), 2) }}</p></div></div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="flex items-center"><div class="flex-shrink-0 bg-red-100 rounded-md p-3"><svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Cost</p><p class="text-2xl font-semibold text-red-600">${{ number_format($itemProfits->sum('total_purchase_cost'), 2) }}</p></div></div></div>
            <div class="bg-white rounded-lg shadow p-6"><div class="flex items-center"><div class="flex-shrink-0 bg-green-100 rounded-md p-3"><svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Profit</p><p class="text-2xl font-semibold text-green-600">${{ number_format($itemProfits->sum('total_profit'), 2) }}</p></div></div></div>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin %</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($itemProfits as $profit)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-gray-900">{{ $profit['item']->item_name }}</div><div class="text-sm text-gray-500">{{ $profit['item']->item_number }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ number_format($profit['total_quantity_sold'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($profit['total_sales_revenue'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">${{ number_format($profit['total_purchase_cost'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $profit['total_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($profit['total_profit'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $profit['profit_margin_percent'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($profit['profit_margin_percent'], 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No profitability data found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr><td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">Totals:</td><td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-900">{{ number_format($itemProfits->sum('total_quantity_sold'), 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">${{ number_format($itemProfits->sum('total_sales_revenue'), 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-red-600">${{ number_format($itemProfits->sum('total_purchase_cost'), 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-green-600">${{ number_format($itemProfits->sum('total_profit'), 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-500">-</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
