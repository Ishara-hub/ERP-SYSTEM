@extends('layouts.modern')

@section('title', 'Items')
@section('breadcrumb', 'Items')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Items</h1>
                <p class="text-sm text-gray-500">Manage your inventory items and services</p>
            </div>
            <a href="{{ route('items.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Item
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <form method="GET" action="{{ route('items.web.index') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search items..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="item_type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        @foreach($itemTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('item_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <select name="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Items</option>
                        <option value="top_level" {{ request('parent_id') === 'top_level' ? 'selected' : '' }}>Top Level</option>
                        @foreach($parentItems as $parent)
                            <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->item_name }}</option>
                        @endforeach
                    </select>
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

    <!-- Items Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($items->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Number
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cost
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                On Hand
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
                        @foreach($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-6 w-6 rounded-full {{ $item->item_type_bg_color }} flex items-center justify-center mr-2">
                                            <svg class="h-3 w-3 {{ $item->item_type_color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($item->item_type === 'Service')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @elseif($item->item_type === 'Inventory Part' || $item->item_type === 'Inventory Assembly')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                @endif
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                            @if($item->parent)
                                                <div class="text-xs text-gray-500">Parent: {{ $item->parent->item_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $item->item_type_bg_color }} {{ $item->item_type_color }}">
                                        {{ $item->item_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->item_number ? '#' . $item->item_number : '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->cost > 0 ? $item->formatted_cost : '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->sales_price > 0 ? $item->formatted_sales_price : '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->on_hand > 0 ? number_format($item->on_hand, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($item->is_active && !$item->is_inactive)
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
                                        <a href="{{ route('items.web.show', $item) }}" 
                                           class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('items.web.edit', $item) }}" 
                                           class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('items.web.toggle-status', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                </svg>
                                                {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('items.web.destroy', $item) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this item?')">
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
                    @if($items->previousPageUrl())
                        <a href="{{ $items->previousPageUrl() }}" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    @if($items->nextPageUrl())
                        <a href="{{ $items->nextPageUrl() }}" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $items->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $items->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $items->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No items found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first item.</p>
                <div class="mt-6">
                    <a href="{{ route('items.web.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add First Item
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection