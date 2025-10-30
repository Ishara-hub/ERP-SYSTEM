@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>Add Account</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('chart-of-accounts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Account Code</label>
                        <input type="text" name="account_code" class="form-control" required value="{{ old('account_code') }}">
                        @error('account_code')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" class="form-control" required value="{{ old('account_name') }}">
                        @error('account_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
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
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 