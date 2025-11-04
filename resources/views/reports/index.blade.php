@extends('layouts.modern')

@section('title', 'Reports')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Reports</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
            <p class="mt-1 text-sm text-gray-600">View comprehensive business reports and analytics</p>
        </div>

        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Sales Reports -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-blue-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-blue-900">Sales Reports</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('reports.sales-by-customer') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Sales by Customer</div>
                            <div class="text-sm text-gray-500">View sales performance by customer</div>
                        </div>
                    </a>
                    <a href="{{ route('reports.sales-by-item') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Sales by Item</div>
                            <div class="text-sm text-gray-500">View sales performance by product</div>
                        </div>
                    </a>
                    <a href="{{ route('reports.sales-trend') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Sales Trend</div>
                            <div class="text-sm text-gray-500">Monthly sales performance</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Purchase Reports -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-green-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-green-900">Purchase Reports</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('reports.purchase-by-supplier') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Purchase by Supplier</div>
                            <div class="text-sm text-gray-500">View purchases by supplier</div>
                        </div>
                    </a>
                    <a href="{{ route('reports.purchase-by-item') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Purchase by Item</div>
                            <div class="text-sm text-gray-500">View purchases by product</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Financial Reports -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-purple-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-purple-900">Financial Reports</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('reports.invoice-summary') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Invoice Summary</div>
                            <div class="text-sm text-gray-500">Invoice status and totals</div>
                        </div>
                    </a>
                    <a href="{{ route('reports.income-summary') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Income Summary</div>
                            <div class="text-sm text-gray-500">Revenue and income trends</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Item Reports -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-yellow-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-yellow-900">Item Reports</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('reports.item-profitability') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Item Profitability</div>
                            <div class="text-sm text-gray-500">Profit margins by product</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Accounting Reports -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-indigo-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-indigo-900">Accounting Reports</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('accounts.general-ledger.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">General Ledger</div>
                            <div class="text-sm text-gray-500">All transactions</div>
                        </div>
                    </a>
                    <a href="{{ route('accounts.reports.balance-sheet') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Balance Sheet</div>
                            <div class="text-sm text-gray-500">Assets, liabilities, equity</div>
                        </div>
                    </a>
                    <a href="{{ route('accounts.reports.income-statement') }}" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">Income Statement</div>
                            <div class="text-sm text-gray-500">Profit & loss statement</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Quick Stats</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $totalInvoices ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total Invoices</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $totalPOs ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Purchase Orders</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $totalCustomers ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Customers</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $totalSuppliers ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Suppliers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
