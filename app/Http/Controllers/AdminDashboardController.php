<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\asset;
use App\Models\booking;
use App\Models\owner_profile;
use App\Models\payment;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan dashboard admin.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'totalUsers' => User::count(),
            'totalProperties' => asset::count(),
            'totalOwners' => owner_profile::count(),
            'pendingApprovals' => asset::where('status', 'pending')->count(),
            'pendingOwnerVerifications' => owner_profile::where('status', 'pending')->count(),
            'pendingBookings' => booking::where('booking_status', 'pending')->count(),
            'totalBookings' => booking::count(),
            'monthlyRevenue' => payment::where('payment_status', 'paid')
                ->whereBetween('payment_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->whereHas('booking')
                ->with('booking:id,total')
                ->get()
                ->sum(fn ($payment) => $payment->booking?->total ?? 0),
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

        $recentActivities = collect()
            ->merge(owner_profile::with('user')->latest()->limit(3)->get()->map(fn ($profile) => [
                'title' => 'Pengajuan Owner Baru',
                'desc' => ($profile->user?->name ?? 'Pengguna') . ' mengajukan verifikasi owner',
                'time' => $profile->created_at?->diffForHumans(),
                'type' => 'user',
                'status' => ucfirst($profile->status),
            ]))
            ->merge(asset::with('ownerProfile.user')->latest()->limit(3)->get()->map(fn ($asset) => [
                'title' => 'Listing Aset Baru',
                'desc' => $asset->title . ' — ' . ($asset->ownerProfile?->user?->name ?? 'Owner'),
                'time' => $asset->created_at?->diffForHumans(),
                'type' => 'property',
                'status' => $asset->status === 'pending' ? 'Review' : ucfirst($asset->status),
            ]))
            ->merge(booking::with('asset')->latest()->limit(3)->get()->map(fn ($booking) => [
                'title' => 'Booking Baru',
                'desc' => ($booking->asset?->title ?? 'Aset') . ' — ' . $booking->booking_code,
                'time' => $booking->created_at?->diffForHumans(),
                'type' => 'finance',
                'status' => ucfirst($booking->booking_status),
            ]))
            ->sortByDesc(fn ($activity) => $activity['time'])
            ->take(5)
            ->values();

        $quickActions = [
            ['title' => 'Validasi Identitas Pemilik', 'desc' => $stats['pendingOwnerVerifications'] . ' pengajuan menunggu verifikasi', 'route' => 'admin.pengajuan-akun', 'type' => 'user'],
            ['title' => 'Review Listing Properti', 'desc' => $stats['pendingApprovals'] . ' aset menunggu validasi', 'route' => 'admin.asset-validation', 'type' => 'property'],
            ['title' => 'Lihat Booking Pending', 'desc' => $stats['pendingBookings'] . ' booking menunggu proses', 'route' => 'admin.dashboard', 'type' => 'finance'],
        ];

        return Inertia::render('admin/dashboard', [
            'admin' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'recentActivities' => $recentActivities,
            'quickActions' => $quickActions,
        ]);
    }
}
