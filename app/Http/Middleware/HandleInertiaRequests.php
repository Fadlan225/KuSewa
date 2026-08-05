<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $unreadCount = 0;
        
        if ($user) {
            $user->load('ownerProfile');
            
            $unreadCount = \App\Models\room_chat::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('ownerProfile', function ($query) use ($user) {
                      $query->where('user_id', $user->id);
                  });
            })->whereHas('messages', function($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->where('is_read', false);
            })->withCount(['messages as unread' => function($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->where('is_read', false);
            }])->get()->sum('unread');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'unreadCount' => $unreadCount,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
