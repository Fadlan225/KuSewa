<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\auth_provider;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(Request $request)
    {
        // Store intended URL if not already stored
        if (!session()->has('url.intended')) {
            session()->put('url.intended', url()->previous());
        }

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('Home')->with('error', 'Gagal login menggunakan Google. Silakan coba lagi.');
        }

        // Check if user is already logged in (Linking Account)
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if this google account is already linked to someone else
            $existingProvider = auth_provider::where('provider', 'google')
                ->where('provider_user_id', $googleUser->getId())
                ->first();
                
            if ($existingProvider && $existingProvider->user_id !== $user->id) {
                 return redirect()->route('profile.settings')->with('error', 'Akun Google ini sudah tertaut dengan pengguna lain.');
            }

            if (!$existingProvider) {
                 auth_provider::create([
                     'user_id' => $user->id,
                     'provider' => 'google',
                     'provider_user_id' => $googleUser->getId(),
                 ]);
            }
            
            return redirect()->route('profile.settings')->with('success', 'Akun Google berhasil ditautkan.');
        }

        // --- GUEST LOGIN FLOW ---
        
        // Check if provider exists
        $provider = auth_provider::where('provider', 'google')
            ->where('provider_user_id', $googleUser->getId())
            ->first();

        if ($provider) {
            // Login the user associated with this provider
            $user = $provider->user;
            
            $updateData = ['last_login_at' => Carbon::now()];
            if (is_null($user->email_verified_at)) {
                $updateData['email_verified_at'] = Carbon::now();
            }
            $user->update($updateData);

            Auth::login($user);
            return redirect()->intended(route('Home', absolute: false))->with('success', 'Login Berhasil via Google!');
        }

        // Provider doesn't exist, check if email exists
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Auto-link the Google account since the emails match
            auth_provider::create([
                'user_id' => $user->id,
                'provider' => 'google',
                'provider_user_id' => $googleUser->getId(),
            ]);

            $updateData = ['last_login_at' => Carbon::now()];
            if (is_null($user->email_verified_at)) {
                $updateData['email_verified_at'] = Carbon::now();
            }
            $user->update($updateData);

            Auth::login($user);
            return redirect()->intended(route('Home', absolute: false))->with('success', 'Login Berhasil via Google!');
        }

        $avatarPath = null;
        if ($avatarUrl = $googleUser->getAvatar()) {
            try {
                $response = \Illuminate\Support\Facades\Http::get($avatarUrl);
                if ($response->successful()) {
                    $filename = 'profile_photos/' . uniqid('google_') . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $response->body());
                    $avatarPath = $filename;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mendownload foto dari Google: ' . $e->getMessage());
            }
        }

        // User doesn't exist, create a new user (with null password)
        $newUser = User::create([
            'name' => $googleUser->getName() ?? 'Google User',
            'email' => $googleUser->getEmail(),
            'password' => null,
            'email_verified_at' => Carbon::now(), // Google emails are already verified
            'role' => 'customer',
            'status' => 'active',
            'profile_photo' => $avatarPath,
            'last_login_at' => Carbon::now(),
        ]);

        auth_provider::create([
            'user_id' => $newUser->id,
            'provider' => 'google',
            'provider_user_id' => $googleUser->getId(),
        ]);

        Auth::login($newUser);

        return redirect()->intended(route('Home', absolute: false))->with('success', 'Pendaftaran Berhasil via Google!');
    }

    /**
     * Unlink Google account from the currently authenticated user.
     */
    public function unlink(Request $request)
    {
        $user = $request->user();

        // Check if password is set to prevent locking the user out
        if (is_null($user->password)) {
            return redirect()->back()->with('error', 'Anda tidak dapat memutuskan tautan Google karena Anda belum mengatur password. Silakan buat password di menu Keamanan terlebih dahulu.');
        }

        $user->providers()->where('provider', 'google')->delete();

        return redirect()->back()->with('success', 'Tautan akun Google berhasil diputuskan.');
    }
}
