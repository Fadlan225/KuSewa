<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $unreadCount = 0;
        
        if ($user) {
            $user->load('ownerProfile');
            
            $unreadCount = \App\Models\room_chat::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('ownerProfile', function ($query) use ($user) {
                      $query->where('user_id', $user->id);
                  });
            })->whereHas('messages', function($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->where('is_read', false);
            })->withCount(['messages as unread' => function($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->where('is_read', false);
            }])->get()->sum('unread');
        }

        $sidebarCounts = [
            'pendingPropertyCount' => null, // TODO
            'pendingBookingCount' => null, // TODO
            'verificationCount' => null, // TODO
            'unreadNotificationCount' => $unreadCount, // Menggunakan query yang sudah ada
        ];

        $globalPriceRange = \Illuminate\Support\Facades\Cache::remember('global_price_range', 3600, function() {
            $minPrice = \App\Models\asset_pricing::min('price') ?? 0;
            $maxPrice = \App\Models\asset_pricing::max('price') ?? 0;
            
            // Bulatkan ke kelipatan 50.000 terdekat sesuai request
            $minPriceRounded = floor($minPrice / 50000) * 50000;
            $maxPriceRounded = ceil($maxPrice / 50000) * 50000;
            
            // Cegah error jika database kosong
            if ($maxPriceRounded == 0) {
                $maxPriceRounded = 10000000;
            }
            if ($minPriceRounded < 0) {
                $minPriceRounded = 0;
            }

            return [
                'min' => $minPriceRounded,
                'max' => $maxPriceRounded,
            ];
        });

        $isProfileComplete = false;
        $ownerAssets = [];
        if ($user && $user->ownerProfile) {
            $isProfileComplete = $user->ownerProfile->bankAccounts()->exists();
            $ownerAssets = \App\Models\asset::where('owner_profile_id', $user->ownerProfile->id)
                ->select('id', 'title', 'slug', 'status')
                ->with(['thumbnailImages' => function($q) {
                    $q->select('asset_id', 'image')->limit(1);
                }])
                ->get()
                ->map(function ($asset) {
                    $thumbnail = null;
                    if ($asset->thumbnailImages->isNotEmpty()) {
                        $img = $asset->thumbnailImages->first();
                        $thumbnail = $img->image ?? null;
                        if ($thumbnail && !str_starts_with($thumbnail, 'http')) {
                            $thumbnail = asset('storage/' . $thumbnail);
                        }
                    }
                    return [
                        'id' => $asset->id,
                        'slug' => $asset->slug,
                        'title' => $asset->title,
                        'status' => $asset->status,
                        'thumbnail' => $thumbnail,
                    ];
                });
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'unreadCount' => $unreadCount,
            ],
            'active_asset_slug' => $request->session()->get('active_asset_slug'),
            'ownerAssets' => $ownerAssets,
            'sidebarCounts' => $sidebarCounts,
            'globalPriceRange' => $globalPriceRange,
            'isProfileComplete' => $isProfileComplete,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ],
        ];
    }
}
