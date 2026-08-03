<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilityCategorySeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('facility_categories')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = [
            ['name' => 'Internet',           'icon' => 'wifi',            'sort_order' => 1],
            ['name' => 'Hiburan',            'icon' => 'tv',              'sort_order' => 2],
            ['name' => 'Kenyamanan Kamar',   'icon' => 'bed',             'sort_order' => 3],
            ['name' => 'Dapur',              'icon' => 'utensils',        'sort_order' => 4],
            ['name' => 'Kamar Mandi',        'icon' => 'bath',            'sort_order' => 5],
            ['name' => 'Keamanan',           'icon' => 'shield',          'sort_order' => 6],
            ['name' => 'Parkir',             'icon' => 'car',             'sort_order' => 7],
            ['name' => 'Akses & Mobilitas',  'icon' => 'door-open',       'sort_order' => 8],
            ['name' => 'Olahraga & Rekreasi','icon' => 'dumbbell',        'sort_order' => 9],
            ['name' => 'F&B',                'icon' => 'coffee',          'sort_order' => 10],
            ['name' => 'Bisnis',             'icon' => 'briefcase',       'sort_order' => 11],
            ['name' => 'Peralatan Musik',    'icon' => 'music',           'sort_order' => 12],
            ['name' => 'Outdoor',            'icon' => 'tree',            'sort_order' => 13],
            ['name' => 'Laundry',            'icon' => 'washing-machine', 'sort_order' => 14],
            ['name' => 'Penyimpanan',         'icon' => 'box',             'sort_order' => 15],
            ['name' => 'Lainnya',             'icon' => 'more-horizontal', 'sort_order' => 99],
        ];

        $rows = array_map(fn($cat) => [
            'name'       => $cat['name'],
            'slug'       => Str::slug($cat['name']),
            'icon'       => $cat['icon'],
            'sort_order' => $cat['sort_order'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $categories);

        DB::table('facility_categories')->insert($rows);

        $this->command->info('✓ ' . count($rows) . ' facility categories berhasil dibuat!');
    }
}
