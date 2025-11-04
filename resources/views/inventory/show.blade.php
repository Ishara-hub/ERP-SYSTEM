@extends('layouts.modern')

@section('title', $item->item_name . ' - Inventory Details')

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
                        <a href="{{ route('inventory.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Inventory
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $item->item_name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $item->item_name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Inventory details and movements</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.adjustment-form') }}?item_id={{ $item->id }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Adjust
                    </a>
                    <a href="{{ route('items.web.edit', $item) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Item
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">On Hand</p>
                <p class="mt-2 text-3xl font-semibold {{ $item->on_hand > 0 ? 'text-gray-900' : 'text-red-600' }}">
                    {{ number_format($item->on_hand, 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">{{ $item->unit_of_measure ?? 'units' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Unit Cost</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">${{ number_format($item->cost, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Total Value</p>
                <p class="mt-2 text-3xl font-semibold text-green-600">${{ number_format($item->total_value, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Reorder Point</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($item->reorder_point ?? 0, 2) }}</p>
                @if($item->on_hand <= ($item->reorder_point ?? 0) && $item->on_hand > 0)
                    <p class="mt-1 text-xs text-yellow-600 font-medium">Low Stock</p>
                @elseif($item->on_hand <= 0)
                    <p class="mt-1 text-xs text-red-600 font-medium">Out of Stock</p>
                @else
                    <p class="mt-1 text-xs text-green-600 font-medium">In Stock</p>
                @endif
            </div>
        </div>

        <!-- Item Details -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Item Information</h2>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Item Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->item_number ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Item Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $item->item_type)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Unit of Measure</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->unit_of_measure ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sales Price</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($item->sales_price, 2) }}</dd>
                    </div>
                    @if($item->cogsAccount)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">COGS Account</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->cogsAccount->account_name }}</dd>
                    </div>
                    @endif
                    @if($item->assetAccount)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Asset Account</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->assetAccount->account_name }}</dd>
                    </div>
                    @endif
                    @if($item->preferredVendor)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Preferred Vendor</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->preferredVendor->name }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->updated_at->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Recent Movements -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Recent Movements</h2>
            </div>
            <div class="overflow-x-auto">
                @if($movements->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Source
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $movement->transaction_date ? $movement->transaction_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movement->type === 'purchase')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Purchase
                                            </span>
                                        @elseif($movement->type === 'sale')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Sale
                                            </span>
                                        @elseif($movement->type === 'adjustment')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Adjustment
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($movement->type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        @if($movement->quantity > 0)
                                            <span class="text-green-600 font-medium">+{{ number_format($movement->quantity, 2) }}</span>
                                        @else
                                            <span class="text-red-600 font-medium">{{ number_format($movement->quantity, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $movement->source_document }}
                                        @if($movement->source_document_id)
                                            #{{ $movement->source_document_id }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $movement->description }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200">
                        {{ $movements->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No movements found</h3>
                        <p class="mt-1 text-sm text-gray-500">Inventory movements will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
