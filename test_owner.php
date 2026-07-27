<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$booking = \App\Models\booking::with('asset.ownerProfile.user')->first();
echo json_encode([
    'booking_id' => $booking->id ?? null,
    'asset_id' => $booking->asset_id ?? null,
    'owner_profile' => $booking->asset->ownerProfile ?? null,
    'user' => $booking->asset->ownerProfile->user ?? null,
    'phone_number' => $booking->asset->ownerProfile->user->phone_number ?? 'null',
]);
