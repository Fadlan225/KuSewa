<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\owner_profile;

class OwnerDashboardController extends Controller
{
    /**
     * Menampilkan Dashboard khusus Owner Properti
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Mengambil profil owner berdasarkan user_id
        $ownerProfile = owner_profile::where('user_id', $user->id)->first();

        // Data statistik pendukung dashboard
        $stats = [
            'total_properti' => 8,
            'pesanan_aktif' => 14,
            'pending_verifikasi' => 3,
            'total_pendapatan' => 12400000,
        ];

        return Inertia::render('owner/dashboard', [
            'ownerProfile' => $ownerProfile,
            'stats' => $stats,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}