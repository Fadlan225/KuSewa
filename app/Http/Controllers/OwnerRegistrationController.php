<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\owner_profile;
use App\Http\Requests\ProcessKtpOcrRequest;
use App\Http\Requests\StoreOwnerDataRequest;
use App\Http\Requests\StoreOwnerRegistrationStep1Request;
use App\Http\Requests\StoreOwnerRegistrationStep2Request;
use App\Http\Requests\StoreOwnerRegistrationStep3Request;
use App\Jobs\ProcessKtpOcrJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OwnerRegistrationController extends Controller
{
    /**
     * Menampilkan halaman registrasi owner (1 step baru dengan OCR).
     */
    public function index()
    {
        $user = auth()->user();
        $ownerProfile = $user->ownerProfile;

        if ($ownerProfile && $ownerProfile->status !== 'rejected' && $ownerProfile->status !== 'pending' && $ownerProfile->verification_at !== null) {
            return redirect()->route('owner.verification');
        }

        return Inertia::render('Auth/OwnerRegistration', [
            'initialUser' => [
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'gender'             => $user->gender,
                'place_of_birth_code' => $user->place_of_birth_code,
                'date_of_birth'      => $user->date_of_birth,
            ],
            'initialProfile' => $ownerProfile ? [
                'national_id'     => $ownerProfile->national_id,
                'province_code'   => $ownerProfile->province_code,
                'city_code'       => $ownerProfile->city_code,
                'district_code'   => $ownerProfile->district_code,
                'village_code'    => $ownerProfile->village_code,
                'postal_code'     => $ownerProfile->postal_code,
                'address'         => $ownerProfile->address,
                'religion'        => $ownerProfile->religion,
                'marital_status'  => $ownerProfile->marital_status,
                'occupation'      => $ownerProfile->occupation,
                'nationality'     => $ownerProfile->nationality,
                'has_ktp_photo'   => !empty($ownerProfile->ktp_photo),
            ] : null,
        ]);
    }

    /**
     * Menampilkan halaman registrasi owner via OCR instan.
     */
    public function instantIndex()
    {
        $user = auth()->user();
        $ownerProfile = $user->ownerProfile;

        if ($ownerProfile && $ownerProfile->status !== 'rejected' && $ownerProfile->status !== 'pending' && $ownerProfile->verification_at !== null) {
            return redirect()->route('owner.verification');
        }

        return Inertia::render('Auth/OwnerOcrRegistration', [
            'initialUser' => [
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'gender'             => $user->gender,
                'place_of_birth_code' => $user->place_of_birth_code,
                'date_of_birth'      => $user->date_of_birth,
            ],
            'initialProfile' => $ownerProfile ? [
                'national_id'     => $ownerProfile->national_id,
                'province_code'   => $ownerProfile->province_code,
                'city_code'       => $ownerProfile->city_code,
                'district_code'   => $ownerProfile->district_code,
                'village_code'    => $ownerProfile->village_code,
                'postal_code'     => $ownerProfile->postal_code,
                'address'         => $ownerProfile->address,
                'religion'        => $ownerProfile->religion,
                'marital_status'  => $ownerProfile->marital_status,
                'occupation'      => $ownerProfile->occupation,
                'nationality'     => $ownerProfile->nationality,
                'has_ktp_photo'   => !empty($ownerProfile->ktp_photo),
            ] : null,
        ]);
    }

    // =========================================================================
    // NEW: KTP OCR Flow (1 step)
    // =========================================================================

    /**
     * Upload KTP dan dispatch OCR job.
     * Return job_id untuk polling status.
     */
    public function uploadKtp(ProcessKtpOcrRequest $request)
    {
        $user = auth()->user();

        // Store ke private storage dengan random filename
        $file      = $request->file('ktp_photo');
        $ext       = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $ext;
        $tempPath  = 'owner/ktp_temp/' . $filename;

        Storage::disk('local')->put($tempPath, file_get_contents($file->getRealPath()));

        // Dispatch ke named queue 'ocr'
        $jobId = (string) Str::uuid();

        ProcessKtpOcrJob::dispatch($tempPath, $jobId, $user->id);

        Log::info("OCR job dispatched for user #{$user->id}", ['job_id' => $jobId]);

        // Jika menggunakan sync driver (dev), job sudah selesai — langsung return hasil
        if (config('queue.default') === 'sync') {
            $cacheKey = "ocr_result_{$jobId}";
            $result   = Cache::get($cacheKey);

            if ($result) {
                return response()->json([
                    'success'        => true,
                    'status'         => $result['status'],
                    'job_id'         => $jobId,
                    'data'           => $result['data']           ?? null,
                    'errors'         => $result['errors']         ?? null,
                    'partial'        => $result['partial']        ?? false,
                    'ktp_photo_path' => $result['ktp_photo_path'] ?? null,
                    'sync'           => true, // flag ke frontend agar skip polling
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'status'  => 'processing',
            'job_id'  => $jobId,
        ]);
    }

    /**
     * Polling status OCR job.
     */
    public function pollOcrStatus(Request $request, string $jobId)
    {
        // Sanitasi jobId (UUID format only)
        if (!preg_match('/^[0-9a-f\-]{36}$/i', $jobId)) {
            return response()->json(['success' => false, 'status' => 'failed'], 400);
        }

        $cacheKey = "ocr_result_{$jobId}";
        $result   = Cache::get($cacheKey);

        if (!$result) {
            // Job belum selesai atau sudah expired
            return response()->json([
                'success' => true,
                'status'  => 'processing',
            ]);
        }

        return response()->json([
            'success' => true,
            'status'  => $result['status'],
            'data'    => $result['status'] === 'completed' ? $result['data']    : null,
            'errors'  => $result['status'] === 'failed'    ? $result['errors']  : null,
            'partial' => $result['partial'] ?? false,
            'ktp_photo_path' => $result['ktp_photo_path'] ?? null,
        ]);
    }

    /**
     * Submit final: simpan data user + owner_profile + pindahkan KTP ke storage final.
     */
    public function storeOwnerData(StoreOwnerDataRequest $request)
    {
        $user = auth()->user();

        // --- Simpan data user ---
        $user->update([
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'gender'             => $request->gender,
            'place_of_birth_code' => $request->place_of_birth_code,
            'date_of_birth'      => $request->date_of_birth,
        ]);

        // --- Pindahkan KTP dari temp ke final path ---
        $ktpPhotoPath = null;
        $tempPath     = $request->ktp_temp_path;

        if ($tempPath && Storage::disk('local')->exists($tempPath)) {
            $ext          = pathinfo($tempPath, PATHINFO_EXTENSION);
            $finalPath    = 'owner/ktp/' . $user->id . '_' . Str::uuid() . '.' . $ext;
            Storage::disk('local')->move($tempPath, $finalPath);
            $ktpPhotoPath = $finalPath;
        }

        // --- Simpan/update owner_profile ---
        owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'national_id'    => $request->national_id,
                'religion'       => $request->religion,
                'marital_status' => $request->marital_status,
                'occupation'     => $request->occupation,
                'nationality'    => $request->nationality ?? 'WNI',
                'province_code'  => $request->province_code,
                'city_code'      => $request->city_code,
                'district_code'  => $request->district_code,
                'village_code'   => $request->village_code,
                'postal_code'    => $request->postal_code,
                'address'        => $request->address,
                'ktp_photo'      => $ktpPhotoPath,
                'status'         => 'pending',
                'verification_at' => null,
            ]
        );

        return redirect()->route('owner.verification');
    }

    // =========================================================================
    // LEGACY: Step 1/2/3 — dipertahankan untuk backward compatibility
    // =========================================================================

    /**
     * @deprecated Gunakan uploadKtp + storeOwnerData (flow OCR baru)
     */
    public function storeStep1(StoreOwnerRegistrationStep1Request $request)
    {
        $user = auth()->user();

        $user->update($request->only([
            'name',
            'email',
            'phone',
            'gender',
            'place_of_birth_code',
            'date_of_birth'
        ]));

        if ($request->has('national_id')) {
            owner_profile::updateOrCreate(
                ['user_id' => $user->id],
                ['national_id' => $request->national_id]
            );
        }

        return back();
    }

    /**
     * @deprecated Gunakan storeOwnerData (flow OCR baru)
     */
    public function storeStep2(StoreOwnerRegistrationStep2Request $request)
    {
        $user = auth()->user();

        owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return back();
    }

    /**
     * @deprecated Gunakan storeOwnerData (flow OCR baru)
     */
    public function storeStep3(StoreOwnerRegistrationStep3Request $request)
    {
        $user = auth()->user();

        $data = [
            'status'          => 'pending',
            'verification_at' => null,
        ];

        if ($request->hasFile('ktp_photo')) {
            $file     = $request->file('ktp_photo');
            $ext      = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $ext;
            $path     = $file->storeAs('owner/ktp', $filename, 'local');
            $data['ktp_photo'] = $path;
        }

        owner_profile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return redirect()->route('owner.verification');
    }

    /**
     * Tampilkan Halaman Status Verifikasi.
     */
    public function verificationStatus()
    {
        $user         = auth()->user();
        $ownerProfile = $user->ownerProfile;

        if (!$ownerProfile) {
            return redirect()->route('owner.register');
        }

        return Inertia::render('Auth/OwnerVerificationStatus', [
            'status'          => $ownerProfile->status,
            'createdAt'       => $ownerProfile->created_at ? $ownerProfile->created_at->translatedFormat('d F Y') : null,
            'rejectionReason' => $ownerProfile->rejection_reason,
        ]);
    }
}
