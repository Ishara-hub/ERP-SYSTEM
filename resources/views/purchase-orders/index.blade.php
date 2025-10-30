@extends('layouts.modern')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Purchase Orders')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Purchase Orders</h1>
                <p class="text-xs text-gray-500">Manage purchase orders and inventory receiving</p>
            </div>
            <a href="{{ route('purchase-orders.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create PO
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <form method="GET" action="{{ route('purchase-orders.web.index') }}" class="p-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search PO, supplier..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search
                </button>
            </div>  
        </form>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($purchaseOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-5 w-5 rounded-full bg-orange-100 flex items-center justify-center mr-2">
                                            <svg class="h-2.5 w-2.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-gray-900">{{ $po->po_number }}</div>
                                            @if($po->reference)
                                                <div class="text-xs text-gray-500">{{ $po->reference }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <div class="text-xs text-gray-900">{{ $po->supplier->name }}</div>
                                    @if($po->supplier->company_name)
                                        <div class="text-xs text-gray-500">{{ $po->supplier->company_name }}</div>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">
                                    {{ $po->order_date->format('M d, Y') }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">
                                    ${{ number_format($po->total_amount, 2) }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-{{ $po->status_color }}-100 text-{{ $po->status_color }}-800">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-12 bg-gray-200 rounded-full h-1.5 mr-2">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $po->progress_percentage }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $po->progress_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('purchase-orders.web.show', $po) }}" 
                                           class="inline-flex items-center px-1.5 py-0.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        @if($po->status === 'draft')
                                            <a href="{{ route('purchase-orders.web.edit', $po) }}" 
                                               class="inline-flex items-center px-1.5 py-0.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if(in_array($po->status, ['sent', 'confirmed', 'partial']))
                                            <a href="{{ route('purchase-orders.web.show', $po) }}#receive" 
                                               class="inline-flex items-center px-1.5 py-0.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($po->status === 'draft')
                                            <form method="POST" action="{{ route('purchase-orders.web.destroy', $po) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this purchase order?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-1.5 py-0.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
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
                    @if($purchaseOrders->previousPageUrl())
                        <a href="{{ $purchaseOrders->previousPageUrl() }}" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    @if($purchaseOrders->nextPageUrl())
                        <a href="{{ $purchaseOrders->nextPageUrl() }}" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $purchaseOrders->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $purchaseOrders->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $purchaseOrders->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $purchaseOrders->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first purchase order.</p>
                <div class="mt-6">
                    <a href="{{ route('purchase-orders.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create First Purchase Order
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
