<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SocialMediaLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            [
                'platform_name' => 'kitasewa.web.id',
                'url' => 'https://www.instagram.com/kitasewa.web.id/',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'platform_name' => 'kitasewa.web.id@gmail.com',
                'url' => 'mailto:kitasewa.web.id@gmail.com',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'platform_name' => '085151241588',
                'url' => 'https://wa.me/6285151241588',
                'is_active' => true,
                'order' => 3,
            ]
        ];

        foreach ($links as $link) {
            \App\Models\SocialMediaLink::updateOrCreate(
                ['platform_name' => $link['platform_name']],
                $link
            );
        }

        \Illuminate\Support\Facades\Cache::forget('social_links');
    }
}
