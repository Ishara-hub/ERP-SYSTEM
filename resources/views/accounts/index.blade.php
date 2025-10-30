@extends('layouts.modern')

@section('title', 'Chart of Accounts')
@section('breadcrumb', 'Chart of Accounts')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Chart of Accounts</h2>
                <p class="text-sm text-gray-500">Manage accounting accounts and hierarchy</p>
            </div>
            <a href="{{ route('accounts.create') }}" class="btn-primary text-sm px-3 py-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Account
            </a>
        </div>
    </div>

    <!-- Compact Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        <div class="p-4">
            <form method="GET" action="{{ route('accounts.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Search accounts..."
                           class="form-control text-sm py-2">
                </div>
                
                <div class="min-w-32">
                    <label for="account_type" class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                    <select name="account_type" id="account_type" class="form-control text-sm py-2">
                        <option value="">All Types</option>
                        @foreach($accountTypes ?? [] as $type)
                            <option value="{{ $type }}" {{ request('account_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="min-w-24">
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="form-control text-sm py-2">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="min-w-32">
                    <label for="parent_only" class="block text-xs font-medium text-gray-700 mb-1">Show</label>
                    <select name="parent_only" id="parent_only" class="form-control text-sm py-2">
                        <option value="">All Accounts</option>
                        <option value="1" {{ request('parent_only') ? 'selected' : '' }}>Parents Only</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn-outline text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('accounts.index') }}" class="btn-secondary text-sm px-3 py-2">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Compact Accounts Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900">Accounts</h3>
            <span class="text-xs text-gray-500">{{ $accounts->total() ?? 0 }} total</span>
        </div>
        
        @if(isset($accounts) && $accounts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Parent
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($accounts as $account)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-mono font-medium text-gray-900">{{ $account->account_code }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs text-gray-900">{{ $account->account_name }}</div>
                                    @if($account->description)
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($account->description, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $account->account_type_color }} {{ $account->account_type_bg_color }}">
                                        {{ $account->account_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($account->parent)
                                        <div class="text-xs text-gray-900">{{ Str::limit($account->parent->account_name, 20) }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $account->parent->account_code }}</div>
                                    @else
                                        <span class="text-xs text-gray-400">Top Level</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900">${{ number_format($account->current_balance, 2) }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($account->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs font-medium">
                                    <div class="flex justify-end space-x-1">
                                        <a href="{{ route('accounts.show', $account) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-1"
                                           data-bs-toggle="tooltip" 
                                           title="View">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('accounts.edit', $account) }}" 
                                           class="text-yellow-600 hover:text-yellow-900 p-1"
                                           data-bs-toggle="tooltip" 
                                           title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('accounts.toggle-status', $account) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-blue-600 hover:text-blue-900 p-1"
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 p-1 confirm-delete"
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete"
                                                    data-confirm-message="Are you sure you want to delete {{ $account->account_name }}?">
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
            
            <!-- Compact Pagination -->
            <div class="px-4 py-2 border-t border-gray-200">
                {{ $accounts->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No accounts found</h3>
                <p class="mt-1 text-xs text-gray-500">Get started by creating a new account.</p>
                <div class="mt-4">
                    <a href="{{ route('accounts.create') }}" class="btn-primary text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Account
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

