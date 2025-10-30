@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0"><i class="fas fa-list me-2"></i>Sub-Accounts for <span class="fw-bold">{{ $parent->account_code }} - {{ $parent->account_name }}</span></h2>
                <div class="small">Category: {{ $parent->category->name ?? '-' }}</div>
            </div>
            <a href="{{ route('chart-of-accounts.sub-accounts.create', $parent->id) }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Sub-Account
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subAccounts as $sub)
                        <tr>
                            <td>{{ $sub->sub_account_code }}</td>
                            <td>{{ $sub->sub_account_name }}</td>
                            <td>{{ $sub->description }}</td>
                            <td>
                                <span class="badge {{ $sub->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('sub-accounts.edit', [$parent->id, $sub->id]) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sub-accounts.destroy', [$parent->id, $sub->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No sub-accounts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $subAccounts->links() }}
            </div>
            <div class="mt-3">
                <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">Back to Chart of Accounts</a>
            </div>
        </div>
    </div>
</div>
@endsection 