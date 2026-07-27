<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Owner\PropertyController;
use App\Http\Controllers\OwnerRegisterController;
use App\Http\Controllers\Owner\MonthlyPaymentController;

// ==========================================
// PUBLIC ROUTES (Dapat diakses siapapun)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('Home');

// Search Routes
Route::get('/search', [HomeController::class, 'search'])->name('assets.search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

// Detail Aset
Route::resource('assets', AssetController::class)->only(['show']);

// Notifikasi Page
Route::get('/notifikasi', function () {
    return Inertia::render('Home/Notifikasi');
});


// ==========================================
// AUTHENTICATED USER ROUTES (Harus Login)
// ==========================================
Route::middleware('auth')->group(function () {

    // Search History Logs
    Route::post('/search-logs', [HomeController::class, 'logSearch'])->name('search.log');
    Route::delete('/search-logs', [HomeController::class, 'clearSearchHistory'])->name('search.clear');
    Route::delete('/search-logs/keyword', [HomeController::class, 'deleteSearchKeyword'])->name('search.deleteKeyword');

    // General User Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware('verified')->name('dashboard');

    // Favorites
    Route::resource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Upgrade Akun ke Owner
    Route::get('/become-owner', [OwnerRegisterController::class, 'create'])->name('owner.register');
    Route::post('/become-owner', [OwnerRegisterController::class, 'store'])->name('owner.register.store');
    Route::get('/become-owner/verify/{id}', [OwnerRegisterController::class, 'verify'])
        ->name('owner.register.verify')
        ->middleware('signed');
});


// ==========================================
// OWNER ROUTES (Khusus Pemilik Aset)
// ==========================================
// CATATAN: Jika role:owner masih bikin error saat testing, kamu bisa hilangkan 'role:owner' sementara.
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {

    // Dashboard Owner
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Properti (Menggunakan Resource Route standar Laravel)
    // Ini otomatis mendaftarkan route:
    // GET    /owner/property          -> owner.property.index
    // GET    /owner/property/create   -> owner.property.create
    // POST   /owner/property          -> owner.property.store
    // GET    /owner/property/{id}/edit-> owner.property.edit
    // PUT    /owner/property/{id}     -> owner.property.update
    // DELETE /owner/property/{id}     -> owner.property.destroy
    Route::resource('property', PropertyController::class);

    // Monthly Payment
    Route::get('/monthly-payment', [MonthlyPaymentController::class, 'index'])->name('MonthlyPayment');
    Route::post('/monthly-payment', [MonthlyPaymentController::class, 'store'])->name('MonthlyPayment.store');

});


// Auth Routes (Login, Register, Reset Password)
require __DIR__ . '/auth.php';