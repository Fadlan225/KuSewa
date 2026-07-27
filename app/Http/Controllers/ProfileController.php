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
     * Display the user's profile settings form.
     */
    public function settings(Request $request): Response
    {
        $user = $request->user()->load(['ownerProfile.bankAccounts']);

        $ownerProfile = null;
        $bankAccount = null;

        if ($user->ownerProfile) {
            $ownerProfile = $user->ownerProfile;
            $bankAccount = $ownerProfile->bankAccounts->first();
        }

        $photo = $user->profile_photo;
        $avatarUrl = null;
        if ($photo) {
            $avatarUrl = (filter_var($photo, FILTER_VALIDATE_URL)) ? $photo : asset('storage/' . $photo);
        }

        return Inertia::render('Profile/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $avatarUrl,
                'is_owner' => $user->role === 'admin' || $ownerProfile !== null,
            ],
            'owner_profile' => $ownerProfile ? [
                'national_id' => $ownerProfile->national_id,
                'address' => $ownerProfile->address,
                'place_of_birth' => $ownerProfile->place_of_birth,
                'date_of_birth' => $ownerProfile->date_of_birth,
                'status' => $ownerProfile->status,
            ] : null,
            'bank_account' => $bankAccount ? [
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_holder' => $bankAccount->account_holder,
            ] : null,
        ]);
    }

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

        // Bookings Count (Berlangsung): Bookings with status 'confirmed'/'accepted' AND payment is 'paid'/'success'
        $bookingsCount = $user->bookings()
            ->whereIn('booking_status', ['confirmed', 'accepted'])
            ->whereHas('payment', function ($query) {
                $query->whereIn('payment_status', ['paid', 'success']);
            })
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
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->ownerProfile) {
            $user->ownerProfile->update([
                'national_id' => $validated['national_id'] ?? $user->ownerProfile->national_id,
                'address' => $validated['address'] ?? $user->ownerProfile->address,
                'place_of_birth' => $validated['place_of_birth'] ?? $user->ownerProfile->place_of_birth,
                'date_of_birth' => $validated['date_of_birth'] ?? $user->ownerProfile->date_of_birth,
            ]);

            if (isset($validated['bank_name']) || isset($validated['account_number']) || isset($validated['account_holder'])) {
                $bankAccount = $user->ownerProfile->bankAccounts()->first();
                if ($bankAccount) {
                    $bankAccount->update([
                        'bank_name' => $validated['bank_name'] ?? $bankAccount->bank_name,
                        'account_number' => $validated['account_number'] ?? $bankAccount->account_number,
                        'account_holder' => $validated['account_holder'] ?? $bankAccount->account_holder,
                    ]);
                } else {
                    $user->ownerProfile->bankAccounts()->create([
                        'bank_name' => $validated['bank_name'],
                        'account_number' => $validated['account_number'],
                        'account_holder' => $validated['account_holder'],
                    ]);
                }
            }
        }

        return Redirect::route('profile.settings');
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->profile_photo && !filter_var($user->profile_photo, FILTER_VALIDATE_URL)) {
                // Delete old photo
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('photo')->store('profile_photos', 'public');
            $user->profile_photo = $path;
            $user->save();
        }

        return \Illuminate\Support\Facades\Redirect::back()->with('status', 'photo-updated');
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
