@extends('layouts.modern')

@section('title', 'User Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    User Details
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    View user information and permissions.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <a href="{{ route('users.edit', $user) }}" class="btn-outline">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </a>
                <a href="{{ route('users.index') }}" class="btn-primary ml-3">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- User Profile -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
            </div>
            <div class="card-body">
                <div class="flex items-center space-x-4">
                    <img class="h-16 w-16 rounded-full" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" 
                         alt="{{ $user->name }}">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">{{ $user->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        <div class="mt-2">
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-warning">Pending Verification</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">Account Statistics</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                        <dd class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $user->updated_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                        <dd class="text-sm text-gray-900">
                            @if($user->email_verified_at)
                                {{ $user->email_verified_at->format('M d, Y') }}
                            @else
                                Not verified
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <a href="{{ route('users.edit', $user) }}" class="btn-outline w-full">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit User
                    </a>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn-danger w-full"
                                data-confirm-delete
                                data-confirm-message="Are you sure you want to delete this user?">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles and Permissions -->
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">Roles and Permissions</h3>
        </div>
        <div class="card-body">
            @if($user->roles->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($user->roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900">{{ $role->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $role->description ?? 'No description available' }}</p>
                            @if($role->permissions->count() > 0)
                                <div class="mt-3">
                                    <h5 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Permissions</h5>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(5) as $permission)
                                            <span class="badge badge-info text-xs">{{ $permission->name }}</span>
                                        @endforeach
                                        @if($role->permissions->count() > 5)
                                            <span class="badge badge-info text-xs">+{{ $role->permissions->count() - 5 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No roles assigned</h3>
                    <p class="mt-1 text-sm text-gray-500">This user doesn't have any roles assigned.</p>
                    <div class="mt-6">
                        <a href="{{ route('users.edit', $user) }}" class="btn-primary">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Assign Roles
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

