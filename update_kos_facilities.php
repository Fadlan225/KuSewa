

// 1. Ensure Indoor category exists
$catIndoor = DB::table('facility_categories')->where('name', 'Indoor')->first();
if (!$catIndoor) {
    $id = DB::table('facility_categories')->insertGetId([
        'name' => 'Indoor',
        'slug' => Str::slug('Indoor'),
        'icon' => 'home',
        'sort_order' => 14,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $catIndoor = DB::table('facility_categories')->where('id', $id)->first();
}

$catKeamanan = DB::table('facility_categories')->where('name', 'Keamanan')->first();
$catPerlengkapan = DB::table('facility_categories')->where('name', 'Perlengkapan Kamar')->first();
$catOutdoor = DB::table('facility_categories')->where('name', 'Outdoor')->first();

// Function to update or insert facility
function updateOrInsertFacility($name, $catId, $sortOrder = 99) {
    $fac = DB::table('facilities')->where('name', $name)->first();
    if ($fac) {
        DB::table('facilities')->where('id', $fac->id)->update([
            'facility_category_id' => $catId,
        ]);
        return $fac->id;
    } else {
        return DB::table('facilities')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'facility_category_id' => $catId,
            'is_active' => true,
            'sort_order' => $sortOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// 2. Add facilities to Keamanan
$keamananFacilities = ['Kunci Gerbang', 'Penjaga Kos', 'Pengurus Kos', 'Kartu Akses Masuk'];
$sortK = 7;
foreach($keamananFacilities as $name) {
    updateOrInsertFacility($name, $catKeamanan->id, $sortK++);
}

// 3. Add facilities to Indoor
$indoorFacilities = ['Ruang Santai', 'Ruang Tamu'];
$sortI = 1;
foreach($indoorFacilities as $name) {
    updateOrInsertFacility($name, $catIndoor->id, $sortI++);
}

// 4. Attach to Kos asset_type
$kosType = DB::table('asset_types')->where('name', 'Kos')->first();
if ($kosType) {
    $facilitiesToAttach = [
        // Perlengkapan Kamar (unit)
        ['name' => 'Bantal', 'scope' => 'unit'],
        ['name' => 'Guling', 'scope' => 'unit'],
        // Keamanan (asset)
        ['name' => 'Kunci Gerbang', 'scope' => 'asset'],
        ['name' => 'Penjaga Kos', 'scope' => 'asset'],
        ['name' => 'Pengurus Kos', 'scope' => 'asset'],
        ['name' => 'Kartu Akses Masuk', 'scope' => 'asset'],
        // Indoor (asset)
        ['name' => 'Ruang Santai', 'scope' => 'asset'],
        ['name' => 'Ruang Tamu', 'scope' => 'asset'],
        // Outdoor (asset)
        ['name' => 'Taman', 'scope' => 'asset'],
        ['name' => 'Gazebo', 'scope' => 'asset'],
    ];

    foreach ($facilitiesToAttach as $facInfo) {
        $fac = DB::table('facilities')->where('name', $facInfo['name'])->first();
        if ($fac) {
            $exists = DB::table('asset_type_facilities')->where([
                'asset_type_id' => $kosType->id,
                'facility_id' => $fac->id,
            ])->exists();

            if (!$exists) {
                DB::table('asset_type_facilities')->insert([
                    'asset_type_id' => $kosType->id,
                    'facility_id' => $fac->id,
                    'scope' => $facInfo['scope'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

echo "Database updated successfully.\n";
