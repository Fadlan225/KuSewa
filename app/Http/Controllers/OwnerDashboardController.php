<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\booking;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Data dari Property model
        $properties = Property::where('user_id', $user->id)->get();

        // Total unit
        $totalUnit = $properties->count();

        // Sedang tersewa
        $totalTersewa = $properties->where('status', 'Tersewa')->count();

        // Siap disewakan
        $totalTersedia = $properties->where('status', 'Tersedia')->count();

        // Menunggu verifikasi
        $totalPendingVerifikasi = $properties->where('verification_status', 'pending')->count();

        // Ditolak
        $totalDitolak = $properties->where('verification_status', 'rejected')->count();

        // Terverifikasi
        $totalTerverifikasi = $properties->where('verification_status', 'approved')->count();

        // Booking pending (perlu konfirmasi)
        $bookingPending = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
            ->where('booking_status', 'pending')
            ->count();

        // Booking aktif (confirmed)
        $bookingAktif = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
            ->where('booking_status', 'confirmed')
            ->count();

        // Booking selesai bulan ini
        $bookingSelesaiBulanIni = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
            ->where('booking_status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Pendapatan bulan ini (dari booking completed)
        $pendapatanBulanIni = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
            ->where('booking_status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        // Total pendapatan keseluruhan
        $totalPendapatan = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
            ->where('booking_status', 'completed')
            ->sum('total');

        // Data kota untuk chart
        $kotaData = $properties->groupBy('city')->map(fn($items) => $items->count())->toArray();

        // Data 6 bulan terakhir untuk chart pendapatan
        $monthlyIncome = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $income = booking::whereHas('asset', fn($q) => $q->where('owner_profile_id', $user->ownerProfile?->id))
                ->where('booking_status', 'completed')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('total');
            $monthlyIncome[] = [
                'month' => $month->translatedFormat('M'),
                'income' => (float) $income,
            ];
        }

        return Inertia::render('owner/dashboard', [
            'stats' => [
                'totalUnit' => $totalUnit,
                'totalTersewa' => $totalTersewa,
                'totalTersedia' => $totalTersedia,
                'totalPendingVerifikasi' => $totalPendingVerifikasi,
                'totalDitolak' => $totalDitolak,
                'totalTerverifikasi' => $totalTerverifikasi,
                'bookingPending' => $bookingPending,
                'bookingAktif' => $bookingAktif,
                'bookingSelesaiBulanIni' => $bookingSelesaiBulanIni,
                'pendapatanBulanIni' => (float) $pendapatanBulanIni,
                'totalPendapatan' => (float) $totalPendapatan,
                'kotaData' => $kotaData,
                'monthlyIncome' => $monthlyIncome,
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}