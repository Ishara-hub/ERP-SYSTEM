<?php

namespace App\Http\Controllers\Api\Roles;

use App\Http\Controllers\Api\ApiController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RolesApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Role::with('permissions');

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $roles = $query->paginate(15);
            $permissions = Permission::all()->groupBy('module');

            $data = [
                'roles' => $roles,
                'permissions' => $permissions,
                'filters' => $request->only(['search'])
            ];

            return $this->success($data, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve roles: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $role = Role::create(['name' => $validated['name']]);

            if ($request->has('permissions')) {
                $role->givePermissionTo($validated['permissions']);
            }

            $role->load('permissions');

            return $this->success($role, 'Role created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        try {
            $role->load('permissions');
            return $this->success($role, 'Role retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve role: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $role->update(['name' => $validated['name']]);

            if ($request->has('permissions')) {
                $role->syncPermissions($validated['permissions']);
            } else {
                $role->syncPermissions([]);
            }

            $role->load('permissions');

            return $this->success($role, 'Role updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try {
            // Prevent deletion of system roles
            if (in_array($role->name, ['admin', 'user'])) {
                return $this->error('Cannot delete system roles.', null, 403);
            }

            $role->delete();

            return $this->success(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete role: ' . $e->getMessage());
        }
    }
}
