<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        return $this->sendResponse($user->load('referredBy'));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->sendResponse($user, 'Profile updated successfully');
    }

    /**
     * Get the authenticated user's referrals.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function referrals(Request $request)
    {
        $referrals = $request->user()->referrals()
            ->with('referredUser')
            ->latest()
            ->paginate(10);

        return $this->sendResponse($referrals);
    }

    /**
     * Get the authenticated user's referral statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function referralStats(Request $request)
    {
        $user = $request->user();
        $referralService = app(\App\Services\ReferralService::class);
        
        return $this->sendResponse([
            'total_referrals' => $user->referral_count,
            'pending_referrals' => $user->referrals()->pending()->count(),
            'completed_referrals' => $user->referrals()->completed()->count(),
            'total_earnings' => (float) $user->referral_earnings,
            'referral_link' => $user->referral_link,
            'referral_code' => $user->referral_code,
        ]);
    }
}
