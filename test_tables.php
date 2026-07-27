<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = \Illuminate\Support\Facades\Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
$chatTables = array_filter($tables, fn($t) => strpos($t, 'chat') !== false || strpos($t, 'message') !== false);
echo json_encode(array_values($chatTables));
