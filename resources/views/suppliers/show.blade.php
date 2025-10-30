@extends('layouts.modern')

@section('title', 'Supplier Details')
@section('breadcrumb', 'Supplier Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                <p class="text-sm text-gray-500">Supplier details and information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('suppliers.web.edit', $supplier) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Supplier
                </a>
                <a href="{{ route('suppliers.web.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Suppliers
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Supplier Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Supplier Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Basic supplier details and contact information.</p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->name }}</dd>
                        </div>
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-500">{{ $supplier->email }}</a>
                            </dd>
                        </div>
                        @if($supplier->phone)
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="tel:{{ $supplier->phone }}" class="text-blue-600 hover:text-blue-500">{{ $supplier->phone }}</a>
                            </dd>
                        </div>
                        @endif
                        @if($supplier->company_name)
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->company_name }}</dd>
                        </div>
                        @endif
                        @if($supplier->contact_person)
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->contact_person }}</dd>
                        </div>
                        @endif
                        @if($supplier->website)
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="{{ $supplier->website }}" target="_blank" class="text-blue-600 hover:text-blue-500">{{ $supplier->website }}</a>
                            </dd>
                        </div>
                        @endif
                        @if($supplier->tax_id)
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->tax_id }}</dd>
                        </div>
                        @endif
                        @if($supplier->address)
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $supplier->address }}</dd>
                        </div>
                        @endif
                        @if($supplier->notes)
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $supplier->notes }}</dd>
                        </div>
                        @endif
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if($supplier->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Supplier Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->supplier_code }}</dd>
                        </div>
                        <div class="bg-white px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->created_at->format('M d, Y \a\t g:i A') }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $supplier->updated_at->format('M d, Y \a\t g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('suppliers.web.edit', $supplier) }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Supplier
                        </a>
                        <form method="POST" action="{{ route('suppliers.web.toggle-status', $supplier) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('suppliers.web.destroy', $supplier) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this supplier?')" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Supplier
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Financial Information</h3>
                    <div class="mt-5 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Credit Limit</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if($supplier->credit_limit)
                                    {{ number_format($supplier->credit_limit, 2) }} {{ $supplier->currency ?: 'USD' }}
                                @else
                                    No limit set
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Payment Terms</span>
                            <span class="text-sm font-medium text-gray-900">{{ $supplier->payment_terms ?: 'Not specified' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Currency</span>
                            <span class="text-sm font-medium text-gray-900">{{ $supplier->currency ?: 'USD' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Statistics</h3>
                    <div class="mt-5 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Purchase Orders</span>
                            <span class="text-sm font-medium text-gray-900">{{ $supplier->purchaseOrders->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($supplier->purchaseOrders->count() > 0)
    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Latest purchase orders for this supplier.</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center text-gray-500">
                        <p>Purchase order details will be implemented when the purchase order module is enhanced.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection


