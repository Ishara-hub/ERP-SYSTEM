@extends('layouts.modern')

@section('title', 'Suppliers')
@section('breadcrumb', 'Suppliers')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
                <p class="text-sm text-gray-500">Manage your supplier accounts and information</p>
            </div>
            <a href="{{ route('suppliers.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Supplier
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <form method="GET" action="{{ route('suppliers.web.index') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search suppliers..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <select name="company" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Companies</option>
                        <option value="has_company" {{ request('company') === 'has_company' ? 'selected' : '' }}>Has Company</option>
                        <option value="no_company" {{ request('company') === 'no_company' ? 'selected' : '' }}>No Company</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($suppliers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Company
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit Limit
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($suppliers as $supplier)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-6 w-6 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                                            <svg class="h-3 w-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                            @if($supplier->contact_person)
                                                <div class="text-xs text-gray-500">Contact: {{ $supplier->contact_person }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supplier->email }}</div>
                                    @if($supplier->phone)
                                        <div class="text-xs text-gray-500">{{ $supplier->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $supplier->company_name ?: '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $supplier->supplier_code }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    @if($supplier->credit_limit)
                                        {{ number_format($supplier->credit_limit, 2) }} {{ $supplier->currency ?: 'USD' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($supplier->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('suppliers.web.show', $supplier) }}" 
                                           class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('suppliers.web.edit', $supplier) }}" 
                                           class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('suppliers.web.toggle-status', $supplier) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                </svg>
                                                {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('suppliers.web.destroy', $supplier) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this supplier?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
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
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($suppliers->previousPageUrl())
                        <a href="{{ $suppliers->previousPageUrl() }}" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    @if($suppliers->nextPageUrl())
                        <a href="{{ $suppliers->nextPageUrl() }}" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $suppliers->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $suppliers->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $suppliers->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $suppliers->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No suppliers found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first supplier.</p>
                <div class="mt-6">
                    <a href="{{ route('suppliers.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add First Supplier
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection


