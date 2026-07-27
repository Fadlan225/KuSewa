<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BookingController;

Route::get('/', [HomeController::class, 'index'])->name('Home');

// Search
Route::get('/search', [HomeController::class, 'search'])->name('assets.search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

Route::middleware('auth')->group(function () {
    Route::post('/search-logs', [HomeController::class, 'logSearch'])->name('search.log');
    Route::delete('/search-logs', [HomeController::class, 'clearSearchHistory'])->name('search.clear');
    Route::delete('/search-logs/keyword', [HomeController::class, 'deleteSearchKeyword'])->name('search.deleteKeyword');
});

Route::resource('assets', AssetController::class)->only(['show']);

Route::get('/notifikasi', function () {
    return Inertia::render('Home/Notifikasi');
});


    Route::get('/dashboard', function () { return Inertia::render('Admin/Dashboard'); })->name('admin.dashboard');
    Route::get('/validasi-akun', function () { return Inertia::render('Admin/ValidasiAkun'); })->name('admin.validasi-akun');
    Route::get('/validasi-aset', function () { return Inertia::render('Admin/ValidasiAset'); })->name('admin.validasi-aset');
    Route::get('/validasi-pembayaran', function () { return Inertia::render('Admin/ValidasiPembayaran'); })->name('admin.validasi-pembayaran');
    Route::get('/kelola-penyewa', function () { return Inertia::render('Admin/KelolaPenyewa'); })->name('admin.kelola-penyewa');
    Route::get('/kelola-pemilik', function () { return Inertia::render('Admin/KelolaPemilik'); })->name('admin.kelola-pemilik');
    Route::get('/promo', function () { return Inertia::render('Admin/Promo'); })->name('admin.promo');
    Route::get('/pembayaran', function () { return Inertia::render('Admin/Pembayaran'); })->name('admin.pembayaran');
    Route::get('/laporan', function () { return Inertia::render('Admin/Laporan'); })->name('admin.laporan');
    Route::get('/notifikasi', function () { return Inertia::render('Admin/Notifikasi'); })->name('admin.notifikasi');


Route::middleware(['auth', 'role:owner'])->group(function () {

});

Route::middleware('auth')->group(function () {
    // ==========================
    // Booking
    // ==========================
    Route::resource('booking', BookingController::class);

    // ==========================
    // Favorite
    // ==========================
    Route::resource('favorites', FavoriteController::class)
        ->only(['index', 'store', 'destroy']);

    // ==========================
    // Profile
    // ==========================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
