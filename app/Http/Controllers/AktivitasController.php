<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\booking;

class AktivitasController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $bookings = booking::with([
            "asset" => function($q) use ($userId) {
                $q->with(['favorites' => function($f) use ($userId) {
                    $f->where('user_id', $userId);
                }]);
            },
            "asset.firstImage",
            "asset.type.category",
            "payment",
            "reviews"
        ])
        ->where("user_id", $userId)
        ->orderBy("created_at", "desc")
        ->get();

        // Transform booking to set isFavorite and favoriteId on asset
        $bookings->each(function($b) {
            if ($b->asset) {
                $fav = $b->asset->favorites->first();
                $b->asset->isFavorite = $fav ? true : false;
                $b->asset->favorite_id = $fav ? $fav->id : null;
            }
        });

        return Inertia::render("Home/Aktivitas", [
            "bookings" => $bookings
        ]);
    }
}
