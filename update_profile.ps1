$content = Get-Content "app\Http\Controllers\ProfileController.php" -Raw

$imports = @"
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\booking;
use App\Models\search_log;
use App\Models\AssetView;
use App\Models\review;
use App\Models\asset_category;
"@

$content = $content -replace 'use App\\Http\\Requests\\ProfileUpdateRequest;', $imports

$dataLogic = @"
        `$tab = `$request->query('tab', 'profil');
        
        `$data = [
            'mustVerifyEmail' => `$request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'id' => `$user->id,
                'name' => `$user->name,
                'email' => `$user->email,
                'phone' => `$user->phone ?? '-',
                'date_of_birth' => `$user->date_of_birth,
                'place_of_birth_code' => `$user->place_of_birth_code,
                'gender' => `$user->gender,
                'avatar' => `$avatarUrl,
                'profile_photo' => `$avatarUrl,
                'is_owner' => `$isOwner || `$user->role === 'admin',
                'is_google_linked' => `$user->providers()->where('provider', 'google')->exists(),
            ],
            'owner_profile' => `$ownerProfile ? [
                'national_id' => `$ownerProfile->national_id,
                'address' => `$ownerProfile->address,
                'place_of_birth' => `$ownerProfile->place_of_birth,
                'date_of_birth' => `$ownerProfile->date_of_birth,
                'status' => `$ownerProfile->status,
            ] : null,
            'bank_account' => `$bankAccount ? [
                'bank_name' => `$bankAccount->bank_name,
                'account_number' => `$bankAccount->account_number,
                'account_holder' => `$bankAccount->account_holder,
            ] : null,
            'total_assets_rented' => `$totalAssetsRented,
            'bookings_count' => `$bookingsCount,
            'unpaid_bookings_count' => `$unpaidBookingsCount,
            'favorite_assets_count' => `$favoriteAssetsCount,
            'tab' => `$tab,
        ];

        `$userId = `$user->id;

        if (`$tab === 'transaksi') {
            `$data['bookings'] = booking::with([
                "asset" => function(`$q) use (`$userId) {
                    `$q->with(['favorites' => function(`$f) use (`$userId) {
                        `$f->where('user_id', `$userId);
                    }]);
                },
                "asset.firstImage",
                "asset.type.category",
                "payment",
                "reviews"
            ])->where("user_id", `$userId)->orderBy("id", "desc")->get();
        } elseif (`$tab === 'terakhir-dilihat') {
            `$data['lastSeen'] = AssetView::with(['asset.firstImage', 'asset.type.category', 'asset.defaultPricing'])
                ->where('user_id', `$userId)->orderBy('last_viewed', 'desc')->paginate(24);
        } elseif (`$tab === 'pencarian') {
            `$data['searchLogs'] = search_log::where('user_id', `$userId)->orderBy('searched_at', 'desc')->paginate(15);
        } elseif (`$tab === 'ulasan') {
            `$data['reviews'] = review::with(['booking.asset.firstImage', 'booking.asset.type.category', 'items.reviewTag'])
                ->where('user_id', `$userId)->orderBy('created_at', 'desc')->paginate(15);
        } elseif (`$tab === 'favorit') {
            `$favorites = `$user->favorites()->with(['asset' => function (`$query) {
                `$query->select([
                    'id', 'asset_type_id', 'owner_profile_id',
                    'title', 'city_code', 'district_code', 'address', 'status', 'detail'
                ])->with([
                    'city:code,name',
                    'thumbnailImages' => fn(`$q) => `$q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                    'defaultPricing:id,asset_id,price,rental_unit',
                    'type:id,name,allow_units,category_id',
                    'type.category:id,name,icon',
                ])
                ->withAvg('reviews as reviews_avg_rating', 'rating')
                ->withCount('reviews');
            }])->latest()->get();
            
            `$data['initialFavorites'] = `$favorites->map(function (`$fav) {
                `$asset = `$fav->asset;
                if (!`$asset) return null;
                
                `$asset->city_name = `$asset->city->name ?? '';
                `$asset->isFavorite = true;
                `$asset->favorite_id = `$fav->id;
                
                return `$asset;
            })->filter()->values();
            
            `$data['categoriesList'] = collect(['Semua'])->merge(asset_category::pluck('name'))->values();
        }

        return Inertia::render('Profile/Edit', `$data);
"@

$content = $content -replace "(?s)return Inertia::render\('Profile/Edit', \[.*?\]\);", $dataLogic

Set-Content "app\Http\Controllers\ProfileController.php" $content
