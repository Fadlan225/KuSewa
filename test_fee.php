<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$fee = \Illuminate\Support\Facades\DB::table('service_fees')->first();
echo json_encode($fee);
