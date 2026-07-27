<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$asset = \App\Models\asset::with('ownerProfile.user')->first();
echo json_encode(array_keys($asset->toArray()));
