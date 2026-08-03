<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetView;
use Inertia\Inertia;

class AssetViewController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetView::with(['asset.firstImage', 'asset.type.category', 'asset.defaultPricing'])
            ->where('user_id', auth()->id())
            ->orderBy('last_viewed', 'desc');

        $views = $query->paginate(24);

        if ($request->wantsJson()) {
            return response()->json($views);
        }

        return Inertia::render('Home/LastSeen', [
            'initialViews' => $views,
        ]);
    }

    public function destroy(AssetView $assetView)
    {
        if ($assetView->user_id !== auth()->id()) {
            abort(403);
        }
        
        $assetView->delete();

        return redirect()->back();
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $ids = $request->input('ids');

        if (count($ids) === 1 && $ids[0] === 'all') {
            AssetView::where('user_id', auth()->id())->delete();
        } else {
            AssetView::where('user_id', auth()->id())
                ->whereIn('id', $ids)
                ->delete();
        }

        return redirect()->back();
    }
}
