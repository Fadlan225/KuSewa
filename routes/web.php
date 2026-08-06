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

Route::get('/', [HomeController::class, 'index'])->name('Home');

// Search
Route::get('/search', [HomeController::class, 'search'])->name('assets.search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

// API for Home
Route::get('/api/home/nearby-assets', [HomeAssetController::class, 'nearby'])->name('api.home.nearby-assets');

// API for Locations
Route::get('/api/cities', [\App\Http\Controllers\LocationController::class, 'getCities'])->name('api.cities.index');

Route::middleware('auth')->group(function () {
    Route::post('/search-logs', [HomeController::class, 'logSearch'])->name('search.log');
    Route::delete('/search-logs', [HomeController::class, 'clearSearchHistory'])->name('search.clear');
    Route::delete('/search-logs/keyword', [HomeController::class, 'deleteSearchKeyword'])->name('search.deleteKeyword');
});

Route::resource('assets', AssetController::class)->only(['show']);

Route::get('/bantuan', function () {
    return Inertia::render('Home/Support/PusatBantuan');
})->name('bantuan');

Route::get('/hubungi-kami', function () {
    return Inertia::render('Home/Support/HubungiKami');
})->name('hubungi-kami');

Route::get('/mulai-sewakan', function () {
    return Inertia::render('Home/MulaiSewakan');
});

Route::middleware(['auth', 'role:owner'])->group(function () {

});

Route::get('/confirm-payment', function(){
    return Inertia::render('Home/Confirm-Payment');
});

Route::middleware('auth')->group(function () {
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
