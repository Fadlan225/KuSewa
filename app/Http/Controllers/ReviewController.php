<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\booking;
use App\Models\review;
use App\Models\review_tag;
use App\Models\review_tag_item;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create($id)
    {
        $booking = booking::with(['asset.ownerProfile.user', 'asset.firstImage', 'asset.reviews'])->findOrFail($id);

        // Pastikan hanya user yang bersangkutan yang bisa memberikan ulasan
        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('aktivitas.transaksi')->with('error', 'Anda tidak memiliki akses ke ulasan ini.');
        }

        // Cek apakah booking ini sudah pernah diberi ulasan
        if (review::where('booking_id', $id)->exists()) {
            return redirect()->route('aktivitas.transaksi')->with('error', 'Anda sudah memberikan ulasan untuk penyewaan ini.');
        }

        $avgRating = $booking->asset->reviews->avg('rating') ?? 0;
        $assetData = [
            'name' => $booking->asset->title ?? $booking->asset_name,
            'image' => $booking->asset->firstImage ? $booking->asset->firstImage->image_url : null,
            'host' => $booking->asset->ownerProfile->user->name ?? 'Anonim',
            'rating' => round($avgRating, 1)
        ];

        // Ambil tag yang relevan dengan tipe aset
        $availableTags = review_tag::where('asset_type_id', $booking->asset->asset_type_id)
            ->select('id', 'name')
            ->get();

        return Inertia::render('Home/Reviews', [
            'booking_id' => $id,
            'booking_code' => $booking->booking_code,
            'asset' => $assetData,
            'availableTags' => $availableTags
        ]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
            'tags' => 'array',
            'tags.*' => 'integer|exists:review_tags,id'
        ]);

        $booking = booking::findOrFail($id);

        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('aktivitas.transaksi')->with('error', 'Akses ditolak.');
        }

        if (review::where('booking_id', $id)->exists()) {
            return redirect()->route('aktivitas.transaksi')->with('error', 'Anda sudah memberikan ulasan.');
        }

        // Simpan review utama
        $review = review::create([
            'user_id' => Auth::id(),
            'booking_id' => $id,
            'rating' => $request->rating,
            'review' => $request->review ?? '',
        ]);

        // Simpan tag (jika ada)
        if (!empty($request->tags)) {
            foreach ($request->tags as $tag_id) {
                review_tag_item::create([
                    'review_id' => $review->id,
                    'review_tag_id' => $tag_id,
                ]);
            }
        }

        return redirect()->route('aktivitas.transaksi')->with('success', 'Ulasan berhasil dikirim! Terima kasih.');
    }

    public function myReviews()
    {
        $userId = Auth::id();
        $reviews = review::with(['booking.asset.firstImage', 'booking.asset.type.category', 'items.reviewTag'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return Inertia::render('Home/Activity/MyReviews', [
            'reviews' => $reviews
        ]);
    }
}
