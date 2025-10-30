@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Account Payment</h2>
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
            <form method="POST" action="{{ route('accounts.payments.store') }}" id="paymentForm">
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
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" id="paymentMethod" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        @error('payment_method')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3" id="chequeDetails" style="display:none;">
                        <label class="form-label">Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control">
                        @error('cheque_no')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Accounting Date</label>
                        <input type="date" name="accounting_date" class="form-control" required value="{{ old('accounting_date', date('Y-m-d')) }}">
                        @error('accounting_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" required value="{{ old('description') }}">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
                <h4 class="mt-4">Payment Account (Credit)</h4>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Account</label>
                        <select name="credit_account_id" class="form-select" required id="creditAccount">
                            <option value="">Select Payment Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('credit_account_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sub Account</label>
                        <select name="credit_sub_account_id" class="form-select" id="creditSubAccount">
                            <option value="">None</option>
                            @foreach($subAccounts as $sub)
                                <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_account_id }}">{{ $sub->sub_account_code }} - {{ $sub->sub_account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Amount (Rs.)</label>
                        <input type="number" class="form-control" id="totalAmount" step="0.01" min="0" readonly>
                    </div>
                </div>
                <h4 class="mt-4">Payment Entries (Debit)</h4>
                <div id="expenseEntries">
                    <div class="entry-row row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Account</label>
                            <select name="debit_account_id[]" class="form-select account-select" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sub Account</label>
                            <select name="debit_sub_account_id[]" class="form-select sub-account-select" disabled>
                                <option value="">None</option>
                                @foreach($subAccounts as $sub)
                                    <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_account_id }}">{{ $sub->sub_account_code }} - {{ $sub->sub_account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Amount (Rs.)</label>
                            <input type="number" name="debit[]" class="form-control debit" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Description</label>
                            <input type="text" name="debit_description[]" class="form-control">
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
                            <i class="fas fa-save me-1"></i> Save Payment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show/hide cheque details
    $('#paymentMethod').change(function() {
        if ($(this).val() === 'cheque') {
            $('#chequeDetails').show();
            $('input[name="cheque_no"]').prop('required', true);
        } else {
            $('#chequeDetails').hide();
            $('input[name="cheque_no"]').prop('required', false);
        }
    });
    
    // Add new entry row
    $('#addEntry').click(function() {
        const newRow = $('.entry-row:first').clone();
        newRow.find('input').val('');
        newRow.find('select').val('');
        newRow.find('.sub-account-select').prop('disabled', true);
        $('#expenseEntries').append(newRow);
    });
    
    // Remove entry row
    $(document).on('click', '.remove-entry', function() {
        if ($('.entry-row').length > 1) {
            $(this).closest('.entry-row').remove();
            calculateTotal();
        }
    });
    
    // Calculate total amount when debit amounts change
    $(document).on('input', '.debit', function() {
        calculateTotal();
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
    
    // Load sub accounts for credit account
    $('#creditAccount').change(function() {
        const accountId = $(this).val();
        const subAccountSelect = $('#creditSubAccount');
        
        if (accountId) {
            subAccountSelect.find('option').hide();
            subAccountSelect.find('option[value=""]').show();
            subAccountSelect.find('option[data-parent="'+accountId+'"]').show();
        } else {
            subAccountSelect.find('option').hide();
            subAccountSelect.find('option[value=""]').show();
        }
        subAccountSelect.val('');
    });
    
    // Calculate total function
    function calculateTotal() {
        let total = 0;
        $('.debit').each(function() {
            const amount = parseFloat($(this).val()) || 0;
            if (amount < 0) {
                $(this).val('');
                alert("Amount cannot be negative");
                return;
            }
            total += amount;
        });
        $('#totalAmount').val(total.toFixed(2));
    }
    
    // Form submission validation
    $('#paymentForm').on('submit', function(e) {
        const total = parseFloat($('#totalAmount').val()) || 0;
        if (total <= 0) {
            e.preventDefault();
            alert('Please enter at least one debit amount greater than zero.');
            return false;
        }
        
        // Ensure at least one debit entry has an amount
        let hasValidAmount = false;
        $('.debit').each(function() {
            if (parseFloat($(this).val()) > 0) {
                hasValidAmount = true;
                return false; // break the loop
            }
        });
        
        if (!hasValidAmount) {
            e.preventDefault();
            alert('Please enter at least one debit amount greater than zero.');
            return false;
        }
    });
    
    // Initial calculation
    calculateTotal();
});
</script>
@endsection 