<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\asset;
use App\Models\OwnerBilling;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerBillingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'Semua');

        // Mengambil semua user yang memiliki profil owner beserta profil
        $owners = User::whereHas('ownerProfile')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with([
                'ownerProfile',
            ])
            ->get();

        // Mengambil jumlah aset aktif per owner_profile_id
        $ownerProfileIds = $owners->pluck('ownerProfile.id')->filter();
        $assetCounts = asset::whereIn('owner_profile_id', $ownerProfileIds)
            ->whereNull('deleted_at')
            ->selectRaw('owner_profile_id, count(*) as count')
            ->groupBy('owner_profile_id')
            ->pluck('count', 'owner_profile_id');

        // Mengambil semua billings untuk owner-owner tersebut
        $ownerIds = $owners->pluck('id');
        $billings = OwnerBilling::whereIn('owner_id', $ownerIds)
            ->whereIn('status', ['unpaid', 'waiting_verification', 'overdue'])
            ->get()
            ->groupBy('owner_id');

        // Format data untuk frontend
        $mappedOwners = $owners->map(function ($owner) use ($billings, $assetCounts) {
            $ownerBillings = $billings->get($owner->id, collect());
            
            $totalUnpaid = $ownerBillings->sum('total_amount');
            $hasOverdue = $ownerBillings->where('status', 'overdue')->count() > 0;
            $hasWaiting = $ownerBillings->where('status', 'waiting_verification')->count() > 0;
            
            if ($hasOverdue) {
                $status = 'Menunggak';
                $priority = 1;
            } elseif ($hasWaiting) {
                $status = 'Menunggu Verifikasi';
                $priority = 2;
            } else {
                $status = 'Lancar';
                $priority = 3;
            }

            $propertiesCount = $owner->ownerProfile ? ($assetCounts[$owner->ownerProfile->id] ?? 0) : 0;

            return [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'avatar' => $owner->avatar,
                'properties' => $propertiesCount,
                'account_status' => $owner->status,
                'billing_status' => $status,
                'total_unpaid' => $totalUnpaid,
                'priority' => $priority,
            ];
        });

        // Filter berdasarkan status
        if ($statusFilter !== 'Semua') {
            $mappedOwners = $mappedOwners->filter(function ($owner) use ($statusFilter) {
                if ($statusFilter === 'Menunggak') return $owner['billing_status'] === 'Menunggak';
                if ($statusFilter === 'Menunggu Verifikasi') return $owner['billing_status'] === 'Menunggu Verifikasi';
                if ($statusFilter === 'Lancar') return $owner['billing_status'] === 'Lancar';
                return true;
            });
        }

        // Sorting: Prioritas (Menunggak -> Menunggu -> Lancar) lalu berdasarkan nama
        $mappedOwners = $mappedOwners->sortBy([
            ['priority', 'asc'],
            ['name', 'asc'],
        ])->values();

        // Pagination
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $currentItems = $mappedOwners->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedOwners = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $mappedOwners->count(), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        // Statistik
        $totals = [
            'all' => $owners->count(),
            'active' => $owners->where('status', 'active')->count(),
            'suspended' => $owners->where('status', '!=', 'active')->count(),
        ];

        return Inertia::render('admin/ServiceFeeSanksi', [
            'owners' => $paginatedOwners,
            'totals' => $totals,
            'filters' => ['search' => $search, 'status' => $statusFilter]
        ]);
    }
}
