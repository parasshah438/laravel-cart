<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChallengeParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_challenge_id',
        'current_progress',
        'target_progress',
        'progress_percentage',
        'status',
        'completed_at',
        'reward_claimed_at',
        'reward_coupon_code',
        'orders_count',
        'amount_spent',
    ];

    protected $casts = [
        'current_progress' => 'decimal:2',
        'target_progress' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'amount_spent' => 'decimal:2',
        'completed_at' => 'datetime',
        'reward_claimed_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale challenge
     */
    public function saleChallenge(): BelongsTo
    {
        return $this->belongsTo(SaleChallenge::class);
    }

    /**
     * Check if challenge is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if reward is claimed
     */
    public function isRewardClaimed(): bool
    {
        return $this->reward_claimed_at !== null;
    }

    /**
     * Mark reward as claimed
     */
    public function claimReward(): bool
    {
        if (!$this->isCompleted() || $this->isRewardClaimed()) {
            return false;
        }

        $this->reward_claimed_at = now();
        $this->status = 'rewarded';

        return $this->save();
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->target_progress <= 0) {
            return 0;
        }

        return min(100, ($this->current_progress / $this->target_progress) * 100);
    }

    /**
     * Get remaining progress needed
     */
    public function getRemainingProgress(): float
    {
        return max(0, $this->target_progress - $this->current_progress);
    }

    /**
     * Check if challenge has failed (expired without completion)
     */
    public function hasFailed(): bool
    {
        if ($this->status === 'failed') {
            return true;
        }

        if ($this->saleChallenge && $this->saleChallenge->hasEnded() && !$this->isCompleted()) {
            $this->status = 'failed';
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Get challenge type specific progress description
     */
    public function getProgressDescription(): string
    {
        $challenge = $this->saleChallenge;

        if (!$challenge) {
            return "Progress: {$this->progress_percentage}%";
        }

        switch ($challenge->type) {
            case 'spend_amount':
                return "Spent: ₹{$this->amount_spent} / ₹{$this->target_progress}";
            case 'buy_quantity':
                return "Bought: {$this->orders_count} / {$this->target_progress} products";
            case 'visit_days':
                return "Visited: {$this->current_progress} / {$this->target_progress} days";
            default:
                return "Progress: {$this->current_progress} / {$this->target_progress}";
        }
    }

    /**
     * Get time remaining for challenge completion
     */
    public function getTimeRemaining(): int
    {
        if (!$this->saleChallenge) {
            return 0;
        }

        return $this->saleChallenge->getTimeRemaining();
    }
}