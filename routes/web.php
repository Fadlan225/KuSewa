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
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Api\AssetTypeController;
use App\Http\Controllers\Owner\AssetController as OwnerAssetController;
use App\Http\Controllers\Owner\IncomeController;

Route::get('/', [HomeController::class, 'index'])->name('Home');

// Search
Route::get('/search', [HomeController::class, 'search'])->name('assets.search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

// API for Home
Route::get('/api/home/nearby-assets', [HomeAssetController::class, 'nearby'])->name('api.home.nearby-assets');
Route::get('/api/home/sections', [HomeController::class, 'apiGetSections'])->name('api.home.sections');

// API for Locations
Route::get('/api/provinces', [LocationController::class, 'getProvinces'])->name('api.provinces.index');
Route::get('/api/cities', [LocationController::class, 'getCities'])->name('api.cities.index');
Route::get('/api/districts', [LocationController::class, 'getDistricts'])->name('api.districts.index');
Route::get('/api/villages', [LocationController::class, 'getVillages'])->name('api.villages.index');

// API for Asset Create Form
Route::get('/api/asset-types', [AssetTypeController::class, 'byCategory'])->name('api.asset-types.by-category');
Route::get('/api/asset-type/{id}/details', [AssetTypeController::class, 'details'])->name('api.asset-type.details');

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

    Route::post('asset/upload-temp', [OwnerAssetController::class, 'uploadTemp'])->name('asset.upload-temp');
    Route::post('asset/auto-save', [OwnerAssetController::class, 'autoSaveDraft'])->name('asset.auto-save');
    Route::get('asset/draft/{id}', [OwnerAssetController::class, 'editDraft'])->name('asset.edit-draft');
    Route::resource('asset', OwnerAssetController::class)->names('asset');
    Route::patch('asset/{asset}/toggle-status', [OwnerAssetController::class, 'toggleStatus'])->name('asset.toggle-status');
    Route::post('asset/{asset}/facilities', [OwnerAssetController::class, 'storeFacility'])->name('asset.facilities.store');
    Route::delete('asset/{asset}/facilities/{facility}', [OwnerAssetController::class, 'destroyFacility'])->name('asset.facilities.destroy');

    Route::post('asset/{asset}/units', [OwnerAssetController::class, 'storeUnit'])->name('asset.units.store');
    Route::put('asset/{asset}/units/{unit}', [OwnerAssetController::class, 'updateUnit'])->name('asset.units.update');

    Route::post('asset/{asset}/images', [OwnerAssetController::class, 'storeImage'])->name('asset.images.store');
    Route::delete('asset/{asset}/images/{image}', [OwnerAssetController::class, 'destroyImage'])->name('asset.images.destroy');

    // FAQ & Kebijakan
    Route::post('asset/{asset}/faqs', [OwnerAssetController::class, 'storeFaq'])->name('asset.faqs.store');
    Route::put('asset/{asset}/faqs/{faq}', [OwnerAssetController::class, 'updateFaq'])->name('asset.faqs.update');
    Route::delete('asset/{asset}/faqs/{faq}', [OwnerAssetController::class, 'destroyFaq'])->name('asset.faqs.destroy');

    Route::post('asset/{asset}/policies', [OwnerAssetController::class, 'storePolicy'])->name('asset.policies.store');
    Route::put('asset/{asset}/policies/{policy}', [OwnerAssetController::class, 'updatePolicy'])->name('asset.policies.update');
    Route::delete('asset/{asset}/policies/{policy}', [OwnerAssetController::class, 'destroyPolicy'])->name('asset.policies.destroy');

    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{id}', [OwnerBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{id}/confirm', [OwnerBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{id}/verify-payment', [OwnerBookingController::class, 'verifyPayment'])->name('bookings.verify-payment');
    Route::patch('/bookings/{id}/reject', [OwnerBookingController::class, 'reject'])->name('bookings.reject');
    Route::patch('/bookings/{id}/complete', [OwnerBookingController::class, 'complete'])->name('bookings.complete');
    Route::get('/monthly-payment', [MonthlyPaymentController::class, 'index'])->name('monthly-payment');
    Route::post('/monthly-payment/submit', [MonthlyPaymentController::class, 'store'])->name('monthly-payment.store');
    Route::get('/income', [IncomeController::class, 'index'])->name('income');
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

    // ==========================
    // Activity Hub & Sub-menus
    // ==========================
    Route::get('/aktivitas', [AktivitasController::class, 'hub'])->name('aktivitas.hub');
    Route::get('/aktivitas/transaksi', [AktivitasController::class, 'transaksi'])->name('aktivitas.transaksi');
    Route::get('/aktivitas/pencarian', [AktivitasController::class, 'searchHistory'])->name('aktivitas.pencarian');
    Route::get('/aktivitas/ulasan', [ReviewController::class, 'myReviews'])->name('aktivitas.ulasan');

    Route::get('/ulasan/{id}', [ReviewController::class, 'create'])->name('ulasan.index');
    Route::post('/ulasan/{id}', [ReviewController::class, 'store'])->name('ulasan.store');

    // ==========================
    // Booking & Payment
    // ==========================
    Route::patch('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::resource('booking', BookingController::class);
    Route::resource('payment', PaymentController::class);

    // ==========================
    // Favorite
    // ==========================
    Route::get('/aktivitas/favorit', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // ==========================
    // Last Seen / Terakhir Dilihat
    // ==========================
    Route::get('/aktivitas/terakhir-dilihat', [AssetViewController::class, 'index'])->name('last-seen.index');
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

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('admin/dashboard'))->name('dashboard');
    Route::get('/activity-log', fn() => Inertia::render('admin/ActivityLog'))->name('activity-log');
    Route::get('/account-management', fn() => Inertia::render('admin/AdministratorAccountManagement'))->name('account-management');
    Route::get('/aset-properti', fn() => Inertia::render('admin/AsetProperti'))->name('aset-properti');
    Route::get('/backup-restore', fn() => Inertia::render('admin/BackupRestore'))->name('backup-restore');
    Route::get('/cms-manager', fn() => Inertia::render('admin/CMSManager'))->name('cms-manager');
    Route::get('/kategori-fasilitas', fn() => Inertia::render('admin/KategoriFasilitas'))->name('kategori-fasilitas');
    Route::get('/pengajuan-akun', fn() => Inertia::render('admin/KelolaPengajuanAkun'))->name('pengajuan-akun');
    Route::get('/payment-system', fn() => Inertia::render('admin/PaymentSystem'))->name('payment-system');
    Route::get('/promo-diskon', fn() => Inertia::render('admin/PromoDiskon'))->name('promo-diskon');
    Route::get('/service-fee', fn() => Inertia::render('admin/ServiceFeeSanksi'))->name('service-fee');
    Route::get('/system-notifications', fn() => Inertia::render('admin/SystemNotifications'))->name('system-notifications');
    Route::get('/user-management', fn() => Inertia::render('admin/UserAccountManagement'))->name('user-management');
    Route::get('/user-reports', fn() => Inertia::render('admin/UserReports'))->name('user-reports');
    Route::get('/validasi-aset', fn() => Inertia::render('admin/ValidasiAsetPengajuan'))->name('validasi-aset');
    Route::patch('/validasi-aset/{id}/approve', fn() => back())->name('validasi-aset.approve');
    Route::patch('/validasi-aset/{id}/reject', fn() => back())->name('validasi-aset.reject');
});

// =============================================
// Notification & Push Subscription Routes
// =============================================
Route::middleware(['auth', 'web'])->group(function () {
    // Notifikasi in-app
    Route::prefix('api/notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all');
        Route::post('/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-one');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Web Push Subscriptions
    Route::post('/api/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('/api/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

    // Halaman notifikasi
    Route::get('/notifications', fn() => inertia('Notifications/Index'))->name('notifications.page');


});

require __DIR__ . '/auth.php';
