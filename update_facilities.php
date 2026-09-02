

// 1. Rename Kenyamanan Kamar to Perlengkapan Kamar
$catKenyamanan = DB::table('facility_categories')->where('name', 'Kenyamanan Kamar')->first();
if ($catKenyamanan) {
    DB::table('facility_categories')->where('id', $catKenyamanan->id)->update([
        'name' => 'Perlengkapan Kamar',
        'slug' => Str::slug('Perlengkapan Kamar'),
    ]);
}

$catPerlengkapan = DB::table('facility_categories')->where('name', 'Perlengkapan Kamar')->first();

// 2. Create Sirkulasi Udara
$catSirkulasi = DB::table('facility_categories')->where('name', 'Sirkulasi Udara')->first();
if (!$catSirkulasi) {
    $id = DB::table('facility_categories')->insertGetId([
        'name' => 'Sirkulasi Udara',
        'slug' => Str::slug('Sirkulasi Udara'),
        'icon' => 'wind',
        'sort_order' => 4,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $catSirkulasi = DB::table('facility_categories')->where('id', $id)->first();
}

// 3. Ensure Lainnya
$catLainnya = DB::table('facility_categories')->where('name', 'Lainnya')->first();
if (!$catLainnya) {
    $id = DB::table('facility_categories')->insertGetId([
        'name' => 'Lainnya',
        'slug' => Str::slug('Lainnya'),
        'icon' => 'more-horizontal',
        'sort_order' => 99,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $catLainnya = DB::table('facility_categories')->where('id', $id)->first();
}

// Rename existing Jendela / Sirkulasi to Jendela and Meja Kerja to Meja
DB::table('facilities')->where('name', 'Jendela / Sirkulasi')->update(['name' => 'Jendela', 'slug' => Str::slug('Jendela')]);
DB::table('facilities')->where('name', 'Meja Kerja')->update(['name' => 'Meja', 'slug' => Str::slug('Meja')]);

// Facilities to map
$sirkulasiNames = ['AC', 'Kipas Angin', 'Ventilasi', 'Jendela'];
$perlengkapanNames = ['Kasur', 'Bantal', 'Guling', 'Lemari Pakaian', 'Meja', 'Meja Rias', 'Kursi', 'Sofa', 'Cermin'];
$lainnyaNames = ['Balkon'];

// Function to update or insert facility
function updateOrInsertFacility($name, $catId, $sortOrder) {
    $fac = DB::table('facilities')->where('name', $name)->first();
    if ($fac) {
        DB::table('facilities')->where('id', $fac->id)->update([
            'facility_category_id' => $catId,
            'sort_order' => $sortOrder,
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

$sort = 1;
foreach ($perlengkapanNames as $name) {
    updateOrInsertFacility($name, $catPerlengkapan->id, $sort++);
}

// For existing items in Perlengkapan Kamar that are not in the list (like Brankas, Hair Dryer, Mini Bar)
// We will just let them stay in Perlengkapan Kamar as the category was renamed.

$sort = 1;
foreach ($sirkulasiNames as $name) {
    updateOrInsertFacility($name, $catSirkulasi->id, $sort++);
}

// Keep existing Lainnya sorting, just append or put Balkon first
updateOrInsertFacility('Balkon', $catLainnya->id, 99);

echo "Database updated successfully.\n";
