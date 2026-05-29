<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorGamificationController extends Controller
{
    /**
     * Get the authenticated vendor's gamification profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || $user->user_role !== 'store_vendor') {
            return response()->json([
                'success' => false,
                'message' => 'Only store vendors can access gamification',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reputation' => $user->reputation ?? 0,
                'rank' => $this->getUserRank($user),
                'badges' => $user->badges->map(function ($badge) {
                    return [
                        'id' => $badge->id,
                        'name' => $badge->name,
                        'description' => $badge->description,
                        'icon' => $badge->icon,
                        'level' => $badge->level,
                        'earned_at' => $badge->pivot->created_at,
                    ];
                }),
                'recent_points' => $user->reputations()
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($reputation) {
                        return [
                            'id' => $reputation->id,
                            'points' => $reputation->point,
                            'name' => $reputation->name,
                            'earned_at' => $reputation->created_at,
                            'meta' => $reputation->meta,
                        ];
                    }),
            ],
        ]);
    }

    /**
     * Get leaderboard of top vendors by reputation
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $timeframe = $request->input('timeframe', 'all'); // all, month, week

        $query = User::where('user_role', 'store_vendor')
            ->orderBy('reputation', 'desc');

        // Filter by timeframe based on recent points earned
        if ($timeframe === 'month') {
            $query->whereHas('reputations', function ($q) {
                $q->where('created_at', '>=', now()->subMonth());
            });
        } elseif ($timeframe === 'week') {
            $query->whereHas('reputations', function ($q) {
                $q->where('created_at', '>=', now()->subWeek());
            });
        }

        $vendors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => $vendors->map(function ($vendor, $index) {
                    return [
                        'rank' => $vendor->reputation > 0 ? ($vendors->firstItem() + $index) : null,
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                        'reputation' => $vendor->reputation ?? 0,
                        'badge_count' => $vendor->badges->count(),
                        'top_badge' => $vendor->badges->sortByDesc('level')->first()?->name,
                    ];
                }),
                'pagination' => [
                    'current_page' => $vendors->currentPage(),
                    'total_pages' => $vendors->lastPage(),
                    'per_page' => $vendors->perPage(),
                    'total' => $vendors->total(),
                ],
            ],
        ]);
    }

    /**
     * Get detailed point history for the authenticated vendor
     */
    public function pointHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || $user->user_role !== 'store_vendor') {
            return response()->json([
                'success' => false,
                'message' => 'Only store vendors can access this endpoint',
            ], 403);
        }

        $perPage = $request->input('per_page', 50);

        $points = $user->reputations()
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $points->map(function ($reputation) {
                    return [
                        'id' => $reputation->id,
                        'points' => $reputation->point,
                        'name' => $reputation->name,
                        'subject_type' => $reputation->subject_type,
                        'subject_id' => $reputation->subject_id,
                        'meta' => $reputation->meta,
                        'earned_at' => $reputation->created_at,
                    ];
                }),
                'pagination' => [
                    'current_page' => $points->currentPage(),
                    'total_pages' => $points->lastPage(),
                    'per_page' => $points->perPage(),
                    'total' => $points->total(),
                ],
                'summary' => [
                    'total_reputation' => $user->reputation ?? 0,
                    'total_points_earned' => $user->reputations()->sum('point'),
                ],
            ],
        ]);
    }

    /**
     * Get available badges and progress
     */
    public function badges(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || $user->user_role !== 'store_vendor') {
            return response()->json([
                'success' => false,
                'message' => 'Only store vendors can access this endpoint',
            ], 403);
        }

        $allBadges = [
            \App\Gamify\Badges\FirstOrderBadge::class,
            \App\Gamify\Badges\TenOrdersBadge::class,
            \App\Gamify\Badges\FiftyOrdersBadge::class,
            \App\Gamify\Badges\HundredOrdersBadge::class,
            \App\Gamify\Badges\PointMasterBadge::class,
        ];

        $earnedBadges = $user->badges->pluck('name')->toArray();

        $badgeData = [];
        foreach ($allBadges as $badgeClass) {
            $badge = new $badgeClass();
            $badgeData[] = [
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'level' => $badge->level,
                'earned' => in_array($badge->name, $earnedBadges),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'badges' => $badgeData,
                'earned_count' => count($earnedBadges),
                'total_count' => count($allBadges),
            ],
        ]);
    }

    /**
     * Get user's rank among all vendors
     */
    private function getUserRank(User $user): int
    {
        return User::where('user_role', 'store_vendor')
            ->where('reputation', '>', $user->reputation ?? 0)
            ->count() + 1;
    }
}
