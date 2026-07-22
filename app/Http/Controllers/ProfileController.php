<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        // Check if the user has an owner profile
        $isOwner = $user->ownerProfile()->exists();

        // Total Assets Rented: Bookings with status 'accepted'
        $totalAssetsRented = $user->bookings()
            ->where('booking_status', 'accepted')
            ->count();

        // Bookings Count: Bookings with status 'pending' or 'accepted'
        $bookingsCount = $user->bookings()
            ->whereIn('booking_status', ['pending', 'accepted'])
            ->count();

        // Unpaid Bookings Count: Bookings that are not rejected and have no payment or pending payment
        $unpaidBookingsCount = $user->bookings()
            ->where('booking_status', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function ($q) {
                        $q->where('payment_status', '!=', 'paid');
                    });
            })
            ->count();

        // Favorite Assets Count
        $favoriteAssetsCount = $user->favorites()->count();

        // Resolve avatar url
        $photo = $user->profile_photo;
        $avatarUrl = null;
        if ($photo) {
            $avatarUrl = (filter_var($photo, FILTER_VALIDATE_URL)) ? $photo : asset('storage/' . $photo);
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'avatar' => $avatarUrl,
                'profile_photo' => $avatarUrl,
                'is_owner' => $isOwner,
            ],
            'total_assets_rented' => $totalAssetsRented,
            'bookings_count' => $bookingsCount,
            'unpaid_bookings_count' => $unpaidBookingsCount,
            'favorite_assets_count' => $favoriteAssetsCount,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
