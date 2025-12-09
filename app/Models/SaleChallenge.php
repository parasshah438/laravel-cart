<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SaleChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'target_value',
        'target_days',
        'reward_type',
        'reward_value',
        'reward_product_id',
        'max_participants',
        'current_participants',
        'per_user_attempts',
        'sale_event_id',
        'banner_image',
        'icon',
        'badge_image',
        'starts_at',
        'ends_at',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'reward_value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the sale event this challenge belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the reward product (if applicable)
     */
    public function rewardProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reward_product_id');
    }

    /**
     * Get user participations in this challenge
     */
    public function participations(): HasMany
    {
        return $this->hasMany(UserChallengeParticipation::class);
    }

    /**
     * Get active participations
     */
    public function activeParticipations(): HasMany
    {
        return $this->participations()->where('status', 'active');
    }

    /**
     * Get completed participations
     */
    public function completedParticipations(): HasMany
    {
        return $this->participations()->where('status', 'completed');
    }

    /**
     * Scope: Active challenges
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where(function ($q) {
                $q->where('max_participants', 0)
                  ->orWhereRaw('current_participants < max_participants');
            });
    }

    /**
     * Scope: Featured challenges
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By challenge type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if challenge is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at > $now || $this->ends_at < $now) {
            return false;
        }

        if ($this->max_participants > 0 && $this->current_participants >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can participate in challenge
     */
    public function canUserParticipate(int $userId): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Check if user has already participated max times
        if ($this->per_user_attempts > 0) {
            $userAttempts = $this->participations()
                ->where('user_id', $userId)
                ->count();

            if ($userAttempts >= $this->per_user_attempts) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get user's current participation
     */
    public function getUserParticipation(int $userId): ?UserChallengeParticipation
    {
        return $this->participations()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Create user participation
     */
    public function createParticipation(int $userId): UserChallengeParticipation
    {
        $participation = $this->participations()->create([
            'user_id' => $userId,
            'target_progress' => $this->target_value,
            'status' => 'active',
        ]);

        $this->increment('current_participants');

        return $participation;
    }

    /**
     * Update user progress
     */
    public function updateUserProgress(int $userId, float $progressValue): bool
    {
        $participation = $this->getUserParticipation($userId);

        if (!$participation) {
            return false;
        }

        // Update progress based on challenge type
        switch ($this->type) {
            case 'spend_amount':
                $participation->increment('amount_spent', $progressValue);
                $participation->current_progress = $participation->amount_spent;
                break;

            case 'buy_quantity':
                $participation->increment('orders_count');
                $participation->current_progress = $participation->orders_count;
                break;

            default:
                $participation->current_progress += $progressValue;
        }

        // Calculate progress percentage
        $participation->progress_percentage = ($participation->current_progress / $participation->target_progress) * 100;

        // Check if challenge is completed
        if ($participation->current_progress >= $participation->target_progress) {
            $participation->status = 'completed';
            $participation->completed_at = now();

            // Generate reward
            $this->generateReward($participation);
        }

        $participation->save();

        return true;
    }

    /**
     * Generate reward for completed challenge
     */
    protected function generateReward(UserChallengeParticipation $participation): void
    {
        switch ($this->reward_type) {
            case 'discount_coupon':
                $couponCode = $this->generateCouponCode();
                $participation->reward_coupon_code = $couponCode;
                
                // Create dynamic coupon
                DynamicCoupon::create([
                    'code' => $couponCode,
                    'name' => "Challenge Reward: {$this->name}",
                    'type' => 'percentage',
                    'value' => $this->reward_value,
                    'usage_limit' => 1,
                    'per_user_limit' => 1,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(30),
                    'is_active' => true,
                ]);
                break;

            case 'cashback':
                // Handle cashback logic
                break;

            case 'points':
                // Handle points system
                break;
        }
    }

    /**
     * Generate unique coupon code
     */
    protected function generateCouponCode(): string
    {
        do {
            $code = 'CHALLENGE' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (DynamicCoupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get challenge progress description
     */
    public function getProgressDescription(): string
    {
        switch ($this->type) {
            case 'spend_amount':
                return "Spend ₹{$this->target_value}";
            case 'buy_quantity':
                return "Buy {$this->target_value} products";
            case 'visit_days':
                return "Visit for {$this->target_days} days";
            default:
                return "Complete challenge";
        }
    }

    /**
     * Get reward description
     */
    public function getRewardDescription(): string
    {
        switch ($this->reward_type) {
            case 'cashback':
                return "₹{$this->reward_value} cashback";
            case 'discount_coupon':
                return "{$this->reward_value}% discount coupon";
            case 'free_product':
                return $this->rewardProduct ? "Free {$this->rewardProduct->name}" : "Free product";
            case 'points':
                return "{$this->reward_value} points";
            case 'badge':
                return "Special badge";
            default:
                return "Surprise reward";
        }
    }

    /**
     * Get time remaining for challenge
     */
    public function getTimeRemaining(): int
    {
        if (!$this->isCurrentlyActive()) {
            return 0;
        }

        return max(0, $this->ends_at->diffInSeconds(now()));
    }

    /**
     * Get completion rate
     */
    public function getCompletionRate(): float
    {
        if ($this->current_participants === 0) {
            return 0;
        }

        $completedCount = $this->completedParticipations()->count();

        return ($completedCount / $this->current_participants) * 100;
    }
}