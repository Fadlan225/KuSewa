<?php

namespace App\Services;

use App\Models\User;
use App\Models\auth_provider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthService
{
    /**
     * Check the status of an email for authentication routing.
     * Returns an array with status and related data.
     */
    public function checkEmail(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'status' => 'not_registered',
            ];
        }

        if ($user->password) {
            return [
                'status' => 'registered_with_password',
                'user' => $user,
            ];
        }

        // Check if user has Google provider
        $provider = auth_provider::where('user_id', $user->id)->first();
        if ($provider) {
            return [
                'status' => 'registered_without_password', // Created via Google
                'provider' => $provider->provider,
                'user' => $user,
            ];
        }

        // Edge case: registered but no password and no provider
        return [
            'status' => 'invalid_state',
        ];
    }

    /**
     * Create a new user from email flow.
     */
    public function registerUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => Carbon::now(), // Verified via OTP
        ]);
    }

    /**
     * Set password for an existing user (e.g. Google user or Forgot Password).
     */
    public function setPassword(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Perform login.
     */
    public function login(User $user, bool $remember = false): void
    {
        Auth::login($user, $remember);
        $user->update(['last_login_at' => Carbon::now()]);
    }
}
