<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\review;
use App\Models\review_tag;
use App\Models\review_tag_item;

class ReviewTagItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        review_tag_item::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $reviews = review::with('booking.asset')->get();
        $tagsByType = review_tag::all()->groupBy('asset_type_id');

        foreach ($reviews as $review) {
            $asset = $review->booking?->asset;
            if ($asset && $asset->asset_type_id) {
                $typeId = $asset->asset_type_id;
                
                if (isset($tagsByType[$typeId])) {
                    $availableTags = $tagsByType[$typeId];
                    
                    // Pick 1 to 3 random tags
                    $numTagsToPick = rand(1, min(3, $availableTags->count()));
                    $randomTags = $availableTags->random($numTagsToPick);
                    
                    foreach ($randomTags as $tag) {
                        review_tag_item::create([
                            'review_id' => $review->id,
                            'review_tag_id' => $tag->id,
                        ]);
                    }
                }
            }
        }
    }
}
