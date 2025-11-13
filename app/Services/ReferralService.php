<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Create a new referral relationship.
     *
     * @param User $referrer
     * @param User $referredUser
     * @param string|null $referralCode
     * @return Referral
     */
    public function createReferral(User $referrer, User $referredUser, ?string $referralCode = null): Referral
    {
        $referralCode = $referralCode ?? $referrer->referral_code;
        
        // Create the referral record
        $referral = Referral::create([
            'user_id' => $referrer->id,
            'referred_user_id' => $referredUser->id,
            'referral_code_used' => $referralCode,
            'status' => 'pending',
            'earnings' => 0,
        ]);
        
        // Increment the referrer's referral count
        $referrer->increment('referral_count');
        
        return $referral;
    }
}
