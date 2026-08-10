<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\HomeAssetController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AssetViewController;
use App\Http\Controllers\OwnerRegistrationController;
use App\Http\Controllers\Owner\DashboardController;

Route::get('/', [HomeController::class, 'index'])->name('Home');

// Search
Route::get('/search', [HomeController::class, 'search'])->name('assets.search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

// API for Home
Route::get('/api/home/nearby-assets', [HomeAssetController::class, 'nearby'])->name('api.home.nearby-assets');

// API for Locations
Route::get('/api/provinces', [\App\Http\Controllers\LocationController::class, 'getProvinces'])->name('api.provinces.index');
Route::get('/api/cities', [\App\Http\Controllers\LocationController::class, 'getCities'])->name('api.cities.index');
Route::get('/api/districts', [\App\Http\Controllers\LocationController::class, 'getDistricts'])->name('api.districts.index');
Route::get('/api/villages', [\App\Http\Controllers\LocationController::class, 'getVillages'])->name('api.villages.index');

// API for Asset Create Form
Route::get('/api/asset-types', [\App\Http\Controllers\Api\AssetTypeController::class, 'byCategory'])->name('api.asset-types.by-category');
Route::get('/api/asset-type/{id}/details', [\App\Http\Controllers\Api\AssetTypeController::class, 'details'])->name('api.asset-type.details');

Route::resource('assets', AssetController::class)->only(['show']);

Route::get('/bantuan', function () {
    return Inertia::render('Home/Support/PusatBantuan');
})->name('bantuan');

Route::get('/hubungi-kami', function () {
    return Inertia::render('Home/Support/HubungiKami');
})->name('hubungi-kami');


Route::middleware('auth')->prefix('owner')->group(function () {
    Route::get('/register', [OwnerRegistrationController::class, 'index'])->name('owner.register');
    Route::post('/register/step1', [OwnerRegistrationController::class, 'storeStep1'])->name('owner.register.step1');
    Route::post('/register/step2', [OwnerRegistrationController::class, 'storeStep2'])->name('owner.register.step2');
    Route::post('/register/step3', [OwnerRegistrationController::class, 'storeStep3'])->name('owner.register.step3');
    Route::get('/verification', [OwnerRegistrationController::class, 'verificationStatus'])->name('owner.verification');
});

Route::middleware('auth')->prefix('owner')->name('owner.')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('asset', \App\Http\Controllers\Owner\AssetController::class)->names('asset');
    Route::get('/bookings', function(){ 
        return Inertia::render('owner/Workspace', [
            'type' => 'bookings',
            'title' => 'Daftar Pesanan',
            'description' => 'Pantau dan kelola semua pesanan aset Anda di satu tempat.',
            'bookings' => [
                'data' => [
                    [
                        'id' => 1,
                        'code' => 'BOOK-987654321',
                        'asset' => 'Kamera Sony A7III',
                        'status' => 'pending',
                        'tenant' => 'Fadlan Firdaus',
                        'period' => '12 Agust 2026 - 15 Agust 2026',
                        'total' => 615000
                    ],
                    [
                        'id' => 2,
                        'code' => 'BOOK-123456789',
                        'asset' => 'Vila Indah Permai (Unit A)',
                        'status' => 'confirmed',
                        'tenant' => 'John Doe',
                        'period' => '10 Agust 2026 - 12 Agust 2026',
                        'total' => 2100000
                    ]
                ],
                'meta' => [
                    'total' => 2,
                    'from' => 1,
                    'to' => 2,
                    'links' => []
                ]
            ]
        ]); 
    })->name('bookings');
    Route::get('/bookings/{id}', function(){ 
        return Inertia::render('owner/BookingReview', [
            'booking' => [
                'id' => 1,
                'code' => 'BOOK-987654321',
                'asset' => 'Kamera Sony A7III',
                'start_date' => '12 Agustus 2026',
                'end_date' => '15 Agustus 2026',
                'subtotal' => 600000,
                'service_fee' => 15000,
                'total' => 615000,
                'tenant' => 'Fadlan Firdaus',
                'tenant_email' => 'fadlan@example.com',
                'tenant_phone' => '081234567890'
            ]
        ]); 
    })->name('bookings.show');
    Route::get('/monthly-payment', function(){ return Inertia::render('owner/MonthlyPayment'); })->name('monthly-payment');
    Route::get('/finance', function(){ return Inertia::render('owner/finance'); })->name('finance');
    Route::get('/settings', function(){ return Inertia::render('owner/settings'); })->name('settings');
    Route::get('/help', function(){ return Inertia::render('owner/help'); })->name('help');
});

Route::middleware('auth')->group(function () {
    Route::post('/search-logs', [HomeController::class, 'logSearch'])->name('search.log');
    Route::delete('/search-logs', [HomeController::class, 'clearSearchHistory'])->name('search.clear');
    Route::delete('/search-logs/keyword', [HomeController::class, 'deleteSearchKeyword'])->name('search.deleteKeyword');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start', [ChatController::class, 'startChat'])->name('chat.start');

    // API endpoints untuk Vue
    Route::get('/api/chats', [ChatController::class, 'getChats'])->name('api.chats.index');
    Route::get('/api/chats/{room}/messages', [ChatController::class, 'getMessages'])->name('api.chats.messages');
    Route::post('/api/chats/{room}/messages', [ChatController::class, 'sendMessage'])->name('api.chats.send');
    Route::put('/api/chats/{room}/messages/{message}', [ChatController::class, 'updateMessage'])->name('api.chats.update');
    Route::delete('/api/chats/{room}/messages/{message}', [ChatController::class, 'deleteMessage'])->name('api.chats.delete');
    Route::put('/api/chats/{room}/messages/read', [ChatController::class, 'markAsRead'])->name('api.chats.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
    Route::get('/ulasan/{id}', [ReviewController::class, 'create'])->name('ulasan.index');
    Route::post('/ulasan/{id}', [ReviewController::class, 'store'])->name('ulasan.store');

    // ==========================
    // Booking & Payment
    // ==========================
    Route::resource('booking', BookingController::class);
    Route::resource('payment', PaymentController::class);

    // ==========================
    // Favorite
    // ==========================
    Route::resource('favorites', FavoriteController::class)
        ->only(['index', 'store', 'destroy']);

    // ==========================
    // Last Seen / Terakhir Dilihat
    // ==========================
    Route::get('/last-seen', [AssetViewController::class, 'index'])->name('last-seen.index');
    Route::delete('/last-seen/bulk', [AssetViewController::class, 'bulkDestroy'])->name('last-seen.bulkDestroy');
    Route::delete('/last-seen/{assetView}', [AssetViewController::class, 'destroy'])->name('last-seen.destroy');

    // ==========================
    // Profile
    // ==========================
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
