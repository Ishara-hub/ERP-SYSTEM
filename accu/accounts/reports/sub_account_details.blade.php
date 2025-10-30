@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-file-invoice me-2"></i>Sub-Account Transaction Details</h4>
                        <div>
                            <a href="{{ route('accounts.reports.chart_of_accounts_data', [
                                'date_from' => $dateFrom,
                                'date_to' => $dateTo,
                                'branch_id' => $branchId
                            ]) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Chart
                            </a>
                            <button class="btn btn-light btn-sm ms-2" onclick="window.print()">
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
                                    <input type="hidden" name="id" value="{{ $subAccount->id }}">
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
                                                <a href="{{ route('accounts.reports.sub_account_details', ['id' => $subAccount->id]) }}" class="btn btn-secondary">
                                                    <i class="fas fa-refresh me-1"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sub-Account Information -->
                    <div class="sub-account-header mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="info-label">Sub-Account:</span>
                                <span>{{ $subAccount->sub_account_code }} - {{ $subAccount->sub_account_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="info-label">Parent Account:</span>
                                <span>{{ $subAccount->parentAccount->account_code ?? '' }} - {{ $subAccount->parentAccount->account_name ?? '' }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="info-label">Category:</span>
                                <span>{{ $subAccount->parentAccount->category->name ?? '' }}</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <span class="info-label">Branch:</span>
                                <span>{{ $branchName }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="info-label">Period:</span>
                                <span>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="info-label">Net Balance:</span>
                                <span class="{{ $netBalance >= 0 ? 'debit-amount' : 'credit-amount' }}">
                                    {{ number_format(abs($netBalance), 2) }}
                                    ({{ $netBalance >= 0 ? 'Debit' : 'Credit' }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    @if(count($transactions) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped sub-account-details">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th>Branch</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $runningBalance = 0; @endphp
                                    @foreach($transactions as $transaction)
                                        @php 
                                            $runningBalance += ($transaction->debit - $transaction->credit);
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($transaction->journal->transaction_date)->format('d/m/Y') }}</td>
                                            <td>{{ $transaction->journal->description }}</td>
                                            <td>{{ $transaction->journal->reference }}</td>
                                            <td>{{ $transaction->journal->branch->branch_name ?? 'N/A' }}</td>
                                            <td class="text-end debit-amount">{{ number_format($transaction->debit, 2) }}</td>
                                            <td class="text-end credit-amount">{{ number_format($transaction->credit, 2) }}</td>
                                            <td class="text-end">{{ number_format($runningBalance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end fw-bold">Totals:</td>
                                        <td class="text-end fw-bold debit-amount">{{ number_format($totalDebit, 2) }}</td>
                                        <td class="text-end fw-bold credit-amount">{{ number_format($totalCredit, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($netBalance, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions found for the selected criteria</h5>
                            <p class="text-muted">Try adjusting your filters or check if there are any transactions in the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sub-account-details th, .sub-account-details td {
        padding: 8px;
        vertical-align: middle;
    }
    .info-label {
        font-weight: bold;
        color: #6c757d;
    }
    .sub-account-header {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
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
        background-color: #f8f9fa;
    }
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; }
        .card-header { background-color: #fff !important; color: #000 !important; }
    }
</style>
@endsection 