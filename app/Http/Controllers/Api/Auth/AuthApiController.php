<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthApiController extends ApiController
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $user = $request->user();
            $token = $user->createToken('auth-token')->plainTextToken;

            $data = [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->success($data, 'Login successful');
        } catch (\Exception $e) {
            return $this->error('Login failed: ' . $e->getMessage(), null, 401);
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            event(new Registered($user));

            Auth::login($user);

            $token = $user->createToken('auth-token')->plainTextToken;

            $data = [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->success($data, 'Registration successful', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->success(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->serverError('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user()->load('roles');
            return $this->success($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Refresh the user's token.
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            $token = $user->createToken('auth-token')->plainTextToken;

            $data = [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->success($data, 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->serverError('Token refresh failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        try {
            // TODO: Implement forgot password functionality
            return $this->success(['message' => 'Forgot password functionality will be implemented soon'], 'Forgot password not implemented');
        } catch (\Exception $e) {
            return $this->serverError('Forgot password failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request)
    {
        try {
            // TODO: Implement password reset functionality
            return $this->success(['message' => 'Password reset functionality will be implemented soon'], 'Password reset not implemented');
        } catch (\Exception $e) {
            return $this->serverError('Password reset failed: ' . $e->getMessage());
        }
    }
}
