@extends('layouts.modern')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Quotations
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage your quotations and track their status
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('quotations.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Quotation
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 bg-white shadow rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div>
                    <label for="customer-filter" class="block text-sm font-medium text-gray-700">Customer</label>
                    <input type="text" id="customer-filter" placeholder="Search by customer..." 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="date-from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" id="date-from" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="date-to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" id="date-to" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
        </div>

        <!-- Quotations Table -->
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:p-6">
                @if($quotations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quotation #
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quotation Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valid Until
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Amount
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($quotations as $quotation)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $quotation->quotation_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $quotation->customer->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $quotation->quotation_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                @if($quotation->status === 'draft') bg-gray-100 text-gray-800
                                                @elseif($quotation->status === 'sent') bg-blue-100 text-blue-800
                                                @elseif($quotation->status === 'accepted') bg-green-100 text-green-800
                                                @elseif($quotation->status === 'rejected') bg-red-100 text-red-800
                                                @elseif($quotation->status === 'expired') bg-yellow-100 text-yellow-800
                                                @endif">
                                                {{ ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($quotation->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a href="{{ route('quotations.show', $quotation) }}" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                                <a href="{{ route('quotations.edit', $quotation) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </a>
                                                <a href="{{ route('quotations.print', $quotation) }}" 
                                                   class="text-green-600 hover:text-green-900">
                                                    Print
                                                </a>
                                                @if($quotation->status === 'draft')
                                                    <form action="{{ route('quotations.send', $quotation) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-purple-600 hover:text-purple-900">
                                                            Send
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('quotations.destroy', $quotation) }}" 
                                                      method="POST" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this quotation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
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
                    <div class="mt-6">
                        {{ $quotations->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No quotations</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new quotation.</p>
                        <div class="mt-6">
                            <a href="{{ route('quotations.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                New Quotation
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
