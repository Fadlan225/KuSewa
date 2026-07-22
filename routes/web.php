<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\KonfirmasiPembayaranController;

// ==========================
// Public Routes
// ==========================
Route::get('/', [AssetController::class, 'index'])->name('Home');
Route::resource('assets', AssetController::class)->only(['show']);

Route::get('/booking-page', [BookingController::class, 'BookingPage'])->name('booking-page');
Route::get('/payment-page', [PaymentController::class, 'show'])->name('payment-page');

// Halaman Konfirmasi Pembayaran (Bisa diakses tanpa bentrok)
Route::get('/konfirmasi-pembayaran', [KonfirmasiPembayaranController::class, 'index'])
    ->name('konfirmasi.index');

Route::post('/konfirmasi-pembayaran', [KonfirmasiPembayaranController::class, 'store'])
    ->name('konfirmasi.store');

// ==========================
// Authenticated Routes
// ==========================
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Favorite
    Route::resource('favorites', FavoriteController::class)
        ->only(['index', 'store', 'destroy']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:owner'])->group(function () {
    //
});

require __DIR__ . '/auth.php';