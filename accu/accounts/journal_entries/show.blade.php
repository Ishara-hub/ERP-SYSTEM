@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fas fa-print me-2"></i>Journal Voucher</h2>
            <button onclick="window.print()" class="btn btn-light no-print"><i class="fas fa-print"></i> Print</button>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Date:</strong> {{ $journal->transaction_date }}<br>
                <strong>Reference:</strong> {{ $journal->reference }}<br>
                <strong>Description:</strong> {{ $journal->description }}<br>
                <strong>Created By:</strong> {{ $journal->user->name ?? 'System' }}
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th>Sub Account</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDebit = 0; $totalCredit = 0; @endphp
                        @foreach($journal->entries as $entry)
                        <tr>
                            <td>{{ $entry->account->account_code ?? '' }} - {{ $entry->account->account_name ?? '' }}</td>
                            <td>{{ $entry->subAccount->sub_account_code ?? '-' }} {{ $entry->subAccount->sub_account_name ?? '' }}</td>
                            <td>{{ $entry->description }}</td>
                            <td class="text-end">{{ number_format($entry->debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->credit, 2) }}</td>
                        </tr>
                        @php $totalDebit += $entry->debit; $totalCredit += $entry->credit; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; }
    body { background: #fff !important; }
}
</style>
@endsection 