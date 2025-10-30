@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Category</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('account-categories.update', $category->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $category->description) }}">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <a href="{{ route('account-categories.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 