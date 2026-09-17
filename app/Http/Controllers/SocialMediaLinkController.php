<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SocialMediaLinkController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'platform_name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);

        $maxOrder = SocialMediaLink::max('order') ?? 0;

        SocialMediaLink::create([
            'platform_name' => $request->platform_name,
            'url' => $request->url,
            'is_active' => true,
            'order' => $maxOrder + 1,
        ]);

        Cache::forget('social_links');

        return redirect()->back();
    }

    public function update(Request $request, SocialMediaLink $socialMediaLink)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'platform_name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $socialMediaLink->update([
            'platform_name' => $request->platform_name,
            'url' => $request->url,
            'is_active' => $request->is_active ?? true,
        ]);

        Cache::forget('social_links');

        return redirect()->back();
    }

    public function destroy(Request $request, SocialMediaLink $socialMediaLink)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $socialMediaLink->delete();

        Cache::forget('social_links');

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'links' => 'required|array',
            'links.*.id' => 'required|exists:social_media_links,id',
            'links.*.order' => 'required|integer',
        ]);

        foreach ($request->links as $linkData) {
            SocialMediaLink::where('id', $linkData['id'])->update(['order' => $linkData['order']]);
        }

        Cache::forget('social_links');

        return response()->json(['message' => 'Urutan berhasil diperbarui']);
    }
}
