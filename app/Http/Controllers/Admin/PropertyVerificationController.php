<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyVerificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/properties/index', [
            'properties' => Property::with('user:id,name,email')
                ->where('verification_status', 'pending')
                ->latest()
                ->get()
                ->map(fn (Property $property) => [
                    'id' => $property->id,
                    'title' => $property->title,
                    'category' => $property->category,
                    'city' => $property->city,
                    'address' => $property->address,
                    'price' => (float) $property->price,
                    'rent_period' => $property->rent_period,
                    'owner' => $property->user?->only(['name', 'email']),
                    'created_at' => $property->created_at?->format('d M Y, H:i'),
                ]),
        ]);
    }

    public function approve(Property $property): RedirectResponse
    {
        $property->update([
            'verification_status' => 'approved',
            'verification_note' => null,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Properti telah disetujui dan dapat ditayangkan.');
    }

    public function reject(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'verification_note' => ['required', 'string', 'max:1000'],
        ]);

        $property->update([
            'verification_status' => 'rejected',
            'verification_note' => $validated['verification_note'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Properti ditolak dan catatan telah dikirim ke owner.');
    }
}
