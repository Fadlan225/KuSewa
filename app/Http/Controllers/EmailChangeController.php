<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailChangeOTP;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmailChangeController extends Controller
{
    /**
     * Send OTP to current email.
     */
    public function sendOldOtp(Request $request)
    {
        $user = Auth::user();

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Cache for 10 minutes
        Cache::put('email_change_old_otp_' . $user->id, [
            'otp' => $otp,
            // no new email yet
        ], now()->addMinutes(10));

        // Send to old email
        Mail::to($user->email)->send(new EmailChangeOTP($otp, 'old', null));

        return response()->json([
            'message' => 'OTP telah dikirim ke email Anda saat ini.'
        ]);
    }

    /**
     * Verify OTP sent to current email.
     */
    public function verifyOldEmail(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $cacheKey = 'email_change_old_otp_' . $user->id;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['otp'] !== $request->otp) {
            return response()->json([
                'errors' => ['otp' => ['Kode OTP salah atau sudah kedaluwarsa.']]
            ], 422);
        }

        // OTP valid, save a flag that user can now input new email
        Cache::put('email_change_verified_old_' . $user->id, true, now()->addMinutes(10));
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Email saat ini berhasil diverifikasi.'
        ]);
    }

    /**
     * Check if email is unique and send OTP to new email.
     */
    public function sendNewOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = Auth::user();

        // Ensure they verified old email
        if (!Cache::has('email_change_verified_old_' . $user->id)) {
            return response()->json([
                'errors' => ['email' => ['Sesi tidak valid. Harap verifikasi ulang email lama Anda.']]
            ], 422);
        }

        $newEmail = $request->email;

        // Check if new email is same as current
        if ($user->email === $newEmail) {
            return response()->json([
                'errors' => ['email' => ['Ini adalah alamat email Anda saat ini.']]
            ], 422);
        }

        // Check uniqueness
        if (User::where('email', $newEmail)->exists()) {
            return response()->json([
                'errors' => ['email' => ['Alamat email ini sudah terdaftar.']]
            ], 422);
        }

        // Generate 6-digit OTP for new email
        $newOtp = sprintf("%06d", mt_rand(1, 999999));
        
        // Save new OTP in cache for 10 mins
        Cache::put('email_change_new_otp_' . $user->id, [
            'otp' => $newOtp,
            'new_email' => $newEmail
        ], now()->addMinutes(10));

        // Send to new email
        Mail::to($newEmail)->send(new EmailChangeOTP($newOtp, 'new', $newEmail));

        return response()->json([
            'message' => 'OTP telah dikirim ke alamat email baru Anda.'
        ]);
    }

    /**
     * Verify OTP sent to new email and update the email.
     */
    public function verifyNewEmail(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $cacheKey = 'email_change_new_otp_' . $user->id;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['otp'] !== $request->otp) {
            return response()->json([
                'errors' => ['otp' => ['Kode OTP salah atau sudah kedaluwarsa.']]
            ], 422);
        }

        $newEmail = $cachedData['new_email'];

        // Update the email
        $user->email = $newEmail;
        $user->email_verified_at = now(); // Automatically verify since they just received an OTP there
        $user->save();

        // Clear caches
        Cache::forget($cacheKey);
        Cache::forget('email_change_verified_old_' . $user->id);

        return response()->json([
            'message' => 'Alamat email berhasil diperbarui.'
        ]);
    }
}
