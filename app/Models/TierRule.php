<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiered_discount_id',
        'min_quantity',
        'min_amount',
        'discount_type',
        'discount_value',
        'tier_name',
        'tier_order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the tiered discount this rule belongs to
     */
    public function tieredDiscount(): BelongsTo
    {
        return $this->belongsTo(TieredDiscount::class);
    }

    /**
     * Calculate discount amount for given base amount
     */
    public function calculateDiscount(float $baseAmount): float
    {
        if ($this->discount_type === 'percentage') {
            return ($baseAmount * $this->discount_value) / 100;
        }

        if ($this->discount_type === 'fixed_amount') {
            return min($this->discount_value, $baseAmount);
        }

        return 0;
    }

    /**
     * Get discount percentage (for display purposes)
     */
    public function getDiscountPercentage(): float
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value;
        }

        return 0;
    }

    /**
     * Get fixed discount amount
     */
    public function getFixedAmount(): float
    {
        if ($this->discount_type === 'fixed_amount') {
            return $this->discount_value;
        }

        return 0;
    }

    /**
     * Check if quantity/amount qualifies for this tier
     */
    public function qualifies(int $quantity = 0, float $amount = 0): bool
    {
        $tieredDiscount = $this->tieredDiscount;

        if ($tieredDiscount->tier_type === 'quantity') {
            return $quantity >= $this->min_quantity;
        }

        if ($tieredDiscount->tier_type === 'amount') {
            return $amount >= $this->min_amount;
        }

        return false;
    }

    /**
     * Get tier display name
     */
    public function getDisplayName(): string
    {
        if ($this->tier_name) {
            return $this->tier_name;
        }

        $tieredDiscount = $this->tieredDiscount;

        if ($tieredDiscount->tier_type === 'quantity') {
            return "Buy {$this->min_quantity}+ items";
        }

        if ($tieredDiscount->tier_type === 'amount') {
            return "Spend ₹{$this->min_amount}+";
        }

        return "Tier {$this->tier_order}";
    }

    /**
     * Get tier benefit description
     */
    public function getBenefitDescription(): string
    {
        if ($this->discount_type === 'percentage') {
            return "Get {$this->discount_value}% off";
        }

        if ($this->discount_type === 'fixed_amount') {
            return "Save ₹{$this->discount_value}";
        }

        return "Special discount";
    }
}