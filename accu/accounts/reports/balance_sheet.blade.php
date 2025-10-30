@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-balance-scale me-2"></i>Balance Sheet</h4>
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
                                                <a href="{{ route('accounts.reports.balance_sheet') }}" class="btn btn-secondary">
                                                    <i class="fas fa-refresh me-1"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Filter Buttons -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('accounts.reports.balance_sheet', array_merge(request()->query(), ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')])) }}" 
                                   class="btn btn-outline-primary btn-sm">This Year</a>
                                <a href="{{ route('accounts.reports.balance_sheet', array_merge(request()->query(), ['date_from' => now()->subYear()->startOfYear()->format('Y-m-d'), 'date_to' => now()->subYear()->endOfYear()->format('Y-m-d')])) }}" 
                                   class="btn btn-outline-primary btn-sm">Last Year</a>
                                <a href="{{ route('accounts.reports.balance_sheet', array_merge(request()->query(), ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->endOfMonth()->format('Y-m-d')])) }}" 
                                   class="btn btn-outline-primary btn-sm">This Month</a>
                                <a href="{{ route('accounts.reports.balance_sheet', array_merge(request()->query(), ['date_from' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->subMonth()->endOfMonth()->format('Y-m-d')])) }}" 
                                   class="btn btn-outline-primary btn-sm">Last Month</a>
                            </div>
                        </div>
                    </div>

                    <!-- Report Header -->
                    <div class="text-center mb-4">
                        <h3>OSHADI INVESTMENT (Pvt) Ltd</h3>
                        <h5>Balance Sheet</h5>
                        <p>Branch: {{ $branchName }} | As at {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
                        <div class="alert alert-info mt-2">
                            <small><i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> Assets show debit balances, Liability & Equity show credit balances as positive amounts. 
                                Net Profit increases Equity (credit), Net Loss decreases Equity (debit).
                            </small>
                        </div>
                    </div>

                    <!-- Balance Sheet Table -->
                    @if(count($groupedData) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped balance-sheet-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>A/C Code</th>
                                        <th>Account Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedData as $type => $accounts)
                                        <tr class="balance-sheet-header">
                                            <td colspan="4" class="fw-bold bg-light">
                                                <i class="fas fa-folder me-2"></i>{{ $type }}
                                            </td>
                                        </tr>
                                        @foreach($accounts as $mainAccountName => $data)
                                            <tr class="main-account">
                                                <td class="fw-bold">{{ $data['account_code'] }}</td>
                                                <td class="fw-bold">{{ $mainAccountName }}</td>
                                                <td class="text-end">
                                                    @if(isset($data['balance']) && $data['balance'] != 0)
                                                        {{ number_format($data['balance'], 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">
                                                    @if(isset($data['total_balance']) && $data['total_balance'] != 0)
                                                        {{ number_format($data['total_balance'], 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            @foreach($data['sub_accounts'] as $sub)
                                                <tr class="sub-account">
                                                    <td class="ps-4">{{ $sub['sub_account_code'] }}</td>
                                                    <td class="ps-4">{{ $sub['sub_account_name'] }}</td>
                                                    <td class="text-end">
                                                        @if($sub['balance'] != 0)
                                                            {{ number_format($sub['balance'], 2) }}
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ number_format($sub['balance'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Balance Sheet Summary -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light category-totals-card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Category Totals</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-success">Total Assets</h6>
                                                <h4 class="text-success">Rs {{ number_format($categoryTotals['Assets'], 2) }}</h4>
                                            </div>
                                                                                         <div class="col-md-6">
                                                 <h6 class="text-danger">Total Liability</h6>
                                                 <h4 class="text-danger">Rs {{ number_format($categoryTotals['Liability'], 2) }}</h4>
                                             </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="text-info">Total Equity</h6>
                                                <h4 class="text-info">Rs {{ number_format($categoryTotals['Equity'], 2) }}</h4>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-warning">Net Profit/(Loss)</h6>
                                                <h4 class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }}">Rs {{ number_format($netProfit, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light balance-equation-card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Balance Sheet Equation</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                                                                 <h6 class="text-dark">Assets = Liability + Equity</h6>
                                                <div class="mt-2">
                                                                                                     <strong>Rs {{ number_format($categoryTotals['Assets'], 2) }}</strong> = 
                                                 <strong>Rs {{ number_format($categoryTotals['Liability'], 2) }}</strong> + 
                                                 <strong>Rs {{ number_format($categoryTotals['Equity'], 2) }}</strong>
                                                </div>
                                                <div class="mt-3">
                                                    @if(abs($balanceSheetEquation) < 0.01)
                                                        <div class="alert alert-success mb-0">
                                                            <i class="fas fa-check-circle me-2"></i>Balance Sheet is Balanced ✓
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning mb-0">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>Balance Sheet Difference: Rs {{ number_format($balanceSheetEquation, 2) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- P&L Summary -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light pl-summary-card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Profit & Loss Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6 class="text-success">Total Income</h6>
                                                <h4 class="text-success">Rs {{ number_format($totalIncome, 2) }}</h4>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-danger">Total Expenses</h6>
                                                <h4 class="h4 text-danger">Rs {{ number_format($totalExpenses, 2) }}</h4>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }}">Net Profit/(Loss)</h6>
                                                <h4 class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }}">Rs {{ number_format($netProfit, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No balance sheet data found for the selected criteria</h5>
                            <p class="text-muted">Try adjusting your filters or check if there are any transactions in the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .balance-sheet-table th, .balance-sheet-table td {
        padding: 8px;
        vertical-align: middle;
    }
    .balance-sheet-header {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .main-account {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .sub-account {
        padding-left: 20px;
    }
    .text-end {
        text-align: right;
    }
    
    .category-totals-card {
        transition: transform 0.2s;
    }
    .category-totals-card:hover {
        transform: translateY(-2px);
    }
    
    .balance-equation-card {
        transition: transform 0.2s;
    }
    .balance-equation-card:hover {
        transform: translateY(-2px);
    }
    
    .pl-summary-card {
        transition: transform 0.2s;
    }
    .pl-summary-card:hover {
        transform: translateY(-2px);
    }
    
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    
    .btn-group .btn {
        border-radius: 0;
        margin-right: -1px;
    }

    .btn-group .btn:first-child {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }

    .btn-group .btn:last-child {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
        margin-right: 0;
    }

    .btn-group .btn:hover {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    .btn-group .btn.active {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; }
        .card-header { background-color: #fff !important; color: #000 !important; }
        .alert { border: 1px solid #000 !important; }
    }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Highlight active quick filter button
    const urlParams = new URLSearchParams(window.location.search);
    const dateFrom = urlParams.get('date_from');
    const dateTo = urlParams.get('date_to');
    
    if (dateFrom && dateTo) {
        // Find and highlight the matching quick filter button
        $('.btn-group .btn').each(function() {
            const href = $(this).attr('href');
            if (href.includes(dateFrom) && href.includes(dateTo)) {
                $(this).addClass('active');
            }
        });
    }
    
    // Auto-submit form when date changes
    $('#date_from, #date_to').change(function() {
        if ($('#date_from').val() && $('#date_to').val()) {
            // Validate date range
            const startDate = new Date($('#date_from').val());
            const endDate = new Date($('#date_to').val());
            
            if (startDate > endDate) {
                alert('Start date cannot be after end date');
                return;
            }
            
            // Auto-submit form
            $('form').submit();
        }
    });
});
</script>
@endpush 