<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSpinHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spin_wheel_campaign_id',
        'spin_wheel_prize_id',
        'prize_won',
        'prize_value',
        'generated_coupon_code',
        'coupon_claimed',
        'coupon_claimed_at',
        'order_id',
    ];

    protected $casts = [
        'prize_value' => 'decimal:2',
        'coupon_claimed' => 'boolean',
        'coupon_claimed_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the spin wheel campaign
     */
    public function spinWheelCampaign(): BelongsTo
    {
        return $this->belongsTo(SpinWheelCampaign::class);
    }

    /**
     * Get the prize won
     */
    public function spinWheelPrize(): BelongsTo
    {
        return $this->belongsTo(SpinWheelPrize::class);
    }

    /**
     * Get the order (if spin was after purchase)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if coupon was generated
     */
    public function hasCoupon(): bool
    {
        return !empty($this->generated_coupon_code);
    }

    /**
     * Check if coupon is claimed
     */
    public function isCouponClaimed(): bool
    {
        return $this->coupon_claimed;
    }

    /**
     * Mark coupon as claimed
     */
    public function claimCoupon(): bool
    {
        if (!$this->hasCoupon() || $this->isCouponClaimed()) {
            return false;
        }

        $this->coupon_claimed = true;
        $this->coupon_claimed_at = now();

        return $this->save();
    }

    /**
     * Check if prize has value
     */
    public function hasValuePrize(): bool
    {
        return $this->spinWheelPrize && $this->spinWheelPrize->hasValue();
    }

    /**
     * Get prize display information
     */
    public function getPrizeInfo(): array
    {
        $prize = $this->spinWheelPrize;

        return [
            'name' => $this->prize_won,
            'value' => $this->prize_value,
            'type' => $prize ? $prize->prize_type : 'unknown',
            'description' => $prize ? $prize->getPrizeDescription() : $this->prize_won,
            'has_coupon' => $this->hasCoupon(),
            'coupon_code' => $this->generated_coupon_code,
            'coupon_claimed' => $this->isCouponClaimed(),
        ];
    }
}