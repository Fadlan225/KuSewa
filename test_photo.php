<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::whereNotNull('profile_photo')->first();
echo $user ? $user->profile_photo : "No user with profile photo";
