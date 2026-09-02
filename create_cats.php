<?php

$cats = ['Bangunan Kos', 'Fasilitas Bersama', 'Bangunan Kos Dari Jalan', 'Depan Kamar'];
foreach ($cats as $cat) {
    App\Models\galery_category::firstOrCreate(['name' => $cat]);
}
echo "Done\n";
