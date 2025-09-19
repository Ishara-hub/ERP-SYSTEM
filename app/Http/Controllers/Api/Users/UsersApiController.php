<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roles', 'employee']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            // Role filter
            if ($request->has('role') && $request->role) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            $users = $query->paginate(15);
            $roles = Role::all();

            $data = [
                'users' => $users,
                'roles' => $roles,
                'filters' => $request->only(['search', 'role'])
            ];

            return $this->success($data, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve users: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'roles' => 'array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($request->has('roles')) {
                $user->assignRole($validated['roles']);
            }

            $user->load('roles');

            return $this->success($user, 'User created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            $user->load(['roles', 'employee']);
            return $this->success($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'roles' => 'array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            if ($request->has('roles')) {
                $user->syncRoles($validated['roles']);
            }

            $user->load('roles');

            return $this->success($user, 'User updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deletion of the last admin user
            if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
                return $this->error('Cannot delete the last admin user.', null, 403);
            }

            $user->delete();

            return $this->success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user->assignRole($validated['roles']);
            $user->load('roles');

            return $this->success($user, 'Roles assigned successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to assign roles: ' . $e->getMessage());
        }
    }

    /**
     * Remove roles from user
     */
    public function removeRoles(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user->removeRole($validated['roles']);
            $user->load('roles');

            return $this->success($user, 'Roles removed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to remove roles: ' . $e->getMessage());
        }
    }
}
