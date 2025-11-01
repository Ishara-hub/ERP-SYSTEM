@extends('layouts.modern')

@section('title', 'Home')
@section('content')

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Home</h1>
            <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name ?? 'Admin' }}! Here's your company overview.</p>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="text-blue-100 text-sm font-medium">Total Revenue</div>
                <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="text-3xl font-bold">${{ number_format($financial['total_sales'], 2) }}</div>
            <div class="text-blue-100 text-xs mt-1">Total sales to date</div>
        </div>

        <!-- Total Expenses -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="text-red-100 text-sm font-medium">Total Expenses</div>
                <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="text-3xl font-bold">${{ number_format($financial['total_purchases'], 2) }}</div>
            <div class="text-red-100 text-xs mt-1">Total purchases to date</div>
        </div>

        <!-- Net Profit -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="text-green-100 text-sm font-medium">Net Profit</div>
                <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="text-3xl font-bold">${{ number_format($financial['total_sales'] - $financial['total_purchases'], 2) }}</div>
            <div class="text-green-100 text-xs mt-1">Revenue - Expenses</div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="text-yellow-100 text-sm font-medium">Pending Invoices</div>
                <svg class="w-8 h-8 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="text-3xl font-bold">{{ number_format($financial['pending_invoices']) }}</div>
            <div class="text-yellow-100 text-xs mt-1">${{ number_format($financial['pending_amount'], 2) }} pending</div>
        </div>
    </div>

    <!-- Charts and Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales vs Purchases Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Sales vs Purchases</h2>
                    <p class="text-sm text-gray-600">Last 30 days performance</p>
                </div>
                <div class="flex space-x-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                        <span class="text-xs text-gray-600">Sales</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                        <span class="text-xs text-gray-600">Purchases</span>
                    </div>
                </div>
            </div>
            <div style="height: 350px;">
                <canvas id="salesVsPurchasesChart"></canvas>
            </div>
        </div>

        <!-- Invoice Status Breakdown -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Invoice Status</h2>
                    <p class="text-sm text-gray-600">Payment status breakdown</p>
                </div>
            </div>
            <div style="height: 350px;">
                <canvas id="invoiceStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Customers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900">Customers</h3>
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $stats['total_customers'] }}</div>
                <div class="flex items-center text-sm text-gray-600">
                    <span>{{ $stats['active_customers'] }} active</span>
                    <span class="mx-2">•</span>
                    <span class="text-green-600 font-medium">{{ $stats['total_customers'] > 0 ? round(($stats['active_customers'] / $stats['total_customers']) * 100, 1) : 0 }}% active rate</span>
                </div>
            </div>
        </div>

        <!-- Suppliers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-purple-900">Suppliers</h3>
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $stats['total_suppliers'] }}</div>
                <div class="flex items-center text-sm text-gray-600">
                    <span>{{ $stats['active_suppliers'] }} active</span>
                    <span class="mx-2">•</span>
                    <span class="text-green-600 font-medium">{{ $stats['total_suppliers'] > 0 ? round(($stats['active_suppliers'] / $stats['total_suppliers']) * 100, 1) : 0 }}% active rate</span>
                </div>
            </div>
        </div>

        <!-- Items & Products -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-green-900">Items & Services</h3>
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $stats['total_items'] }}</div>
                <div class="flex items-center text-sm text-gray-600">
                    <span>{{ $stats['active_items'] }} active</span>
                    <span class="mx-2">•</span>
                    <span class="text-green-600 font-medium">{{ $stats['total_items'] > 0 ? round(($stats['active_items'] / $stats['total_items']) * 100, 1) : 0 }}% active rate</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Top Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 px-6 py-4 border-b border-indigo-200">
                <h3 class="text-lg font-semibold text-indigo-900">Recent Invoices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent_invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $invoice['invoice_no'] }}</div>
                                <div class="text-xs text-gray-500">{{ $invoice['date'] }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $invoice['customer_name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${{ $invoice['total_amount'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($invoice['status'] === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                @elseif($invoice['status'] === 'unpaid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Unpaid
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Partial
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No recent invoices</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recent_invoices->count() > 0)
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <a href="{{ route('invoices.web.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View all invoices →</a>
            </div>
            @endif
        </div>

        <!-- Recent Purchase Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                <h3 class="text-lg font-semibold text-orange-900">Recent Purchase Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent_purchase_orders as $po)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $po['po_number'] }}</div>
                                <div class="text-xs text-gray-500">{{ $po['date'] }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $po['supplier_name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${{ $po['total_amount'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($po['status'] === 'received')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Received
                                    </span>
                                @elseif($po['status'] === 'confirmed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Confirmed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ ucfirst($po['status']) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No recent purchase orders</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recent_purchase_orders->count() > 0)
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <a href="{{ route('purchase-orders.web.index') }}" class="text-sm text-orange-600 hover:text-orange-800 font-medium">View all purchase orders →</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Invoices</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_invoices']) }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Purchase Orders</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_purchase_orders']) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Sales Orders</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_sales_orders']) }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales vs Purchases Chart
    const salesVsPurchasesCtx = document.getElementById('salesVsPurchasesChart').getContext('2d');
    
    const salesData = @json($sales_data);
    const purchaseData = @json($purchase_data);
    
    // Create a map of all data
    const salesMap = {};
    const purchaseMap = {};
    
    salesData.forEach(item => {
        salesMap[item.date] = item.amount;
    });
    
    purchaseData.forEach(item => {
        purchaseMap[item.date] = item.amount;
    });
    
    // Get all unique dates and sort
    const allDates = [...new Set([...salesData.map(s => s.date), ...purchaseData.map(p => p.date)])].sort();
    
    const mappedSales = allDates.map(date => salesMap[date] || 0);
    const mappedPurchases = allDates.map(date => purchaseMap[date] || 0);
    
    new Chart(salesVsPurchasesCtx, {
        type: 'line',
        data: {
            labels: allDates,
            datasets: [{
                label: 'Sales',
                data: mappedSales,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            }, {
                label: 'Purchases',
                data: mappedPurchases,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    // Invoice Status Chart
    const invoiceStatusCtx = document.getElementById('invoiceStatusChart').getContext('2d');
    
    new Chart(invoiceStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Unpaid', 'Partial'],
            datasets: [{
                data: [{{ $invoice_status['paid'] }}, {{ $invoice_status['unpaid'] }}, {{ $invoice_status['partial'] }}],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(251, 191, 36, 0.8)'
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(251, 191, 36)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
