@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>Add Category</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('account-categories.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <a href="{{ route('account-categories.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 