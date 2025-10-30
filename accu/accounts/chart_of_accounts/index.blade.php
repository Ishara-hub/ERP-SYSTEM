@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fas fa-book me-2"></i>Chart of Accounts</h2>
            <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Account
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
                            <th>Account Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->account_code }}</td>
                            <td>{{ $account->account_name }}</td>
                            <td>{{ $account->category->name ?? '-' }}</td>
                            <td>{{ $account->description }}</td>
                            <td>
                                <span class="badge {{ $account->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('chart-of-accounts.sub-accounts.index', $account->id) }}" class="btn btn-sm btn-info" title="Sub Accounts">
                                    <i class="fas fa-list"></i>
                                </a>
                                <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('chart-of-accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No accounts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 