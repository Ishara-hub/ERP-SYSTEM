@extends('layouts.modern')

@section('title', 'Invoice Summary Report')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center"><a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Dashboard</a></li>
                <li><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a></div></li>
                <li aria-current="page"><div class="flex items-center"><svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg><span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Invoice Summary</span></div></li>
            </ol>
        </nav>

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div><h1 class="text-2xl font-bold text-gray-900">Invoice Summary Report</h1><p class="mt-1 text-sm text-gray-600">Invoice status and totals</p></div>
                <div class="flex space-x-3">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>Back to Reports</a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg mb-6">
            <form method="GET" action="{{ route('reports.invoice-summary') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">From Date</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">To Date</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select></div>
                    <div class="flex items-end"><button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Apply Filters</button></div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center"><div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Invoices</p><p class="text-2xl font-semibold text-gray-900">{{ $summary['total_invoices'] }}</p></div></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center"><div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Amount</p><p class="text-2xl font-semibold text-gray-900">${{ number_format($summary['total_amount'], 2) }}</p></div></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center"><div class="flex-shrink-0 bg-green-100 rounded-md p-3"><svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Paid</p><p class="text-2xl font-semibold text-green-600">${{ number_format($summary['total_paid'], 2) }}</p></div></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center"><div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Due</p><p class="text-2xl font-semibold text-yellow-600">${{ number_format($summary['total_due'], 2) }}</p></div></div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Due</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-gray-900">{{ $invoice->invoice_no }}</div></td>
                                <td class="px-6 py-4"><div class="text-sm text-gray-900">{{ $invoice->customer->name ?? 'N/A' }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">{{ $invoice->date->format('M d, Y') }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $invoice->status == 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right"><div class="text-sm font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right"><div class="text-sm text-green-600">${{ number_format($invoice->payments_applied, 2) }}</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right"><div class="text-sm font-medium {{ $invoice->balance_due > 0 ? 'text-yellow-600' : 'text-gray-900' }}">${{ number_format($invoice->balance_due, 2) }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No invoices found for the selected criteria.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr><td colspan="4" class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">Totals:</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">${{ number_format($summary['total_amount'], 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-green-600">${{ number_format($summary['total_paid'], 2) }}</td><td class="px-6 py-4 whitespace-nowrap text-right font-bold text-yellow-600">${{ number_format($summary['total_due'], 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
