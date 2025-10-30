@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>Add Sub-Account for {{ $parent->account_code }} - {{ $parent->account_name }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('chart-of-accounts.sub-accounts.store', $parent->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Sub-Account Code</label>
                        <input type="text" name="sub_account_code" class="form-control" required value="{{ old('sub_account_code') }}">
                        @error('sub_account_code')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Sub-Account Name</label>
                        <input type="text" name="sub_account_name" class="form-control" required value="{{ old('sub_account_name') }}">
                        @error('sub_account_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-12 text-end">
                        <a href="{{ route('chart-of-accounts.sub-accounts.index', $parent->id) }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Sub-Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 