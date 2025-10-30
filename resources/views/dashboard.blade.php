@extends('layouts.modern')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-4">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name ?? 'Admin' }}!</p>
        </div>
    </div>

    <!-- Main Content Grid: Left side (3 horizontal sections) and Right side (2 vertical sections) -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
        <!-- Left Side: 3 Horizontal Sections -->
        <div class="lg:col-span-3 space-y-4">
            <!-- VENDORS Section (Horizontal) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-700">VENDORS</h2>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Purchase Orders -->
                        <a href="{{ route('purchase-orders.web.create') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-200">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Purchase Orders</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Receive Inventory -->
                        <a href="{{ route('purchase-orders.web.index') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-200">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Receive Inventory</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Enter Bills Against Inventory -->
                        <a href="{{ route('bills.enter-bill.create') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Enter Bills Against Inventory</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Pay Bills -->
                        <a href="{{ route('bills.pay-bill.index') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Pay Bills</span>
                        </a>
                        <div class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group ml-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Enter Bills</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CUSTOMERS Section (Horizontal) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-700">CUSTOMERS</h2>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Estimates -->
                        <a href="{{ route('quotations.index') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-200">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Estimates</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Sales Orders -->
                        <a href="{{ route('sales-orders.index') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Sales Orders</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Create Invoices -->
                        <a href="{{ route('invoices.web.create') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Create Invoices</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- POS -->
                        <a href="{{ route('pos.dashboard') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-200">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Accept Credit Cards</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <!-- Payments -->
                        <a href="{{ route('payments.web.index') }}" class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-200">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Receive Payments</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- EMPLOYEES Section (Horizontal) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-700">EMPLOYEES</h2>
                </div>
                <div class="p-4">
                    <div class="flex items-center gap-3">
                        <a href="#" class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-200">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-700 text-center">Enter Time</span>
                        </a>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-xs text-gray-500">(Can be billed to customers)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: 2 Vertical Sections -->
        <div class="lg:col-span-2 space-y-4">
            <!-- COMPANY Section (Vertical) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-700">COMPANY</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <a href="{{ route('accounts.index') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Chart of Accounts</span>
                        </a>
                        <a href="{{ route('items.web.index') }}" class="flex items-center p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-yellow-200">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Items & Services</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Users</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- BANKING Section (Vertical) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-4 py-2 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-700">BANKING</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <a href="{{ route('payments.web.dashboard') }}" class="flex items-center p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-yellow-200">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Record Deposits</span>
                        </a>
                        <a href="{{ route('accounts.index') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Reconcile</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Write Checks</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">Check Register</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection