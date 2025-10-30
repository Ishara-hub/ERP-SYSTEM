@extends('layouts.modern')

@section('title', 'Account Details')
@section('breadcrumb', 'Account Details')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $account->account_name }}</h2>
                <p class="text-sm text-gray-500">{{ $account->account_code }} • {{ $account->account_type }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('accounts.edit', $account) }}" class="btn-outline text-sm px-4 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Account
                </a>
                <a href="{{ route('accounts.index') }}" class="btn-outline text-sm px-4 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Accounts
                </a>
            </div>
        </div>
    </div>

    <!-- Account Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Account Code</p>
                    <p class="text-lg font-semibold text-gray-900 font-mono">{{ $account->account_code }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Current Balance</p>
                    <p class="text-lg font-semibold text-gray-900">${{ number_format($account->current_balance, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Sub-Accounts</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $account->children->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 {{ $account->is_active ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $account->is_active ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <p class="text-lg font-semibold {{ $account->is_active ? 'text-green-900' : 'text-red-900' }}">
                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Account Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Account Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $account->account_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->account_type_color }} {{ $account->account_type_bg_color }}">
                                    {{ $account->account_type }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Parent Account</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($account->parent)
                                    <a href="{{ route('accounts.show', $account->parent) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $account->parent->account_name }} ({{ $account->parent->account_code }})
                                    </a>
                                @else
                                    <span class="text-gray-400">Top Level Account</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Opening Balance</dt>
                            <dd class="mt-1 text-sm text-gray-900">${{ number_format($account->opening_balance, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $account->sort_order }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $account->created_at->format('M d, Y') }}</dd>
                        </div>
                        @if($account->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $account->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Sub-Accounts -->
            @if($account->children->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Sub-Accounts</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Code
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Balance
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($account->children as $child)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono font-medium text-gray-900">{{ $child->account_code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $child->account_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${{ number_format($child->current_balance, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($child->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('accounts.show', $child) }}" class="text-blue-600 hover:text-blue-900">
                                            View
                                        </a>
                                        <a href="{{ route('accounts.edit', $child) }}" class="text-yellow-600 hover:text-yellow-900">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('accounts.edit', $account) }}" class="w-full btn-outline text-sm px-4 py-2 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Account
                    </a>
                    
                    <form action="{{ route('accounts.toggle-status', $account) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full btn-outline text-sm px-4 py-2 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            {{ $account->is_active ? 'Deactivate' : 'Activate' }} Account
                        </button>
                    </form>

                    <a href="{{ route('accounts.create') }}?parent={{ $account->id }}" class="w-full btn-primary text-sm px-4 py-2 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Sub-Account
                    </a>
                </div>
            </div>

            <!-- Account Hierarchy -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Account Hierarchy</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        @if($account->parent)
                            <div class="text-sm text-gray-500">
                                <a href="{{ route('accounts.show', $account->parent) }}" class="text-blue-600 hover:text-blue-800">
                                    ↑ {{ $account->parent->account_name }}
                                </a>
                            </div>
                        @endif
                        
                        <div class="text-sm font-medium text-gray-900 bg-blue-50 px-3 py-2 rounded">
                            {{ $account->account_name }}
                        </div>
                        
                        @if($account->children->count() > 0)
                            @foreach($account->children as $child)
                                <div class="text-sm text-gray-500 ml-4">
                                    <a href="{{ route('accounts.show', $child) }}" class="text-blue-600 hover:text-blue-800">
                                        ↓ {{ $child->account_name }}
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            @if($account->transactions->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($account->transactions->take(5) as $transaction)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $transaction->description ?? 'Transaction' }}</p>
                                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-sm font-medium {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format(abs($transaction->amount), 2) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($account->transactions->count() > 5)
                        <div class="mt-4">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View all transactions →</a>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}

.btn-outline {
    @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}
</style>
@endpush
@endsection
