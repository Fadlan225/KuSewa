<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        try {
            $deviceInfo = \App\Services\DeviceDetectorService::detect($request->header('User-Agent'));

            \App\Models\AccountActivity::create([
                'user_id' => $request->user()->id,
                'type' => 'password_change',
                'ip_address' => $request->ip(),
                'user_agent' => $deviceInfo['raw_agent'],
                'os' => $deviceInfo['os'],
                'device_name' => $deviceInfo['device_name'],
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                // location can be null for password change to keep it fast
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to log password change: ' . $e->getMessage());
        }

        return back();
    }
}
