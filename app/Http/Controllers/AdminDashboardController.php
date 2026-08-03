<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\asset;
use App\Models\booking;
use App\Models\owner_profile;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan dashboard admin.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_users' => User::count(),
            'total_owners' => owner_profile::count(),
            'total_assets' => asset::count(),
            'total_bookings' => booking::count(),
            'pending_owner_verifications' => owner_profile::where('status', 'pending')->count(),
            'pending_bookings' => booking::where('booking_status', 'pending')->count(),
            'total_revenue' => booking::whereIn('booking_status', ['confirmed', 'completed'])->sum('total'),
        ];

        $recentBookings = booking::with('asset')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'code' => $booking->booking_code,
                    'asset' => $booking->asset?->title ?? 'Aset tidak diketahui',
                    'status' => ucfirst($booking->booking_status),
                    'total' => $booking->total,
                ];
            });

        return Inertia::render('admin/dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => $stats,
            'recentBookings' => $recentBookings,
        ]);
    }
}
