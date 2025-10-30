<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Users\UsersApiController;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        // Use your existing API controller logic
        $this->apiController = new UsersApiController();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Direct model access for better performance
        $query = User::with('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->paginate(15);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Use your existing API logic
        $apiResponse = $this->apiController->store($request);
        $data = $apiResponse->getData();
        
        if ($data->success) {
            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        }
        
        return back()->withErrors($data->errors ?? ['error' => 'Failed to create user'])
                    ->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Use your existing API logic
        $apiResponse = $this->apiController->show($user);
        $data = $apiResponse->getData();
        
        if ($data->success) {
            $user = $data->data->user;
            return view('users.show', compact('user'));
        }
        
        return redirect()->route('users.index')
            ->with('error', 'User not found.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Use your existing API logic
        $apiResponse = $this->apiController->update($request, $user);
        $data = $apiResponse->getData();
        
        if ($data->success) {
            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        }
        
        return back()->withErrors($data->errors ?? ['error' => 'Failed to update user'])
                    ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Use your existing API logic
        $apiResponse = $this->apiController->destroy($user);
        $data = $apiResponse->getData();
        
        if ($data->success) {
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        }
        
        return redirect()->route('users.index')
            ->with('error', 'Failed to delete user.');
    }
}
