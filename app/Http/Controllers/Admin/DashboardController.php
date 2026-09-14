<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\asset;
use App\Models\owner_profile;
use App\Models\booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Pengguna
        $totalUsers = User::where('role', '!=', 'admin')->count();

        // 2. Total Listing Properti
        $totalProperties = asset::count();

        // 3. Menunggu Verifikasi
        $pendingApprovals = owner_profile::where('status', 'pending')->count();

        // 4. Omset Platform (Bulan Ini)
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
        $latestUsers = User::where('role', '!=', 'admin')->orderBy('created_at', 'desc')->take(3)->get()->map(function($user) {
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
            $firstPending = owner_profile::with('user')->where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->first();

            $moderationQueue[] = [
                'type'        => 'account_verification',
                'title'       => 'Validasi Identitas Pemilik',
                'description' => $pendingApprovals . ' akun menunggu verifikasi data diri.',
                'link'        => $firstPending
                    ? route('admin.pengajuan-akun.show', $firstPending->id)
                    : route('admin.pengajuan-akun'),
                'applicant'   => $firstPending ? [
                    'name'   => $firstPending->user->name ?? 'Pengguna',
                    'avatar' => $firstPending->user->avatar ?? null,
                    'gender' => $firstPending->user->gender ?? null,
                ] : null,
            ];
        }

        // GENERATE CHART DATA (Platform Growth)
        $days = 30;
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Helpers to get daily counts
        $getDailyCounts = function($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date')->toArray();
        };

        $dailyUsers = $getDailyCounts(User::where('role', '!=', 'admin'));
        $dailyOwners = $getDailyCounts(owner_profile::query());
        $dailyAssets = $getDailyCounts(asset::query());
        $dailyBookings = $getDailyCounts(booking::query());

        // Revenue requires sum
        $dailyRevenue = booking::whereIn('booking_status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(service_fee) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        // GENERATE MONITORING DATA (Device, Browser, OS)
        $monitoringLogs = DB::table('asset_view_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), 'os', 'browser')
            ->get();

        $platformGrowthChart = [];
        $monitoringChart = [];
        
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            
            // Platform Growth
            $platformGrowthChart[] = [
                'date' => $dateStr,
                'users' => $dailyUsers[$dateStr] ?? 0,
                'owners' => $dailyOwners[$dateStr] ?? 0,
                'assets' => $dailyAssets[$dateStr] ?? 0,
                'bookings' => $dailyBookings[$dateStr] ?? 0,
                'revenue' => (int) ($dailyRevenue[$dateStr] ?? 0),
            ];

            // Monitoring Default Initialize
            $dayMonitoring = [
                'date' => $dateStr,
                'desktop' => 0, 'mobile' => 0, 'tablet' => 0,
                'chrome' => 0, 'safari' => 0, 'edge' => 0, 'firefox' => 0, 'opera' => 0, 'other_browser' => 0,
                'windows' => 0, 'macos' => 0, 'linux' => 0, 'android' => 0, 'ios' => 0, 'other_os' => 0,
            ];
            $monitoringChart[$dateStr] = $dayMonitoring;
        }

        // Aggregate Monitoring Data
        foreach ($monitoringLogs as $log) {
            $dateStr = $log->date;
            if (!isset($monitoringChart[$dateStr])) continue;

            $os = strtolower($log->os ?? 'other');
            $browser = strtolower($log->browser ?? 'other');
            
            // Device category inference based on OS (simple)
            if (in_array($os, ['android', 'ios'])) {
                $monitoringChart[$dateStr]['mobile']++;
            } else if (in_array($os, ['windows', 'macos', 'linux', 'mac os'])) {
                $monitoringChart[$dateStr]['desktop']++;
            } else {
                $monitoringChart[$dateStr]['mobile']++; // Defaulting unknown mostly to mobile
            }

            // OS category
            if (str_contains($os, 'win')) $monitoringChart[$dateStr]['windows']++;
            else if (str_contains($os, 'mac')) $monitoringChart[$dateStr]['macos']++;
            else if (str_contains($os, 'linux')) $monitoringChart[$dateStr]['linux']++;
            else if (str_contains($os, 'android')) $monitoringChart[$dateStr]['android']++;
            else if (str_contains($os, 'ios')) $monitoringChart[$dateStr]['ios']++;
            else $monitoringChart[$dateStr]['other_os']++;

            // Browser category
            if (str_contains($browser, 'chrome')) $monitoringChart[$dateStr]['chrome']++;
            else if (str_contains($browser, 'safari')) $monitoringChart[$dateStr]['safari']++;
            else if (str_contains($browser, 'edge')) $monitoringChart[$dateStr]['edge']++;
            else if (str_contains($browser, 'firefox')) $monitoringChart[$dateStr]['firefox']++;
            else if (str_contains($browser, 'opera') || str_contains($browser, 'opr')) $monitoringChart[$dateStr]['opera']++;
            else $monitoringChart[$dateStr]['other_browser']++;
        }

        return Inertia::render('admin/index', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalProperties' => $totalProperties,
                'pendingApprovals' => $pendingApprovals,
                'monthlyRevenue' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.')
            ],
            'bookingStats' => $bookingStats,
            'recentActivities' => $recentActivities,
            'quickActions' => $moderationQueue,
            'chartData' => [
                'platformGrowth' => $platformGrowthChart,
                'monitoring' => array_values($monitoringChart),
            ]
        ]);
    }
}
