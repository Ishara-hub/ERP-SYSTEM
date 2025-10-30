@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fas fa-book me-2"></i>Journal Entries</h2>
            <a href="{{ route('journal-entries.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> New Journal Entry
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
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $journal)
                        <tr>
                            <td>{{ $journal->transaction_date }}</td>
                            <td>{{ $journal->reference }}</td>
                            <td>{{ $journal->description }}</td>
                            <td>{{ $journal->user->name ?? 'System' }}</td>
                            <td>
                                <a href="{{ route('journal-entries.show', $journal->id) }}" class="btn btn-sm btn-info" title="View/Print">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No journal entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $journals->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 