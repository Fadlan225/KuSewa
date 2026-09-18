<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\HelpCategory;
use App\Models\HelpArticle;
use App\Models\HelpArticleFeedback;

class HelpCenterController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'penyewa');

        $categories = HelpCategory::where('target_audience', $tab)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'children.articles' => function($q) {
                $q->where('is_published', true)->orderBy('sort_order')->take(5);
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Home/Support/PusatBantuan/Index', [
            'tab' => $tab,
            'categories' => $categories
        ]);
    }

    public function category($id)
    {
        $category = HelpCategory::where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'children.articles' => function($q) {
                $q->where('is_published', true)->orderBy('sort_order');
            }, 'articles' => function($q) {
                $q->where('is_published', true)->orderBy('sort_order');
            }])
            ->findOrFail($id);

        // Also fetch all root categories for the sidebar (for the same audience)
        $sidebarCategories = HelpCategory::where('target_audience', $category->target_audience)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Home/Support/PusatBantuan/Category', [
            'category' => $category,
            'sidebarCategories' => $sidebarCategories,
            'tab' => $category->target_audience
        ]);
    }

    public function article($slug)
    {
        $article = HelpArticle::where('slug', $slug)
            ->where('is_published', true)
            ->with(['category', 'category.parent'])
            ->firstOrFail();

        // Get sidebar categories for the audience of this article
        $audience = $article->category->target_audience ?? ($article->category->parent->target_audience ?? 'penyewa');

        $sidebarCategories = HelpCategory::where('target_audience', $audience)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Home/Support/PusatBantuan/Article', [
            'article' => $article,
            'sidebarCategories' => $sidebarCategories,
            'tab' => $audience
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json([]);
        }

        $articles = HelpArticle::where('is_published', true)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content_desktop', 'like', "%{$query}%")
                  ->orWhere('content_mobile', 'like', "%{$query}%")
                  ->orWhere('summary_answer', 'like', "%{$query}%");
            })
            ->with('category.parent')
            ->take(10)
            ->get();

        return response()->json($articles);
    }

    public function submitFeedback(Request $request, $id)
    {
        $validated = $request->validate([
            'is_helpful' => 'required|boolean',
            'reason' => 'nullable|string|max:1000'
        ]);

        $article = HelpArticle::findOrFail($id);

        HelpArticleFeedback::create([
            'help_article_id' => $article->id,
            'user_id' => auth()->id(),
            'is_helpful' => $validated['is_helpful'],
            'reason' => $validated['reason']
        ]);

        return back()->with('success', 'Terima kasih atas masukan Anda.');
    }
}
