<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Admin has access to everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Parse multiple roles (comma-separated)
        $allowedRoles = explode(',', $roles);
        $allowedRoles = array_map('trim', $allowedRoles);

        // Check if user has any of the allowed roles
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}