<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Property;
use App\Models\booking;

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
        if ($user) {
            $user->load('ownerProfile');
        }

        $ownerActivityCounts = [
            'propertyReview' => 0,
            'bookingReview' => 0,
            'verificationReview' => 0,
        ];

        if ($user) {
            $ownerActivityCounts['propertyReview'] = Property::where('user_id', $user->id)
                ->where('verification_status', 'pending')
                ->count();

            $ownerActivityCounts['bookingReview'] = booking::whereHas(
                'asset',
                fn ($query) => $query->where('owner_profile_id', $user->ownerProfile?->id)
            )->where('booking_status', 'pending')->count();

            $ownerActivityCounts['verificationReview'] = $user->ownerProfile?->status === 'pending' ? 1 : 0;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'ownerActivityCounts' => $ownerActivityCounts,
        ];
    }
}
