@extends('layouts.modern')

@section('title', 'Invoices')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Invoices</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your invoices and billing</p>
                </div>
                <div>
                    <a href="{{ route('invoices.web.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Invoice
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('invoices.web.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search invoices..." 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('invoices.web.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            @if($invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-32 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                <th class="w-1/4 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="w-24 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="w-24 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="w-24 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="w-20 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="w-32 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_no }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm text-gray-900">{{ $invoice->customer->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $invoice->customer->email }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-center text-sm text-gray-900">
                                        {{ $invoice->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-sm text-gray-900">
                                        {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-sm font-medium text-gray-900">
                                        ${{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'sent' => 'bg-blue-100 text-blue-800',
                                                'paid' => 'bg-green-100 text-green-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                'partial' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $statusColor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <div class="flex justify-center space-x-1">
                                            <a href="{{ route('invoices.web.show', $invoice) }}" 
                                               class="text-blue-600 hover:text-blue-900 p-1 rounded" title="View">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('invoices.web.edit', $invoice) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 p-1 rounded" title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('invoices.web.print', $invoice) }}" 
                                               class="text-gray-600 hover:text-gray-900 p-1 rounded" title="Print" target="_blank">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                            </a>
                                            @if($invoice->status !== 'paid')
                                                <form method="POST" action="{{ route('invoices.web.mark-paid', $invoice) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-green-600 hover:text-green-900 p-1 rounded" 
                                                            title="Mark as Paid"
                                                            onclick="return confirm('Are you sure you want to mark this invoice as paid?')">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('invoices.web.destroy', $invoice) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 p-1 rounded" 
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $invoices->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
                    <div class="mt-6">
                        <a href="{{ route('invoices.web.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Invoice
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // You can add SweetAlert2 or other notification library here
            alert('{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('{{ session('error') }}');
        });
    </script>
@endif
@endsection