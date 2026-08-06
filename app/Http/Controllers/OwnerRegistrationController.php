<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\owner_profile;
use App\Http\Requests\StoreOwnerRegistrationStep1Request;
use App\Http\Requests\StoreOwnerRegistrationStep2Request;
use App\Http\Requests\StoreOwnerRegistrationStep3Request;
use Illuminate\Support\Facades\Storage;

class OwnerRegistrationController extends Controller
{
    /**
     * Menampilkan halaman registrasi
     */
    public function index()
    {
        $user = auth()->user();
        
        // Ambil data profile jika ada
        $ownerProfile = $user->ownerProfile;

        // Tentukan initial step
        // Jika status != pending/null, mungkin redirect atau show message?
        if ($ownerProfile && $ownerProfile->status !== 'rejected' && $ownerProfile->status !== 'pending' && $ownerProfile->verification_at !== null) {
            return redirect()->route('owner.verification');
        }

        return Inertia::render('Auth/OwnerRegistration', [
            'initialUser' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'place_of_birth_code' => $user->place_of_birth_code,
                'date_of_birth' => $user->date_of_birth,
            ],
            'initialProfile' => $ownerProfile ? [
                'national_id' => $ownerProfile->national_id,
                'province_code' => $ownerProfile->province_code,
                'city_code' => $ownerProfile->city_code,
                'district_code' => $ownerProfile->district_code,
                'village_code' => $ownerProfile->village_code,
                'postal_code' => $ownerProfile->postal_code,
                'address' => $ownerProfile->address,
            ] : null,
            // Jika sudah ada status, tapi form ini bisa jadi draft.
        ]);
    }

    /**
     * Validasi dan simpan Step 1
     */
    public function storeStep1(StoreOwnerRegistrationStep1Request $request)
    {
        $user = auth()->user();
        
        // Update tabel users
        $user->update($request->only([
            'name', 
            'email', 
            'phone', 
            'gender', 
            'place_of_birth_code', 
            'date_of_birth'
        ]));

        // NIK (national_id) masuk ke owner_profiles sesuai plan
        if ($request->has('national_id')) {
            owner_profile::updateOrCreate(
                ['user_id' => $user->id],
                ['national_id' => $request->national_id]
            );
        }

        return back()->with('success', 'Data diri berhasil disimpan.');
    }

    /**
     * Validasi dan simpan Step 2
     */
    public function storeStep2(StoreOwnerRegistrationStep2Request $request)
    {
        $user = auth()->user();

        owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return back()->with('success', 'Alamat berhasil disimpan.');
    }

    /**
     * Validasi dan simpan Step 3 (Submit Verifikasi)
     */
    public function storeStep3(StoreOwnerRegistrationStep3Request $request)
    {
        $user = auth()->user();
        
        // Handle upload
        $path = $request->file('ktp_photo')->store('public/owner/ktp');
        
        $ownerProfile = owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ktp_photo' => $path,
                'status' => 'pending',
                'verification_at' => null,
            ]
        );

        // Jangan pakai alert/toast seperti di instruksi, langsung redirect
        return redirect()->route('owner.verification');
    }

    /**
     * Tampilkan Halaman Status Verifikasi
     */
    public function verificationStatus()
    {
        $user = auth()->user();
        $ownerProfile = $user->ownerProfile;

        if (!$ownerProfile) {
            return redirect()->route('owner.register');
        }

        return Inertia::render('Auth/OwnerVerificationStatus', [
            'status' => $ownerProfile->status,
        ]);
    }
}
