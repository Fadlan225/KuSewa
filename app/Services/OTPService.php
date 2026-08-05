<?php

namespace App\Services;

use App\Models\login_token;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OTPService
{
    /**
     * Generate OTP and Magic Link for a given email and purpose.
     */
    public function generateOTP(string $email, string $purpose, ?string $ipAddress = null, ?string $device = null): login_token
    {
        // Invalidate old tokens for this email and purpose
        login_token::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expired_at', '>', Carbon::now())
            ->update(['expired_at' => Carbon::now()]);

        // Generate a 6-digit numeric OTP
        $otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Generate a random string for Magic Link
        $magicToken = Str::random(64);

        return login_token::create([
            'email' => $email,
            'purpose' => $purpose,
            'token' => $otpCode,
            'magic_token' => $magicToken,
            'ip_address' => $ipAddress,
            'device' => \Illuminate\Support\Str::limit($device ?? 'Unknown Device', 145),
            'expired_at' => Carbon::now()->addMinutes(1), // valid for 1 min
        ]);
    }

    /**
     * Verify OTP by the 6-digit code
     */
    public function verifyOTP(string $email, string $purpose, string $token): ?login_token
    {
        $loginToken = login_token::where('email', $email)
            ->where('purpose', $purpose)
            ->where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->whereNull('used_at')
            ->first();

        if ($loginToken) {
            $loginToken->update(['used_at' => Carbon::now()]);
            return $loginToken;
        }

        return null;
    }

    /**
     * Verify Magic Link token
     */
    public function verifyMagicLink(string $magicToken): ?login_token
    {
        $loginToken = login_token::where('magic_token', $magicToken)
            ->where('expired_at', '>', Carbon::now())
            ->whereNull('used_at')
            ->first();

        if ($loginToken) {
            $loginToken->update(['used_at' => Carbon::now()]);
            return $loginToken;
        }

        return null;
    }
}
