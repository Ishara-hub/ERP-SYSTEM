@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-chart-line me-2"></i>Income Statement</h4>
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
                                                <a href="{{ route('accounts.reports.income_statement') }}" class="btn btn-secondary">
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
                        <h5>Income Statement</h5>
                        <p>Branch: {{ $branchName }} | For the period {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
                    </div>

                    <!-- Revenue Section -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped income-statement-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>A/C Code</th>
                                    <th>Account Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="income-statement-header">
                                    <td colspan="3" class="fw-bold bg-light">
                                        <i class="fas fa-arrow-up me-2"></i>Revenue
                                    </td>
                                </tr>
                                @php $revenueTotal = 0; @endphp
                                @foreach($revenueData as $account)
                                    @php $revenueTotal += $account['amount']; @endphp
                                    @if($account['amount'] != 0)
                                        <tr>
                                            <td>{{ $account['account_code'] }}</td>
                                            <td>{{ $account['account_name'] }}</td>
                                            <td class="text-end">{{ number_format($account['amount'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @foreach($account['sub_accounts'] as $sub)
                                        @php $revenueTotal += $sub['amount']; @endphp
                                        @if($sub['amount'] != 0)
                                            <tr>
                                                <td class="ps-4">{{ $sub['sub_account_code'] }}</td>
                                                <td class="ps-4">{{ $sub['sub_account_name'] }}</td>
                                                <td class="text-end">{{ number_format($sub['amount'], 2) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                                <tr class="total-row">
                                    <td colspan="2" class="text-end fw-bold">Total Revenue</td>
                                    <td class="text-end fw-bold">{{ number_format($revenueTotal, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Expenses Section -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped income-statement-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>A/C Code</th>
                                    <th>Account Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="income-statement-header">
                                    <td colspan="3" class="fw-bold bg-light">
                                        <i class="fas fa-arrow-down me-2"></i>Expenses
                                    </td>
                                </tr>
                                @php $expensesTotal = 0; @endphp
                                @foreach($expensesData as $account)
                                    @php $expensesTotal += $account['amount']; @endphp
                                    @if($account['amount'] != 0)
                                        <tr>
                                            <td>{{ $account['account_code'] }}</td>
                                            <td>{{ $account['account_name'] }}</td>
                                            <td class="text-end">{{ number_format($account['amount'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @foreach($account['sub_accounts'] as $sub)
                                        @php $expensesTotal += $sub['amount']; @endphp
                                        @if($sub['amount'] != 0)
                                            <tr>
                                                <td class="ps-4">{{ $sub['sub_account_code'] }}</td>
                                                <td class="ps-4">{{ $sub['sub_account_name'] }}</td>
                                                <td class="text-end">{{ number_format($sub['amount'], 2) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                                <tr class="total-row">
                                    <td colspan="2" class="text-end fw-bold">Total Expenses</td>
                                    <td class="text-end fw-bold">{{ number_format($expensesTotal, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Net Income Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped income-statement-table">
                            <tbody>
                                <tr class="net-income {{ ($revenueTotal - $expensesTotal) >= 0 ? 'table-success' : 'table-danger' }}">
                                    <td colspan="2" class="text-end fw-bold">
                                        {{ ($revenueTotal - $expensesTotal) >= 0 ? 'Net Income' : 'Net Loss' }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format(abs($revenueTotal - $expensesTotal), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .income-statement-table th, .income-statement-table td {
        padding: 8px;
        vertical-align: middle;
    }
    .income-statement-header {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .account-row {
        font-weight: bold;
    }
    .sub-account {
        padding-left: 20px;
    }
    .text-end {
        text-align: right;
    }
    .total-row {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .net-income {
        font-weight: bold;
    }
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; }
        .card-header { background-color: #fff !important; color: #000 !important; }
    }
</style>
@endsection 