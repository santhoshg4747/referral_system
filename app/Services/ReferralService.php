<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * The referral bonus amount.
     * 
     * @var float
     */
    protected $referralBonus = 10.00; // Default bonus amount

    /**
     * Minimum withdrawal amount.
     * 
     * @var float
     */
    protected $minWithdrawalAmount = 50.00;

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

    /**
     * Process a successful referral.
     *
     * @param Referral $referral
     * @param float|null $bonusAmount
     * @return bool
     */
    public function processSuccessfulReferral(Referral $referral, ?float $bonusAmount = null): bool
    {
        $bonusAmount = $bonusAmount ?? $this->referralBonus;
        
        try {
            // Update the referral record
            $referral->markAsCompleted($bonusAmount);
            
            // Update the referrer's stats
            $referral->referrer->increment('referral_earnings', $bonusAmount);
            $referral->referrer->increment('referral_count');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process referral', [
                'referral_id' => $referral->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return false;
        }
    }

    /**
     * Get the referral stats for a user.
     *
     * @param User $user
     * @return array
     */
    public function getUserReferralStats(User $user): array
    {
        return [
            'total_referrals' => $user->referral_count,
            'pending_referrals' => $user->pending_referrals_count,
            'total_earnings' => (float) $user->referral_earnings,
            'available_balance' => (float) $user->referral_earnings - (float) $user->referral_withdrawn,
            'referral_link' => $user->referral_link,
            'referral_code' => $user->referral_code,
            'can_withdraw' => $this->canWithdraw($user),
            'min_withdrawal_amount' => $this->minWithdrawalAmount,
        ];
    }

    /**
     * Check if a user can withdraw their referral earnings.
     *
     * @param User $user
     * @return bool
     */
    public function canWithdraw(User $user): bool
    {
        $availableBalance = (float) $user->referral_earnings - (float) $user->referral_withdrawn;
        return $availableBalance >= $this->minWithdrawalAmount;
    }

    /**
     * Process a withdrawal request.
     *
     * @param User $user
     * @param float $amount
     * @return array
     */
    public function processWithdrawal(User $user, float $amount): array
    {
        $availableBalance = (float) $user->referral_earnings - (float) $user->referral_withdrawn;
        
        if ($amount > $availableBalance) {
            return [
                'success' => false,
                'message' => 'Insufficient balance for withdrawal.',
            ];
        }
        
        if ($amount < $this->minWithdrawalAmount) {
            return [
                'success' => false,
                'message' => "Minimum withdrawal amount is {$this->minWithdrawalAmount}.",
            ];
        }
        
        try {
            // Here you would typically integrate with a payment gateway
            // For now, we'll just update the user's withdrawn amount
            $user->increment('referral_withdrawn', $amount);
            
            // Log the withdrawal
            // You might want to create a Withdrawal model for this
            
            return [
                'success' => true,
                'message' => 'Withdrawal request submitted successfully.',
                'amount' => $amount,
                'balance' => $availableBalance - $amount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process withdrawal', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process withdrawal. Please try again later.',
            ];
        }
    }

    /**
     * Get the top referrers.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopReferrers(int $limit = 10)
    {
        return User::query()
            ->where('referral_count', '>', 0)
            ->orderBy('referral_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'referral_count', 'referral_earnings']);
    }

    /**
     * Get the referral bonus amount.
     * 
     * @return float
     */
    public function getReferralBonus(): float
    {
        return $this->referralBonus;
    }

    /**
     * Set the referral bonus amount.
     * 
     * @param float $amount
     * @return $this
     */
    public function setReferralBonus(float $amount): self
    {
        $this->referralBonus = $amount;
        return $this;
    }

    /**
     * Get the minimum withdrawal amount.
     * 
     * @return float
     */
    public function getMinWithdrawalAmount(): float
    {
        return $this->minWithdrawalAmount;
    }

    /**
     * Set the minimum withdrawal amount.
     * 
     * @param float $amount
     * @return $this
     */
    public function setMinWithdrawalAmount(float $amount): self
    {
        $this->minWithdrawalAmount = $amount;
        return $this;
    }
}
