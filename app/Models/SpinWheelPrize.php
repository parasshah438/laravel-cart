<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinWheelPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'spin_wheel_campaign_id',
        'prize_name',
        'prize_type',
        'prize_value',
        'probability_percentage',
        'max_winners',
        'current_winners',
        'coupon_config',
        'product_id',
        'display_text',
        'icon',
        'color',
    ];

    protected $casts = [
        'prize_value' => 'decimal:2',
        'probability_percentage' => 'decimal:2',
        'coupon_config' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the spin wheel campaign
     */
    public function spinWheelCampaign(): BelongsTo
    {
        return $this->belongsTo(SpinWheelCampaign::class);
    }

    /**
     * Get the product (for free product prizes)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if prize is still available
     */
    public function isAvailable(): bool
    {
        if ($this->max_winners <= 0) {
            return true; // Unlimited winners
        }

        return $this->current_winners < $this->max_winners;
    }

    /**
     * Get remaining winners count
     */
    public function getRemainingWinners(): int
    {
        if ($this->max_winners <= 0) {
            return -1; // Unlimited
        }

        return max(0, $this->max_winners - $this->current_winners);
    }

    /**
     * Get display text for wheel segment
     */
    public function getDisplayText(): string
    {
        if ($this->display_text) {
            return $this->display_text;
        }

        switch ($this->prize_type) {
            case 'discount_coupon':
                return "{$this->prize_value}% OFF";
            case 'cashback':
                return "₹{$this->prize_value} Cashback";
            case 'free_product':
                return $this->product ? $this->product->name : "Free Product";
            case 'free_shipping':
                return "Free Shipping";
            case 'points':
                return "{$this->prize_value} Points";
            case 'nothing':
                return "Try Again!";
            default:
                return $this->prize_name;
        }
    }

    /**
     * Get prize description
     */
    public function getPrizeDescription(): string
    {
        switch ($this->prize_type) {
            case 'discount_coupon':
                return "Get {$this->prize_value}% discount on your next order";
            case 'cashback':
                return "Receive ₹{$this->prize_value} cashback in your wallet";
            case 'free_product':
                return $this->product ? "Get {$this->product->name} for free" : "Get a free product";
            case 'free_shipping':
                return "Free shipping on your next order";
            case 'points':
                return "Earn {$this->prize_value} reward points";
            case 'nothing':
                return "Better luck next time!";
            default:
                return $this->prize_name;
        }
    }

    /**
     * Check if prize has value
     */
    public function hasValue(): bool
    {
        return $this->prize_type !== 'nothing';
    }
}