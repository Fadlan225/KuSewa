<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan booking expired secara otomatis setiap 30 menit
Schedule::command('bookings:cancel-expired')->everyThirtyMinutes();

// Generate tagihan bulanan Owner setiap tanggal 1 pukul 01:00 pagi
Schedule::command('billing:generate')->monthlyOn(1, '01:00');
