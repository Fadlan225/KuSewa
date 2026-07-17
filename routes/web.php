<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// Import Auth Controller bawaan Breeze/Laravel (Blade Version)
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini adalah tempat Anda mendaftarkan rute-rute aplikasi Anda.
|
*/

// Halaman Utama / Home (Inertia)
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Halaman Properti (Inertia)
Route::get('/properti', function () {
    return Inertia::render('Properties/Index');
})->name('properties.index');

// Switch Bahasa (Layanan Internasionalisasi)
Route::get('/lang/{locale}', function (string $locale): RedirectResponse {
    session()->put('locale', $locale);

    return redirect()->back();
})->whereIn('locale', ['id', 'en'])->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Rute Autentikasi (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Halaman & Proses Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Halaman & Proses Register
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Rute Khusus Pengguna Terautentikasi (Auth Only)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Halaman Dashboard (Inertia)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Proses Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});