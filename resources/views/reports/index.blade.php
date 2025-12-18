@extends('layouts.modern')

@section('title', 'Business Reports')

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
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Reports Center</h1>
                <p class="mt-1 text-sm text-gray-600">Select a category to view detailed business and financial reports</p>
            </div>
            <div class="relative">
                <input type="text" id="report-search" placeholder="Search reports..." 
                       class="w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <svg class="absolute right-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Dashboard Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="report-grid">
            
            <!-- 1. Company & Financial Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-blue-600 to-indigo-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">1️⃣</span> Company & Financial
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('accounts.reports.income-statement') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Profit & Loss (Income Statement)
                    </a>
                    <a href="{{ route('accounts.reports.balance-sheet') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Balance Sheet
                    </a>
                    <a href="{{ route('accounts.reports.cash-flow') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Statement of Cash Flows
                    </a>
                    <a href="{{ route('accounts.reports.trial-balance') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Trial Balance
                    </a>
                    <a href="{{ route('accounts.general-ledger.index') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        General Ledger
                    </a>
                    <a href="{{ route('journal-entries.web.index') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-blue-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Journal Report
                    </a>
                </div>
            </div>

            <!-- 2. Sales & Receivables Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-emerald-600 to-teal-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">2️⃣</span> Sales & Receivables
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('accounts.reports.customer-balance') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Customer Balance Summary
                    </a>
                    <a href="{{ route('reports.invoice-summary') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Invoice List / Summary
                    </a>
                    <a href="{{ route('accounts.reports.ar-aging') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        A/R Aging Summary
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Customer Balance Detail
                    </a>
                    <a href="{{ route('reports.sales-by-item') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Sales by Product/Service
                    </a>
                </div>
            </div>

            <!-- 3. Expense & Payables Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-red-600 to-rose-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">3️⃣</span> Expense & Payables
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('accounts.reports.vendor-balance') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-rose-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Vendor Balance Summary
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Unpaid Bills
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        A/P Aging Summary
                    </a>
                    <a href="{{ route('reports.purchase-by-item') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-rose-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Purchases by Item
                    </a>
                </div>
            </div>

            <!-- 4. Banking Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-cyan-600 to-blue-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">4️⃣</span> Banking Reports
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('bank-reconciliation.index') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-cyan-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Bank Reconciliation
                    </a>
                    <a href="{{ route('record-deposit.index') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-cyan-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Deposit Detail
                    </a>
                    <a href="{{ route('accounts.write-check.index') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-cyan-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Check Detail
                    </a>
                </div>
            </div>

            <!-- 5. Payroll Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-purple-600 to-violet-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">5️⃣</span> Payroll Reports
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Payroll Summary
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Employee Earnings Summary
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Statutory (EPF/ETF) Reports
                    </a>
                </div>
            </div>

            <!-- 6. Inventory Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-orange-600 to-amber-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">6️⃣</span> Inventory Reports
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('reports.inventory.valuation-summary') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Inventory Valuation Summary
                    </a>
                    <a href="{{ route('reports.inventory.valuation-detail') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Inventory Valuation Detail
                    </a>
                    <a href="{{ route('reports.inventory.product-service-list') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Product / Service List
                    </a>
                    <a href="{{ route('reports.inventory.stock-on-hand') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Stock on Hand
                    </a>
                    <a href="{{ route('reports.inventory.stock-movement') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Stock Movement Report
                    </a>
                    <a href="{{ route('reports.inventory.low-stock') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Low Stock Report
                    </a>
                    <a href="{{ route('reports.inventory.sales-by-item') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Sales by Item
                    </a>
                    <a href="{{ route('reports.inventory.purchases-by-item') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-amber-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        Purchases by Item
                    </a>
                </div>
            </div>

            <!-- 7. Tax Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-gray-700 to-slate-700">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">7️⃣</span> Tax Reports
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Sales Tax Summary
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        VAT Report
                    </a>
                </div>
            </div>

            <!-- 8. Budget & Forecast Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-pink-600 to-fuchsia-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">8️⃣</span> Budget & Forecast
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Budget Overview
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Budget vs Actual
                    </a>
                </div>
            </div>

            <!-- 9. Management Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-yellow-600 to-orange-600">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">9️⃣</span> Management Reports
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="{{ route('reports.sales-trend') }}" class="report-link flex items-center p-2 text-sm text-gray-700 hover:bg-orange-50 rounded-md transition group">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400 mr-3 group-hover:scale-125 transition-transform"></span>
                        KPI Dashboard / Sales Trend
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Gross Margin Report
                    </a>
                </div>
            </div>

            <!-- 10. Accountant & Audit Reports -->
            <div class="report-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                <div class="px-5 py-4 bg-gradient-to-r from-slate-800 to-black">
                    <h2 class="text-md font-bold text-white flex items-center">
                        <span class="mr-2 text-xl">🔟</span> Accountant & Audit
                    </h2>
                </div>
                <div class="p-4 space-y-1">
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Audit Log
                    </a>
                    <a href="#" class="report-link flex items-center p-2 text-sm text-gray-400 cursor-not-allowed italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></span>
                        Voided Transactions
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('report-search');
        const reportLinks = document.querySelectorAll('.report-link');
        const reportCards = document.querySelectorAll('.report-card');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            
            reportCards.forEach(card => {
                let cardHasMatch = false;
                const links = card.querySelectorAll('.report-link');
                
                links.forEach(link => {
                    const text = link.textContent.toLowerCase();
                    if (text.includes(query)) {
                        link.style.display = '';
                        cardHasMatch = true;
                    } else {
                        link.style.display = 'none';
                    }
                });

                if (cardHasMatch || card.querySelector('h2').textContent.toLowerCase().includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
