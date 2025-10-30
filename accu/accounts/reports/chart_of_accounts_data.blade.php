@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-chart-bar me-2"></i>Chart of Accounts Data</h4>
                        <div>
                            <button class="btn btn-light btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="date_from" class="form-label">Date From</label>
                                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date_to" class="form-label">Date To</label>
                                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="branch_id" class="form-label">Branch</label>
                                            <select name="branch_id" id="branch_id" class="form-control">
                                                <option value="">All Branches</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                        {{ $branch->branch_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-filter me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('accounts.reports.chart_of_accounts_data') }}" class="btn btn-secondary">
                                                    <i class="fas fa-refresh me-1"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Report Header -->
                    <div class="text-center mb-4">
                        <h3>OSHADI INVESTMENT (Pvt) Ltd</h3>
                        <h5>Chart of Accounts Data</h5>
                        <p>Branch: {{ $branchName }} | Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
                    </div>

                    <!-- Chart of Accounts Table -->
                    @if(count($groupedData) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped chart-of-accounts-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Account Code</th>
                                        <th>Account Description</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedData as $categoryId => $category)
                                        <tr class="category-header">
                                            <td colspan="5" class="fw-bold bg-light">
                                                <i class="fas fa-folder me-2"></i>{{ $category['category_name'] }}
                                            </td>
                                        </tr>
                                        
                                        @foreach($category['accounts'] as $accountId => $account)
                                            <tr class="account-row">
                                                <td class="fw-bold">{{ $account['account_code'] }}</td>
                                                <td class="fw-bold">{{ $account['account_name'] }}</td>
                                                <td class="text-end debit-amount">
                                                    @if(isset($account['debit']))
                                                        {{ number_format($account['debit'], 2) }}
                                                    @else
                                                        {{ number_format($account['total_debit'], 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-end credit-amount">
                                                    @if(isset($account['credit']))
                                                        {{ number_format($account['credit'], 2) }}
                                                    @else
                                                        {{ number_format($account['total_credit'], 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">
                                                    @if(isset($account['balance']))
                                                        {{ number_format($account['balance'], 2) }}
                                                    @else
                                                        {{ number_format($account['total_balance'], 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            
                                            @foreach($account['sub_accounts'] as $subAccount)
                                                <tr class="sub-account-row">
                                                    <td class="ps-4">{{ $subAccount['sub_account_code'] }}</td>
                                                    <td class="ps-4">
                                                        <a href="{{ route('accounts.reports.sub_account_details', [
                                                            'id' => $subAccount['sub_account_id'],
                                                            'date_from' => $dateFrom,
                                                            'date_to' => $dateTo,
                                                            'branch_id' => $branchId
                                                        ]) }}" class="sub-account-link">
                                                            {{ $subAccount['sub_account_name'] }}
                                                        </a>
                                                    </td>
                                                    <td class="text-end debit-amount">{{ number_format($subAccount['debit'], 2) }}</td>
                                                    <td class="text-end credit-amount">{{ number_format($subAccount['credit'], 2) }}</td>
                                                    <td class="text-end">{{ number_format($subAccount['balance'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        
                                        <tr class="category-total-row">
                                            <td colspan="2" class="text-end fw-bold">Total {{ $category['category_name'] }}</td>
                                            <td class="text-end fw-bold debit-amount">{{ number_format($category['total_debit'], 2) }}</td>
                                            <td class="text-end fw-bold credit-amount">{{ number_format($category['total_credit'], 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format($category['total_balance'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No data found for the selected criteria</h5>
                            <p class="text-muted">Try adjusting your filters or check if there are any transactions in the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chart-of-accounts-table th, .chart-of-accounts-table td {
        padding: 8px;
        vertical-align: middle;
    }
    .category-header {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .account-row {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .sub-account-row {
        padding-left: 30px;
    }
    .sub-account-link {
        color: #0d6efd;
        text-decoration: none;
    }
    .sub-account-link:hover {
        text-decoration: underline;
    }
    .text-end {
        text-align: right;
    }
    .debit-amount {
        color: #0d6efd;
    }
    .credit-amount {
        color: #dc3545;
    }
    .total-row {
        font-weight: bold;
        background-color: #e9ecef;
    }
    .category-total-row {
        font-weight: bold;
        background-color: #dee2e6;
    }
    .amount-cell {
        min-width: 100px;
    }
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; }
        .card-header { background-color: #fff !important; color: #000 !important; }
    }
</style>
@endsection 