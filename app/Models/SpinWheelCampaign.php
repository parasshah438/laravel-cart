<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SpinWheelCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_spins_per_user',
        'total_spins_allowed',
        'current_total_spins',
        'min_order_amount',
        'requires_purchase',
        'first_time_users_only',
        'sale_event_id',
        'wheel_config',
        'background_image',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'requires_purchase' => 'boolean',
        'first_time_users_only' => 'boolean',
        'is_active' => 'boolean',
        'wheel_config' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the sale event this campaign belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get prizes for this campaign
     */
    public function prizes(): HasMany
    {
        return $this->hasMany(SpinWheelPrize::class);
    }

    /**
     * Get user spin history
     */
    public function userSpins(): HasMany
    {
        return $this->hasMany(UserSpinHistory::class);
    }

    /**
     * Scope: Active campaigns
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where(function ($q) {
                $q->where('total_spins_allowed', 0)
                  ->orWhereRaw('current_total_spins < total_spins_allowed');
            });
    }

    /**
     * Check if campaign is currently active
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

        if ($this->total_spins_allowed > 0 && $this->current_total_spins >= $this->total_spins_allowed) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can spin the wheel
     */
    public function canUserSpin(int $userId, ?float $orderAmount = null): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Check if user has reached spin limit
        if ($this->max_spins_per_user > 0) {
            $userSpinCount = $this->userSpins()
                ->where('user_id', $userId)
                ->count();

            if ($userSpinCount >= $this->max_spins_per_user) {
                return false;
            }
        }

        // Check if purchase is required
        if ($this->requires_purchase && (!$orderAmount || $orderAmount < $this->min_order_amount)) {
            return false;
        }

        // Check if first-time users only
        if ($this->first_time_users_only) {
            $user = User::find($userId);
            if (!$user || $user->orders()->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Spin the wheel for user
     */
    public function spinForUser(int $userId, ?int $orderId = null): ?UserSpinHistory
    {
        if (!$this->canUserSpin($userId)) {
            return null;
        }

        // Get random prize based on probability
        $prize = $this->getRandomPrize();

        if (!$prize) {
            return null;
        }

        // Create spin history
        $spinHistory = $this->userSpins()->create([
            'user_id' => $userId,
            'spin_wheel_prize_id' => $prize->id,
            'prize_won' => $prize->prize_name,
            'prize_value' => $prize->prize_value,
            'order_id' => $orderId,
        ]);

        // Generate coupon if applicable
        if ($prize->prize_type === 'discount_coupon') {
            $couponCode = $this->generateCouponForPrize($prize);
            $spinHistory->generated_coupon_code = $couponCode;
            $spinHistory->save();
        }

        // Update counters
        $this->increment('current_total_spins');
        $prize->increment('current_winners');

        return $spinHistory;
    }

    /**
     * Get random prize based on probability
     */
    protected function getRandomPrize(): ?SpinWheelPrize
    {
        $availablePrizes = $this->prizes()
            ->where(function ($q) {
                $q->where('max_winners', 0)
                  ->orWhereRaw('current_winners < max_winners');
            })
            ->get();

        if ($availablePrizes->isEmpty()) {
            return null;
        }

        // Calculate total probability
        $totalProbability = $availablePrizes->sum('probability_percentage');

        if ($totalProbability <= 0) {
            return null;
        }

        // Generate random number
        $random = mt_rand(1, (int)($totalProbability * 100)) / 100;

        // Find winning prize
        $currentProbability = 0;
        foreach ($availablePrizes as $prize) {
            $currentProbability += $prize->probability_percentage;
            if ($random <= $currentProbability) {
                return $prize;
            }
        }

        return $availablePrizes->first();
    }

    /**
     * Generate coupon for prize
     */
    protected function generateCouponForPrize(SpinWheelPrize $prize): string
    {
        $couponConfig = $prize->coupon_config ?? [];

        do {
            $code = 'SPIN' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (DynamicCoupon::where('code', $code)->exists());

        DynamicCoupon::create([
            'code' => $code,
            'name' => "Spin Wheel Prize: {$prize->prize_name}",
            'type' => $couponConfig['type'] ?? 'percentage',
            'value' => $prize->prize_value,
            'usage_limit' => 1,
            'per_user_limit' => 1,
            'starts_at' => now(),
            'ends_at' => now()->addDays($couponConfig['valid_days'] ?? 30),
            'is_active' => true,
        ]);

        return $code;
    }

    /**
     * Get user's spin count
     */
    public function getUserSpinCount(int $userId): int
    {
        return $this->userSpins()->where('user_id', $userId)->count();
    }

    /**
     * Get remaining spins for user
     */
    public function getRemainingSpinsForUser(int $userId): int
    {
        if ($this->max_spins_per_user <= 0) {
            return -1; // Unlimited
        }

        $usedSpins = $this->getUserSpinCount($userId);
        return max(0, $this->max_spins_per_user - $usedSpins);
    }

    /**
     * Get total remaining spins for campaign
     */
    public function getTotalRemainingSpins(): int
    {
        if ($this->total_spins_allowed <= 0) {
            return -1; // Unlimited
        }

        return max(0, $this->total_spins_allowed - $this->current_total_spins);
    }

    /**
     * Get campaign statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_spins' => $this->current_total_spins,
            'unique_spinners' => $this->userSpins()->distinct('user_id')->count(),
            'prizes_won' => $this->userSpins()->where('prize_won', '!=', 'Nothing')->count(),
            'coupons_generated' => $this->userSpins()->whereNotNull('generated_coupon_code')->count(),
            'coupons_claimed' => $this->userSpins()->where('coupon_claimed', true)->count(),
        ];
    }
}