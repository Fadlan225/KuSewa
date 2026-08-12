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
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\MonthlyPaymentController;

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
    Route::post('asset/{asset}/facilities', [\App\Http\Controllers\Owner\AssetController::class, 'storeFacility'])->name('asset.facilities.store');
    Route::delete('asset/{asset}/facilities/{facility}', [\App\Http\Controllers\Owner\AssetController::class, 'destroyFacility'])->name('asset.facilities.destroy');

    Route::post('asset/{asset}/units', [\App\Http\Controllers\Owner\AssetController::class, 'storeUnit'])->name('asset.units.store');
    Route::put('asset/{asset}/units/{unit}', [\App\Http\Controllers\Owner\AssetController::class, 'updateUnit'])->name('asset.units.update');

    Route::post('asset/{asset}/images', [\App\Http\Controllers\Owner\AssetController::class, 'storeImage'])->name('asset.images.store');
    Route::delete('asset/{asset}/images/{image}', [\App\Http\Controllers\Owner\AssetController::class, 'destroyImage'])->name('asset.images.destroy');

    // FAQ & Kebijakan
    Route::post('asset/{asset}/faqs', [\App\Http\Controllers\Owner\AssetController::class, 'storeFaq'])->name('asset.faqs.store');
    Route::put('asset/{asset}/faqs/{faq}', [\App\Http\Controllers\Owner\AssetController::class, 'updateFaq'])->name('asset.faqs.update');
    Route::delete('asset/{asset}/faqs/{faq}', [\App\Http\Controllers\Owner\AssetController::class, 'destroyFaq'])->name('asset.faqs.destroy');

    Route::post('asset/{asset}/policies', [\App\Http\Controllers\Owner\AssetController::class, 'storePolicy'])->name('asset.policies.store');
    Route::put('asset/{asset}/policies/{policy}', [\App\Http\Controllers\Owner\AssetController::class, 'updatePolicy'])->name('asset.policies.update');
    Route::delete('asset/{asset}/policies/{policy}', [\App\Http\Controllers\Owner\AssetController::class, 'destroyPolicy'])->name('asset.policies.destroy');

    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{id}', [OwnerBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{id}/confirm', [OwnerBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{id}/verify-payment', [OwnerBookingController::class, 'verifyPayment'])->name('bookings.verify-payment');
    Route::patch('/bookings/{id}/reject', [OwnerBookingController::class, 'reject'])->name('bookings.reject');
    Route::patch('/bookings/{id}/complete', [OwnerBookingController::class, 'complete'])->name('bookings.complete');
    Route::get('/monthly-payment', [MonthlyPaymentController::class, 'index'])->name('monthly-payment');
    Route::post('/monthly-payment/submit', [MonthlyPaymentController::class, 'store'])->name('monthly-payment.store');
    Route::get('/finance', function(){ return Inertia::render('owner/finance'); })->name('finance');
    Route::get('/income', function(){ return Inertia::render('owner/Income'); })->name('income');
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
