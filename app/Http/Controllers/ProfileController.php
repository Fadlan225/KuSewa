<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\booking;
use App\Models\search_log;
use App\Models\AssetView;
use App\Models\review;
use App\Models\asset_category;
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

        // Total Assets Rented for Sidebar
        $totalAssetsRented = $user->bookings()
            ->where('booking_status', 'accepted')
            ->count();

        return Inertia::render('Profile/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'place_of_birth_code' => $user->place_of_birth_code,
                'gender' => $user->gender,
                'avatar' => $avatarUrl,
                'is_owner' => $user->role === 'admin' || $ownerProfile !== null,
                'is_google_linked' => $user->providers()->where('provider', 'google')->exists(),
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
            'total_assets_rented' => $totalAssetsRented,
        ]);
    }

    /**
     * Display the user's security form (Mobile only).
     */
    public function security(Request $request): Response
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

        // Total Assets Rented for Sidebar
        $totalAssetsRented = $user->bookings()
            ->where('booking_status', 'accepted')
            ->count();

        return Inertia::render('Profile/Security', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'place_of_birth_code' => $user->place_of_birth_code,
                'gender' => $user->gender,
                'avatar' => $avatarUrl,
                'is_owner' => $user->role === 'admin' || $ownerProfile !== null,
                'is_google_linked' => $user->providers()->where('provider', 'google')->exists(),
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
            'total_assets_rented' => $totalAssetsRented,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['ownerProfile.bankAccounts']);
        
        // Check if the user has an owner profile
        $isOwner = $user->ownerProfile !== null;

        $ownerProfile = $user->ownerProfile;
        $bankAccount = $ownerProfile ? $ownerProfile->bankAccounts->first() : null;

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

        // Unpaid Bookings Count: Bookings that are confirmed and need payment
        $unpaidBookingsCount = $user->bookings()
            ->where('booking_status', 'confirmed')
            ->where(function ($query) {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function ($q) {
                        $q->where('payment_status', 'pending');
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

                $tab = $request->query('tab', 'profil');
        
        $data = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'date_of_birth' => $user->date_of_birth,
                'place_of_birth_code' => $user->place_of_birth_code,
                'gender' => $user->gender,
                'avatar' => $avatarUrl,
                'profile_photo' => $avatarUrl,
                'is_owner' => $isOwner || $user->role === 'admin',
                'is_google_linked' => $user->providers()->where('provider', 'google')->exists(),
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
            'total_assets_rented' => $totalAssetsRented,
            'bookings_count' => $bookingsCount,
            'unpaid_bookings_count' => $unpaidBookingsCount,
            'favorite_assets_count' => $favoriteAssetsCount,
            'tab' => $tab,
        ];

        $userId = $user->id;

        if ($tab === 'transaksi') {
            $data['bookings'] = booking::with([
                "asset" => function($q) use ($userId) {
                    $q->with(['favorites' => function($f) use ($userId) {
                        $f->where('user_id', $userId);
                    }]);
                },
                "asset.firstImage",
                "asset.type.category",
                "payment",
                "reviews"
            ])->where("user_id", $userId)->orderBy("id", "desc")->get();
        } elseif ($tab === 'terakhir-dilihat') {
            $data['lastSeen'] = AssetView::with(['asset.firstImage', 'asset.type.category', 'asset.defaultPricing'])
                ->where('user_id', $userId)->orderBy('last_viewed', 'desc')->paginate(24);
        } elseif ($tab === 'pencarian') {
            $data['searchLogs'] = search_log::where('user_id', $userId)->orderBy('searched_at', 'desc')->paginate(15);
        } elseif ($tab === 'ulasan') {
            $data['reviews'] = review::with(['booking.asset.firstImage', 'booking.asset.type.category', 'items.reviewTag'])
                ->where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(15);
        } elseif ($tab === 'favorit') {
            $favorites = $user->favorites()->with(['asset' => function ($query) {
                $query->select([
                    'id', 'asset_type_id', 'owner_profile_id',
                    'title', 'city_code', 'district_code', 'address', 'status', 'detail'
                ])->with([
                    'city:code,name',
                    'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                    'defaultPricing:id,asset_id,price,rental_unit',
                    'type:id,name,allow_units,category_id',
                    'type.category:id,name,icon',
                ])
                ->withAvg('reviews as reviews_avg_rating', 'rating')
                ->withCount('reviews');
            }])->latest()->get();
            
            $data['initialFavorites'] = $favorites->map(function ($fav) {
                $asset = $fav->asset;
                if (!$asset) return null;
                
                $asset->city_name = $asset->city->name ?? '';
                $asset->isFavorite = true;
                $asset->favorite_id = $fav->id;
                
                return $asset;
            })->filter()->values();
            
            $data['categoriesList'] = collect(['Semua'])->merge(asset_category::pluck('name'))->values();
        }

        return Inertia::render('Profile/Edit', $data);
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
            'phone' => array_key_exists('phone', $validated) ? $validated['phone'] : $user->phone,
            'date_of_birth' => array_key_exists('date_of_birth', $validated) ? $validated['date_of_birth'] : $user->date_of_birth,
            'place_of_birth_code' => array_key_exists('place_of_birth_code', $validated) ? $validated['place_of_birth_code'] : $user->place_of_birth_code,
            'gender' => array_key_exists('gender', $validated) ? $validated['gender'] : $user->gender,
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
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:20480'],
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
            'verified_token' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Check if verified_token matches an OTP sent to this user for deletion
        $tokenRecord = \App\Models\login_token::where('magic_token', $request->verified_token)
            ->where('email', $user->email)
            ->where('purpose', 'delete_account')
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['verified_token' => 'Token verifikasi tidak valid atau sudah digunakan.']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Akun Anda berhasil dihapus.');
    }
}

