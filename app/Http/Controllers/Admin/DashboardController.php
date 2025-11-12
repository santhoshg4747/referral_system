<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $stats = [
            'totalUsers' => User::count(),
            'activeUsers' => User::count(), // Since we don't have is_active, all users are considered active
            'totalReferrals' => User::sum('referral_count'),
            'pendingReferrals' => 0, // You can implement this based on your business logic
        ];

        $recentUsers = User::latest()
            ->paginate(5, ['*'], 'recent_page')
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->is_admin ? 'Admin' : 'User',
                    'referral_count' => $user->referral_count,
                    'created_at' => $user->created_at->toDateTimeString(),
                ];
            });

        $topReferrers = User::where('referral_count', '>', 0)
            ->orderBy('referral_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'referral_count' => $user->referral_count,
                    'referral_earnings' => $user->referral_earnings,
                ];
            });

        $search = request()->input('search');
        
        $users = User::query()
            ->when($search, function ($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'email', 'is_admin', 'referral_code', 'referral_count', 'created_at'])
            ->withCount('referrals')
            ->latest()
            ->paginate(5)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'referral_code' => $user->referral_code,
                    'referral_count' => $user->referral_count,
                    'created_at' => $user->created_at->toDateTimeString(),
                    'role' => $user->is_admin ? 'Admin' : 'User'
                ];
            });

        $data = [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'topReferrers' => $topReferrers,
            'users' => $users,
            'filters' => [
                'search' => $search ?? '',
            ],
        ];

        // Debug: Log the search query and results
        \Log::info('Dashboard search', [
            'search' => $search,
            'users_count' => $users->count(),
            'request_query' => request()->query()
        ]);

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Admin/Dashboard', $data);
    }
}
