<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFIKASI LENGKAP DATABASE ===\n\n";

// Users
$users    = \DB::table('users')->get();
$admin    = $users->where('role', 'admin')->count();
$customer = $users->where('role', 'customer')->count();
$owners   = \DB::table('owner_profiles')->count();
echo "USERS\n";
echo "  Total       : " . $users->count() . "\n";
echo "  Admin       : {$admin}\n";
echo "  Customer    : {$customer}\n";
echo "  Owner prof  : {$owners} (subset dari customer)\n\n";

// Assets
echo "ASSETS\n";
echo "  Total       : " . \DB::table('assets')->count() . "\n";
echo "  Images      : " . \DB::table('asset_images')->count() . "\n";
echo "  Pricing     : " . \DB::table('asset_pricings')->count() . "\n\n";

// Bookings
$bookings  = \DB::table('bookings')->get();
$pending   = $bookings->where('booking_status','pending')->count();
$confirmed = $bookings->where('booking_status','confirmed')->count();
$completed = $bookings->where('booking_status','completed')->count();
$cancelled = $bookings->where('booking_status','cancelled')->count();
echo "BOOKINGS (total: " . $bookings->count() . ")\n";
echo "  Pending     : {$pending}  (" . round($pending/$bookings->count()*100) . "%)\n";
echo "  Confirmed   : {$confirmed} (" . round($confirmed/$bookings->count()*100) . "%)\n";
echo "  Completed   : {$completed} (" . round($completed/$bookings->count()*100) . "%)\n";
echo "  Cancelled   : {$cancelled}  (" . round($cancelled/$bookings->count()*100) . "%)\n\n";

// Payments
$payments = \DB::table('payments')->get();
echo "PAYMENTS (total: " . $payments->count() . ")\n";
echo "  Paid        : " . $payments->where('payment_status','paid')->count() . "\n";
echo "  Failed      : " . $payments->where('payment_status','failed')->count() . "\n\n";

// Engagement
echo "ENGAGEMENT\n";
echo "  Reviews     : " . \DB::table('reviews')->count() . " (dari {$completed} completed)\n";
echo "  Favorites   : " . \DB::table('favorites')->count() . " (5 × 99 customer)\n";
echo "  Search Logs : " . \DB::table('search_logs')->count() . " (10 × 99 customer)\n\n";

// Image files check
$imgDir = public_path('assets/images');
$files  = glob($imgDir . '/*.jpg') ?: [];
echo "GD IMAGES\n";
echo "  Files di public/assets/images/ : " . count($files) . "\n";
echo "  Sample : " . (count($files) > 0 ? basename($files[0]) : 'NONE') . "\n\n";

echo "=== SEMUA DATA SIAP ===\n";
