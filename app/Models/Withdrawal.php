<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Withdrawal extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_details',
        'status',
        'rejection_reason',
        'admin_notes',
        'processed_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->status)) {
                $withdrawal->status = 'pending';
            }
        });
    }

    /**
     * Get the user that owns the withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending withdrawals.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed withdrawals.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include rejected withdrawals.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Mark the withdrawal as processing.
     *
     * @param string|null $adminNotes
     * @return bool
     */
    public function markAsProcessing(?string $adminNotes = null): bool
    {
        return $this->update([
            'status' => 'processing',
            'processed_at' => now(),
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    /**
     * Mark the withdrawal as completed.
     *
     * @param string|null $adminNotes
     * @return bool
     */
    public function markAsCompleted(?string $adminNotes = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    /**
     * Mark the withdrawal as rejected.
     *
     * @param string $reason
     * @param string|null $adminNotes
     * @return bool
     */
    public function markAsRejected(string $reason, ?string $adminNotes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    /**
     * Check if the withdrawal is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the withdrawal is processing.
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the withdrawal is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the withdrawal is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
