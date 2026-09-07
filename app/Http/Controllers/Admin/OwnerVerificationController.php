<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\owner_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OwnerVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = owner_profile::with('user')
            ->when($request->status && $request->status !== 'Semua', fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q
                ->where('national_id', 'like', "%{$request->search}%")
                ->orWhereHas('user', fn($q2) => $q2
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                )
            )
            ->orderByRaw("FIELD(status, 'pending', 'verified', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $query->getCollection()->transform(function ($p) {
            // Gunakan route admin terproteksi agar file dari local disk bisa tampil
            $p->ktp_url = $p->ktp_photo
                ? route('admin.ktp-photo', $p->id)
                : null;
            return $p;
        });

        $stats = [
            'pending'  => owner_profile::where('status', 'pending')->count(),
            'verified' => owner_profile::where('status', 'verified')->count(),
            'rejected' => owner_profile::where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/KelolaPengajuanAkun', [
            'applicants' => $query,
            'stats'      => $stats,
            'filters'    => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Serve foto KTP dari local disk secara aman (hanya admin).
     */
    public function serveKtpPhoto($id)
    {
        $profile = owner_profile::findOrFail($id);

        if (!$profile->ktp_photo || !Storage::disk('local')->exists($profile->ktp_photo)) {
            abort(404, 'Foto KTP tidak ditemukan.');
        }

        $path      = Storage::disk('local')->path($profile->ktp_photo);
        $mimeType  = mime_content_type($path) ?: 'image/jpeg';

        return response()->file($path, [
            'Content-Type'        => $mimeType,
            'Cache-Control'       => 'private, max-age=3600',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Setujui pengajuan owner.
     */
    public function approve($id)
    {
        $profile = owner_profile::findOrFail($id);

        $profile->update([
            'status'          => 'verified',
            'rejection_reason' => null,
            'verification_at' => now(),
        ]);

        return back()->with('success', "Pengajuan {$profile->user->name} telah disetujui.");
    }

    /**
     * Tolak pengajuan owner dengan alasan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $profile = owner_profile::findOrFail($id);

        $profile->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'verification_at'  => now(),
        ]);

        return back()->with('success', "Pengajuan {$profile->user->name} telah ditolak.");
    }
}
