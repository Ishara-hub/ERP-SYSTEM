@extends('layouts.modern')

@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Create New User
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Add a new user to the system with appropriate roles and permissions.
        </p>
    </div>

    <!-- Create User Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" class="needs-validation" novalidate>
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="form-input @error('name') is-invalid @enderror" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="form-input @error('email') is-invalid @enderror" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="form-input @error('password') is-invalid @enderror" 
                               required>
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                                onclick="togglePassword('password')">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="form-input @error('password_confirmation') is-invalid @enderror" 
                               required>
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                                onclick="togglePassword('password_confirmation')">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Roles -->
                <div class="mb-6">
                    <label class="form-label">Roles</label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="role_{{ $role->id }}" 
                                       name="roles[]" 
                                       value="{{ $role->id }}"
                                       {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-700">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="form-label">Status</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="status_active" 
                                   name="status" 
                                   value="active"
                                   {{ old('status', 'active') == 'active' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <label for="status_active" class="ml-2 text-sm text-gray-700">
                                Active
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="status_inactive" 
                                   name="status" 
                                   value="inactive"
                                   {{ old('status') == 'inactive' ? 'checked' : '' }}
                                   class="text-primary-600 focus:ring-primary-500">
                            <label for="status_inactive" class="ml-2 text-sm text-gray-700">
                                Inactive
                            </label>
                        </div>
                    </div>
                    @error('status')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('users.index') }}" class="btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}
</script>
@endsection


