@extends('layouts.modern')

@section('title', 'Item Details')
@section('breadcrumb', 'Item Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $item->item_name }}</h1>
                <div class="flex items-center space-x-4 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->item_type_bg_color }} {{ $item->item_type_color }}">
                        {{ $item->item_type }}
                    </span>
                    @if($item->is_active && !$item->is_inactive)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Inactive
                        </span>
                    @endif
                    @if($item->parent)
                        <span class="text-sm text-gray-500">Child of: {{ $item->parent->item_name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('items.web.edit', $item) }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('items.web.index') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Item Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $item->item_name }}</dd>
                        </div>
                        @if($item->item_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Item Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $item->item_number }}</dd>
                            </div>
                        @endif
                        @if($item->manufacturer_part_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Manufacturer Part Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $item->manufacturer_part_number }}</dd>
                            </div>
                        @endif
                        @if($item->unit_of_measure)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Unit of Measure</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $item->unit_of_measure }}</dd>
                            </div>
                        @endif
                        @if($item->cost > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Cost</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $item->formatted_cost }}</dd>
                            </div>
                        @endif
                        @if($item->sales_price > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sales Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $item->formatted_sales_price }}</dd>
                            </div>
                        @endif
                        @if($item->on_hand > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">On Hand</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->on_hand, 2) }}</dd>
                            </div>
                        @endif
                        @if($item->total_value > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Value</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $item->formatted_total_value }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Purchase Information -->
            @if($item->purchase_description || $item->cogsAccount || $item->preferredVendor)
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            @if($item->purchase_description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Purchase Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->purchase_description }}</dd>
                                </div>
                            @endif
                            @if($item->cogsAccount)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">COGS Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->cogsAccount->account_name }}</dd>
                                </div>
                            @endif
                            @if($item->preferredVendor)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Preferred Vendor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->preferredVendor->name }}</dd>
                                </div>
                            @endif
                            @if($item->reorder_point > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Reorder Point</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->reorder_point, 2) }}</dd>
                                </div>
                            @endif
                            @if($item->max_quantity > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Max Quantity</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->max_quantity, 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Sales Information -->
            @if($item->sales_description || $item->incomeAccount || $item->assetAccount)
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            @if($item->sales_description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Sales Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->sales_description }}</dd>
                                </div>
                            @endif
                            @if($item->incomeAccount)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Income Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->incomeAccount->account_name }}</dd>
                                </div>
                            @endif
                            @if($item->assetAccount)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Asset Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $item->assetAccount->account_name }}</dd>
                                </div>
                            @endif
                            @if($item->markup_percentage > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Markup Percentage</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->markup_percentage, 2) }}%</dd>
                                </div>
                            @endif
                            @if($item->margin_percentage > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Margin Percentage</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->margin_percentage, 2) }}%</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($item->notes)
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <p class="text-sm text-gray-900">{{ $item->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Actions and Statistics -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('items.web.edit', $item) }}" class="w-full btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Item
                        </a>
                        
                        <form method="POST" action="{{ route('items.web.toggle-status', $item) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full btn-secondary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                                {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('items.web.destroy', $item) }}" 
                              class="w-full"
                              onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Item
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Sub-items</dt>
                            <dd class="text-sm text-gray-900">{{ $item->children->count() }}</dd>
                        </div>
                        @if($item->assemblyComponents->count() > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Assembly Components</dt>
                                <dd class="text-sm text-gray-900">{{ $item->assemblyComponents->count() }}</dd>
                            </div>
                        @endif
                        @if($item->cost > 0 && $item->sales_price > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Profit per Unit</dt>
                                <dd class="text-sm text-gray-900">${{ number_format($item->sales_price - $item->cost, 2) }}</dd>
                            </div>
                        @endif
                        @if($item->markup_percentage > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Markup</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($item->markup_percentage, 1) }}%</dd>
                            </div>
                        @endif
                        @if($item->margin_percentage > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Margin</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($item->margin_percentage, 1) }}%</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Sub-items -->
            @if($item->children->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Sub-items</h3>
                        <ul class="space-y-3">
                            @foreach($item->children as $child)
                                <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <a href="{{ route('items.web.show', $child) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">
                                            {{ $child->item_name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ $child->item_type }}</p>
                                    </div>
                                    @if($child->is_active && !$child->is_inactive)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection