<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Use only models we know exist
            $stats = [
                'total_users' => User::count(),
                'total_roles' => Role::count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'pending_users' => User::whereNull('email_verified_at')->count(),
            ];

            $recent_users = User::with('roles')
                ->latest()
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            // Fallback data if database queries fail
            $stats = [
                'total_users' => 0,
                'total_roles' => 0,
                'active_users' => 0,
                'pending_users' => 0,
            ];

            $recent_users = collect();
        }

        // Return dashboard Blade view
        return view('dashboard', [
            'stats' => $stats,
            'recent_users' => $recent_users,
        ]);
    }
}