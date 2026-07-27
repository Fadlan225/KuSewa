<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = App\Models\User::find(131);
auth()->login(\);

\ = \Illuminate\Http\Request::create('/booking', 'POST', [
    'asset_id' => 28,
    'pricing_id' => 56,
    'startDate' => '2026-08-24',
    'endDate' => '2026-08-26',
    'duration' => 2,
    'rental_mode' => 'night'
]);

try {
    \ = app()->make(\App\Http\Controllers\BookingController::class);
    \ = \->store(\);
    print_r(\);
} catch (\Illuminate\Validation\ValidationException \) {
    echo 'Validation Error: ';
    print_r(\->errors());
} catch (\Exception \) {
    echo 'Error: ' . \->getMessage();
}

