<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordApiController extends ApiController
{
    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return $this->success(null, 'Password updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->serverError('Failed to update password: ' . $e->getMessage());
        }
    }
}
