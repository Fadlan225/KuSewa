<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\asset;
use App\Models\owner_profile;
use App\Models\booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Pengguna
        $totalUsers = User::count();

        // 2. Total Listing Properti
        $totalProperties = asset::count();

        // 3. Menunggu Verifikasi
        $pendingApprovals = owner_profile::where('status', 'pending')->count();

        // 4. Omset Platform (Bulan Ini) -> menggunakan total dari service_fee dari booking yang sudah dibayar (completed/confirmed)
        $monthlyRevenue = booking::whereIn('booking_status', ['confirmed', 'completed'])
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->sum('service_fee');

        // Statistik Booking
        $totalBookings = booking::count();
        $pendingBookings = booking::where('booking_status', 'pending')->count();
        $activeBookings = booking::whereIn('booking_status', ['confirmed', 'active'])->count();
        $completedBookings = booking::where('booking_status', 'completed')->count();
        
        $bookingStats = [
            'total' => $totalBookings,
            'pending' => $pendingBookings,
            'active' => $activeBookings,
            'completed' => $completedBookings,
        ];

        // Aktivitas Terbaru (Gabungan dari user baru & booking terbaru)
        $latestUsers = User::orderBy('created_at', 'desc')->take(3)->get()->map(function($user) {
            return [
                'type' => 'user',
                'title' => 'Pengguna Baru Terdaftar',
                'description' => $user->name . ' mendaftar ke platform.',
                'time' => $user->created_at->diffForHumans(),
                'avatar' => $user->avatar,
                'name' => $user->name,
                'gender' => $user->gender,
            ];
        });

        $latestBookings = booking::with('user')->orderBy('created_at', 'desc')->take(3)->get()->map(function($booking) {
            return [
                'type' => 'booking',
                'title' => 'Transaksi Baru (' . $booking->booking_code . ')',
                'description' => ($booking->user->name ?? 'Seseorang') . ' melakukan pemesanan sebesar Rp ' . number_format($booking->total, 0, ',', '.'),
                'time' => $booking->created_at->diffForHumans(),
                'avatar' => $booking->user ? $booking->user->avatar : null,
                'name' => $booking->user ? $booking->user->name : 'Seseorang',
                'gender' => $booking->user ? $booking->user->gender : null,
            ];
        });

        $recentActivities = collect($latestUsers)->merge($latestBookings)->sortByDesc('time')->take(5)->values()->all();

        // Aksi Moderasi Cepat
        $moderationQueue = [];
        if ($pendingApprovals > 0) {
            $moderationQueue[] = [
                'title' => 'Validasi Identitas Pemilik',
                'description' => $pendingApprovals . ' akun menunggu verifikasi data diri.',
                'link' => route('admin.account-management')
            ];
        }

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalProperties' => $totalProperties,
                'pendingApprovals' => $pendingApprovals,
                'monthlyRevenue' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.')
            ],
            'bookingStats' => $bookingStats,
            'recentActivities' => $recentActivities,
            'quickActions' => $moderationQueue
        ]);
    }
}
