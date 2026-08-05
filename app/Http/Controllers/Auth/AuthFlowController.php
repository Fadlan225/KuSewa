<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthFlowController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Check the status of an email (whether it exists, has password, etc).
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255']
        ]);

        $result = $this->authService->checkEmail($request->email);

        return response()->json($result);
    }

    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request, \App\Services\OTPService $otpService): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'purpose' => ['required', 'string', 'in:register,forgot_password,create_password,delete_account,change_email']
        ]);

        $agent = new \Jenssegers\Agent\Agent();
        $browser = $agent->browser();
        $platform = $agent->platform();
        $deviceName = $agent->device();
        
        $parts = [];
        if ($browser) $parts[] = $browser;
        if ($platform || $deviceName) {
            $parts[] = 'di';
            if ($deviceName && $deviceName !== 'WebKit') {
                $parts[] = $deviceName;
            } elseif ($platform) {
                $parts[] = $platform;
            }
        }
        $deviceString = count($parts) > 0 ? implode(' ', $parts) : 'Unknown Device';

        $tokenRecord = $otpService->generateOTP($request->email, $request->purpose, $request->ip(), $deviceString);

        // Determine purpose string for email
        $purposeMap = [
            'register' => 'Pendaftaran Akun Baru',
            'forgot_password' => 'Lupa Password',
            'create_password' => 'Pembuatan Password Baru',
            'delete_account' => 'Penghapusan Akun',
            'change_email' => 'Perubahan Email'
        ];
        $purposeStr = $purposeMap[$request->purpose] ?? 'Verifikasi Keamanan';

        $magicLinkUrl = route('auth.magic_link.verify', ['token' => $tokenRecord->magic_token]);

        \Illuminate\Support\Facades\Mail::to($request->email)->send(
            new \App\Mail\AuthOTP(
                $tokenRecord->token, 
                $magicLinkUrl, 
                $purposeStr, 
                $request->ip(), 
                $deviceString
            )
        );

        return response()->json(['message' => 'OTP sent successfully']);
    }

    /**
     * Verify the 6-digit OTP code via AJAX.
     */
    public function verifyOtp(Request $request, \App\Services\OTPService $otpService): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'purpose' => ['required', 'string', 'in:register,forgot_password,create_password,delete_account,change_email'],
            'token' => ['required', 'string', 'size:6']
        ]);

        $tokenRecord = $otpService->verifyOTP($request->email, $request->purpose, $request->token);

        if (!$tokenRecord) {
            return response()->json(['message' => 'Kode OTP salah atau sudah kedaluwarsa.'], 400);
        }

        // Return a temporary token or success to let frontend know it can proceed
        // The frontend will pass this token to the final registration/password reset endpoint
        return response()->json([
            'message' => 'OTP verified',
            'verified_token' => $tokenRecord->magic_token // Can be used as a proof of verification for the next step
        ]);
    }

    /**
     * Verify via Magic Link (Click from Email).
     */
    public function verifyMagicLink(Request $request, \App\Services\OTPService $otpService)
    {
        $token = $request->query('token');
        if (!$token) {
            abort(400, 'Invalid token.');
        }

        $tokenRecord = $otpService->verifyMagicLink($token);

        if (!$tokenRecord) {
            abort(400, 'Link ini sudah kadaluarsa atau tidak valid.');
        }

        // We can redirect the user back to the home page with a specific query parameter
        // that the frontend AuthModal can catch to open automatically at the correct step.
        // For instance, ?auth_action=continue&email=...&purpose=...&proof=...
        return redirect()->route('Home', [
            'auth_action' => 'magic_link_success',
            'email' => $tokenRecord->email,
            'purpose' => $tokenRecord->purpose,
            'proof' => $tokenRecord->magic_token
        ]);
    }
    /**
     * Final step of registration (after OTP/Magic Link verification).
     */
    public function register(Request $request, \App\Services\OTPService $otpService): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'verified_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female'],
        ]);

        $tokenRecord = \App\Models\login_token::where('email', $request->email)
            ->where('purpose', 'register')
            ->where('magic_token', $request->verified_token)
            ->whereNotNull('used_at')
            ->where('expired_at', '>', \Carbon\Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Token verifikasi tidak valid atau sesi telah berakhir.'], 400);
        }

        // Check if email already registered to prevent duplicates
        if (\App\Models\User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email ini sudah terdaftar.'], 400);
        }

        $user = $this->authService->registerUser($request->all());

        // Perform login
        $this->authService->login($user);

        // Expire the token to prevent reuse, keeping it in history
        $tokenRecord->update(['expired_at' => \Carbon\Carbon::now()]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'redirect' => session()->pull('url.intended', route('Home', absolute: false))
        ]);
    }
    /**
     * Standard Login (Email + Password).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean']
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.'
            ], 401);
        }

        $this->authService->login($user, $request->boolean('remember', false));

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil',
            'redirect' => session()->pull('url.intended', route('Home', absolute: false))
        ]);
    }

    /**
     * Reset or Create Password (after OTP/Magic Link verification).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'verified_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'purpose' => ['required', 'string', 'in:forgot_password,create_password']
        ]);

        $tokenRecord = \App\Models\login_token::where('email', $request->email)
            ->where('purpose', $request->purpose)
            ->where('magic_token', $request->verified_token)
            ->whereNotNull('used_at')
            ->where('expired_at', '>', \Carbon\Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Token verifikasi tidak valid atau sesi telah berakhir.'], 400);
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $this->authService->setPassword($user, $request->password);

        // Perform login automatically
        $this->authService->login($user);
        $request->session()->regenerate();

        // Expire the token to prevent reuse, keeping it in history
        $tokenRecord->update(['expired_at' => \Carbon\Carbon::now()]);

        return response()->json([
            'message' => 'Password berhasil disimpan',
            'redirect' => session()->pull('url.intended', route('Home', absolute: false))
        ]);
    }
}
