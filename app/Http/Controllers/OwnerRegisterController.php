<?php

namespace App\Http\Controllers;

use App\Models\owner_profile;
use App\Models\User;
use App\Notifications\VerifyOwnerEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OwnerRegisterController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        $user = Auth::user();

        // Jika user sudah terverifikasi sebagai owner
        if ($user->ownerProfile && $user->ownerProfile->status === 'verified') {
            return redirect()->route('owner.dashboard');
        }

        return Inertia::render('owner/register', [
            'hasPendingProfile' => $user->ownerProfile ? true : false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->ownerProfile && $user->ownerProfile->status === 'verified') {
            return redirect()->route('owner.dashboard');
        }

        $validated = $request->validate([
            'national_id' => 'required|numeric|digits:16',
            'place_of_birth' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:500',
            'ktp_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $ktpPath = null;
        if ($request->hasFile('ktp_photo')) {
            $ktpPath = $request->file('ktp_photo')->store('ktp_documents', 'public');
        }

        // Simpan / update profil owner dengan status pending
        owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'national_id' => $validated['national_id'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'ktp_photo' => $ktpPath,
                'status' => 'pending',
            ]
        );

        // Kirim email konfirmasi
        $user->notify(new VerifyOwnerEmail());

        return redirect()->back()->with('success', 'Pengajuan berhasil dikirim! Silakan cek inbox email kamu untuk melakukan konfirmasi.');
    }

    /**
     * Verifikasi link yang diklik user dari email
     */
    public function verify(Request $request, $id): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('owner.register')->with('error', 'Link konfirmasi tidak valid atau sudah kadaluwarsa.');
        }

        $user = User::findOrFail($id);
        $ownerProfile = owner_profile::where('user_id', $user->id)->first();

        if ($ownerProfile) {
            $ownerProfile->update([
                'status' => 'verified',
                'verification_at' => now(),
            ]);

            // Update role user menjadi owner setelah konfirmasi email
            $user->update(['role' => 'owner']);
        }

        return redirect()->route('owner.dashboard')->with('success', 'Email berhasil dikonfirmasi! Selamat datang di Dashboard Owner.');
    }
}