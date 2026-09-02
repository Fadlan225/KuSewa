

// 1. Create category Perabot Kamar Mandi
$catPerabot = DB::table('facility_categories')->where('name', 'Perabot Kamar Mandi')->first();
if (!$catPerabot) {
    $id = DB::table('facility_categories')->insertGetId([
        'name' => 'Perabot Kamar Mandi',
        'slug' => Str::slug('Perabot Kamar Mandi'),
        'icon' => 'bath', // Assuming bath is a good icon
        'sort_order' => 6,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $catPerabot = DB::table('facility_categories')->where('id', $id)->first();
}

$catKamarMandi = DB::table('facility_categories')->where('name', 'Kamar Mandi')->first();

// 2. Move items to Perabot Kamar Mandi
$perabotNames = ['Bathtub', 'Shower', 'Water Heater', 'Toiletries', 'Kloset Duduk', 'Kloset Jongkok', 'Wastafel'];
foreach ($perabotNames as $index => $name) {
    $fac = DB::table('facilities')->where('name', $name)->first();
    if ($fac) {
        DB::table('facilities')->where('id', $fac->id)->update([
            'facility_category_id' => $catPerabot->id,
            'sort_order' => $index + 1,
        ]);
    } else {
        DB::table('facilities')->insert([
            'name' => $name,
            'slug' => Str::slug($name),
            'facility_category_id' => $catPerabot->id,
            'is_active' => true,
            'sort_order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// 3. Keep Kamar Mandi items in Kamar Mandi
$kmNames = ['Kamar Mandi Dalam', 'Kamar Mandi Bersama', 'Kamar Mandi Luar'];
foreach ($kmNames as $index => $name) {
    $fac = DB::table('facilities')->where('name', $name)->first();
    if ($fac) {
        DB::table('facilities')->where('id', $fac->id)->update([
            'facility_category_id' => $catKamarMandi->id,
            'sort_order' => $index + 1,
        ]);
    }
}

// 4. Update AssetTypeFacilities
$kosType = DB::table('asset_types')->where('name', 'Kos')->first();
if ($kosType) {
    foreach ($kmNames as $name) {
        $fac = DB::table('facilities')->where('name', $name)->first();
        if ($fac) {
            DB::table('asset_type_facilities')
                ->where('asset_type_id', $kosType->id)
                ->where('facility_id', $fac->id)
                ->update(['scope' => 'asset']);
        }
    }

    foreach ($perabotNames as $name) {
        $fac = DB::table('facilities')->where('name', $name)->first();
        if ($fac) {
            DB::table('asset_type_facilities')->updateOrInsert(
                ['asset_type_id' => $kosType->id, 'facility_id' => $fac->id],
                ['scope' => 'unit', 'updated_at' => now()]
            );
        }
    }
}

echo "Database updated.\n";
