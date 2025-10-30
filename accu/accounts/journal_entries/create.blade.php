@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>New Journal Entry</h2>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('journal-entries.store') }}" id="journalForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" required value="{{ old('transaction_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control" required value="{{ old('reference') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" required value="{{ old('description') }}">
                    </div>
                </div>
                <div id="entriesContainer">
                    <div class="entry-row row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Account</label>
                            <select name="account_id[]" class="form-select account-select" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sub Account</label>
                            <select name="sub_account_id[]" class="form-select sub-account-select" disabled>
                                <option value="">None</option>
                                @foreach($subAccounts as $sub)
                                    <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_account_id }}">{{ $sub->sub_account_code }} - {{ $sub->sub_account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Debit</label>
                            <input type="number" name="debit[]" class="form-control debit" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Credit</label>
                            <input type="number" name="credit[]" class="form-control credit" step="0.01" min="0">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-entry">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="button" id="addEntry" class="btn btn-secondary">
                            <i class="fas fa-plus me-1"></i> Add Entry
                        </button>
                        <button type="submit" class="btn btn-primary float-end">
                            <i class="fas fa-save me-1"></i> Post Journal
                        </button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Totals:</strong>
                            Debit: <span id="totalDebit">0.00</span> |
                            Credit: <span id="totalCredit">0.00</span> |
                            Difference: <span id="difference">0.00</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Add new entry row
    $('#addEntry').click(function() {
        const newRow = $('.entry-row:first').clone();
        newRow.find('input').val('');
        newRow.find('select').val('');
        newRow.find('.sub-account-select').prop('disabled', true);
        $('#entriesContainer').append(newRow);
    });
    // Remove entry row
    $(document).on('click', '.remove-entry', function() {
        if ($('.entry-row').length > 1) {
            $(this).closest('.entry-row').remove();
            calculateTotals();
        }
    });
    // Load sub accounts when account changes
    $(document).on('change', '.account-select', function() {
        const accountId = $(this).val();
        const subAccountSelect = $(this).closest('.entry-row').find('.sub-account-select');
        if (accountId) {
            subAccountSelect.prop('disabled', false);
            subAccountSelect.find('option').hide();
            subAccountSelect.find('option[value=""]').show();
            subAccountSelect.find('option[data-parent="'+accountId+'"]').show();
            subAccountSelect.val('');
        } else {
            subAccountSelect.prop('disabled', true);
            subAccountSelect.val('');
        }
    });
    // Calculate totals when debit/credit changes
    $(document).on('input', '.debit, .credit', function() {
        calculateTotals();
    });
    // Prevent both debit and credit being entered
    $(document).on('change', '.debit', function() {
        if ($(this).val() > 0) {
            $(this).closest('.entry-row').find('.credit').val('');
        }
    });
    $(document).on('change', '.credit', function() {
        if ($(this).val() > 0) {
            $(this).closest('.entry-row').find('.debit').val('');
        }
    });
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;
        $('.entry-row').each(function() {
            const debit = parseFloat($(this).find('.debit').val()) || 0;
            const credit = parseFloat($(this).find('.credit').val()) || 0;
            totalDebit += debit;
            totalCredit += credit;
        });
        $('#totalDebit').text(totalDebit.toFixed(2));
        $('#totalCredit').text(totalCredit.toFixed(2));
        $('#difference').text(Math.abs(totalDebit - totalCredit).toFixed(2));
        if (totalDebit.toFixed(2) === totalCredit.toFixed(2)) {
            $('#difference').parent().removeClass('alert-danger').addClass('alert-info');
        } else {
            $('#difference').parent().removeClass('alert-info').addClass('alert-danger');
        }
    }
});
</script>
@endsection 