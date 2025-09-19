<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileApiController extends ApiController
{
    /**
     * Show the user's profile settings.
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user();
            $user->load('roles');

            $data = [
                'user' => $user,
                'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            ];

            return $this->success($data, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve profile: ' . $e->getMessage());
        }
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            $user->load('roles');

            return $this->success($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->success(null, 'Account deleted successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to delete account: ' . $e->getMessage());
        }
    }
}
