@extends('layouts.modern')

@section('title', 'General Ledger')
@section('breadcrumb', 'General Ledger')

@section('content')
<div class="max-w-full mx-auto px-4">
    <!-- Compact Page Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">General Ledger</h2>
                <p class="text-sm text-gray-500">Double-entry accounting transactions</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('accounts.general-ledger.export', request()->all()) }}" 
                   class="btn-outline text-sm px-3 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
                <button onclick="window.print()" class="btn-outline text-sm px-3 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Compact Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-4">
        <div class="p-4">
            <form method="GET" action="{{ route('accounts.general-ledger.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-64">
                    <label for="account_id" class="block text-xs font-medium text-gray-700 mb-1">Account</label>
                    <select name="account_id" id="account_id" class="form-control text-sm py-2">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $selectedAccountId == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="min-w-40">
                    <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from"
                           value="{{ $dateFrom }}"
                           class="form-control text-sm py-2">
                </div>
                
                <div class="min-w-40">
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to"
                           value="{{ $dateTo }}"
                           class="form-control text-sm py-2">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('accounts.general-ledger.index') }}" class="btn-secondary text-sm px-3 py-2">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Period Summary -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-900">
                    @if($selectedAccountId)
                        @php
                            $selectedAccount = $accounts->firstWhere('id', $selectedAccountId);
                        @endphp
                        <strong>Account:</strong> {{ $selectedAccount->account_code ?? '' }} - {{ $selectedAccount->account_name ?? '' }}
                    @else
                        <strong>All Accounts</strong>
                    @endif
                </div>
                <div class="text-xs text-gray-600 mt-1">
                    <strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-600">Total Debit</div>
                <div class="text-sm font-semibold text-gray-900">${{ number_format($totalDebit, 2) }}</div>
                <div class="text-xs text-gray-600 mt-1">Total Credit</div>
                <div class="text-sm font-semibold text-gray-900">${{ number_format($totalCredit, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- General Ledger Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900">Ledger Entries</h3>
            <span class="text-xs text-gray-500">{{ count($ledgerEntries) }} entries</span>
        </div>
        
        @if(count($ledgerEntries) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="ledgerTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Code
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account Name
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Parent Account
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit
                            </th>
                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ledgerEntries as $entry)
                            <tr class="hover:bg-gray-50 {{ $entry['type'] == 'debit' ? 'bg-blue-50/30' : '' }}">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs text-gray-900">{{ \Carbon\Carbon::parse($entry['date'])->format('M d, Y') }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-mono text-gray-900">{{ $entry['reference'] }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-mono font-medium text-gray-900">{{ $entry['account_code'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs text-gray-900">{{ $entry['account_name'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    @if($entry['sub_account_code'] && $entry['sub_account_name'])
                                        <div class="text-xs text-gray-600">{{ $entry['sub_account_code'] }} - {{ $entry['sub_account_name'] }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    @if($entry['debit'] > 0)
                                        <div class="text-xs font-medium text-green-700">${{ number_format($entry['debit'], 2) }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    @if($entry['credit'] > 0)
                                        <div class="text-xs font-medium text-red-700">${{ number_format($entry['credit'], 2) }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                    <div class="text-xs font-medium text-gray-900">${{ number_format($entry['balance'] ?? 0, 2) }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs text-gray-600">{{ Str::limit($entry['description'], 50) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-right text-xs font-semibold text-gray-900">
                                Totals:
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-green-700">
                                ${{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-red-700">
                                ${{ number_format($totalCredit, 2) }}
                            </td>
                            <td colspan="2" class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No ledger entries found</h3>
                <p class="mt-1 text-xs text-gray-500">No transactions found for the selected period and account.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable if available
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#ledgerTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 50,
                dom: 'Bfrtip',
                buttons: ['excel', 'pdf', 'print'],
                searching: true,
                responsive: true
            });
        }
    });
</script>
@endpush
@endsection
