<?php

namespace App\Http\Controllers;

use App\Models\owner_profile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOwnerValidationController extends Controller
{
    public function index(Request $request)
    {
        $pendingOwners = owner_profile::with('user')
            ->where('status', 'pending')
            ->latest('created_at')
            ->get()
            ->map(function ($owner) {
                return [
                    'id' => $owner->id,
                    'name' => optional($owner->user)->name ?? '-',
                    'email' => optional($owner->user)->email ?? '-',
                    'national_id' => $owner->national_id,
                    'place_of_birth' => $owner->place_of_birth,
                    'date_of_birth' => optional($owner->date_of_birth)?->format('Y-m-d'),
                    'status' => $owner->status,
                    'submitted_at' => $owner->created_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('admin/owner-validation', [
            'pendingOwners' => $pendingOwners,
            'pendingCount' => $pendingOwners->count(),
        ]);
    }
}
