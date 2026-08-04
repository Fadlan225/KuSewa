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
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/owner-validation', [App\Http\Controllers\AdminOwnerValidationController::class, 'index'])->name('owner-validation');
    Route::get('/pengajuan-akun', [App\Http\Controllers\AdminAccountSubmissionController::class, 'index'])->name('pengajuan-akun');
    Route::get('/user-accounts', [App\Http\Controllers\AdminUserAccountController::class, 'index'])->name('user-accounts');
    Route::get('/admin-accounts', [App\Http\Controllers\AdminAdministratorAccountController::class, 'index'])->name('admin-accounts');
    Route::post('/admin-accounts', [App\Http\Controllers\AdminAdministratorAccountController::class, 'store'])->name('admin-accounts.store');
    Route::get('/asset-validation', [App\Http\Controllers\AdminAssetValidationController::class, 'index'])->name('asset-validation');
    Route::get('/property-assets', [App\Http\Controllers\AdminPropertyAssetController::class, 'index'])->name('property-assets');
    Route::get('/category-facility', [App\Http\Controllers\AdminCategoryFacilityController::class, 'index'])->name('category-facility');
    Route::get('/payment-system', [App\Http\Controllers\AdminPaymentSystemController::class, 'index'])->name('payment-system');
    Route::get('/service-fee', [App\Http\Controllers\AdminServiceFeeController::class, 'index'])->name('service-fee');
    Route::get('/promo-discount', [App\Http\Controllers\AdminPromoDiscountController::class, 'index'])->name('promo-discount');
    Route::get('/cms-manager', [App\Http\Controllers\AdminCMSManagerController::class, 'index'])->name('cms-manager');
    Route::get('/system-notifications', [App\Http\Controllers\AdminSystemNotificationsController::class, 'index'])->name('system-notifications');
    Route::get('/user-reports', [App\Http\Controllers\AdminUserReportController::class, 'index'])->name('user-reports');
    Route::get('/activity-log', [App\Http\Controllers\AdminActivityLogController::class, 'index'])->name('activity-log');
    Route::get('/backup-restore', [App\Http\Controllers\AdminBackupRestoreController::class, 'index'])->name('backup-restore');
});


// ==========================================
// OWNER ROUTES (Khusus Pemilik Aset)
// ==========================================
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {

    // Dashboard Owner
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Properti & Aset (CRUD)
    Route::get('/properties', [PropertyController::class, 'index'])->name('property.index');
    Route::get('/property/create', [PropertyController::class, 'create'])->name('property.create');
    Route::post('/property/store', [PropertyController::class, 'store'])->name('property.store');
    Route::get('/property/{property}/edit', [PropertyController::class, 'edit'])->name('property.edit');
    Route::put('/property/{property}', [PropertyController::class, 'update'])->name('property.update');
    Route::delete('/property/{property}', [PropertyController::class, 'destroy'])->name('property.destroy');

});


// Auth Routes (Login, Register, Reset Password)
require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/become-owner', [OwnerRegisterController::class, 'create'])->name('owner.register');
    Route::post('/become-owner', [OwnerRegisterController::class, 'store'])->name('owner.register.store');

    // Route verifikasi dari link email
    Route::get('/become-owner/verify/{id}', [OwnerRegisterController::class, 'verify'])
        ->name('owner.register.verify')
        ->middleware('signed');
});

Route::middleware(['auth'])->prefix('owner')->name('owner.')->group(function () {
    // Berikan nama route persis 'MonthlyPayment'
    Route::get('/monthly-payment', [MonthlyPaymentController::class, 'index'])->name('MonthlyPayment');
    Route::post('/monthly-payment', [MonthlyPaymentController::class, 'store'])->name('MonthlyPayment.store');
});