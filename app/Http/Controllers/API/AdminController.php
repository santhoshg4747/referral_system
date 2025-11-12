<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get all users with pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function users(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $users = User::with('referredBy')
            ->latest()
            ->paginate($perPage);

        return $this->sendResponse($users);
    }

    /**
     * Get a specific user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function showUser(User $user)
    {
        return $this->sendResponse($user->load('referredBy', 'referrals.referredUser'));
    }

    /**
     * Update a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'sometimes|boolean',
            'referral_earnings' => 'sometimes|numeric|min:0',
            'referral_count' => 'sometimes|integer|min:0',
        ]);

        $user->update($validated);

        return $this->sendResponse($user, 'User updated successfully');
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting the last admin
        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return $this->sendError('Cannot delete the last admin user', [], 403);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully');
    }

    /**
     * Get all referrals with pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function allReferrals(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $referrals = Referral::with(['user', 'referredUser'])
            ->latest()
            ->paginate($perPage);

        return $this->sendResponse($referrals);
    }

    /**
     * Get system statistics.
     *
     * @return JsonResponse
     */
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_referrals' => Referral::count(),
            'pending_referrals' => Referral::pending()->count(),
            'completed_referrals' => Referral::completed()->count(),
            'total_referral_earnings' => (float) User::sum('referral_earnings'),
            'top_referrers' => User::select(['id', 'name', 'email', 'referral_count', 'referral_earnings'])
                ->where('referral_count', '>', 0)
                ->orderBy('referral_count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return $this->sendResponse($stats);
    }
}
