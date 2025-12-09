<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sale_event_id',
        'bundle_deal_id',
        'dynamic_coupon_id',
        'original_amount',
        'sale_discount_amount',
        'bundle_discount_amount',
        'coupon_discount_amount',
        'final_amount',
        'sale_tags',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'sale_discount_amount' => 'decimal:2',
        'bundle_discount_amount' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'sale_tags' => 'array',
    ];

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the sale event
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the bundle deal
     */
    public function bundleDeal(): BelongsTo
    {
        return $this->belongsTo(BundleDeal::class);
    }

    /**
     * Get the dynamic coupon
     */
    public function dynamicCoupon(): BelongsTo
    {
        return $this->belongsTo(DynamicCoupon::class);
    }

    /**
     * Calculate total discount amount
     */
    public function getTotalDiscountAmount(): float
    {
        return $this->sale_discount_amount + 
               $this->bundle_discount_amount + 
               $this->coupon_discount_amount;
    }

    /**
     * Calculate discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if ($this->original_amount == 0) {
            return 0;
        }

        return ($this->getTotalDiscountAmount() / $this->original_amount) * 100;
    }

    /**
     * Check if order has sale tag
     */
    public function hasSaleTag(string $tag): bool
    {
        return in_array($tag, $this->sale_tags ?? []);
    }

    /**
     * Add sale tag
     */
    public function addSaleTag(string $tag): void
    {
        $tags = $this->sale_tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['sale_tags' => $tags]);
        }
    }

    /**
     * Get sale breakdown
     */
    public function getSaleBreakdown(): array
    {
        return [
            'original_amount' => $this->original_amount,
            'discounts' => [
                'sale_discount' => $this->sale_discount_amount,
                'bundle_discount' => $this->bundle_discount_amount,
                'coupon_discount' => $this->coupon_discount_amount,
            ],
            'total_discount' => $this->getTotalDiscountAmount(),
            'final_amount' => $this->final_amount,
            'savings_percentage' => $this->getDiscountPercentage(),
        ];
    }
}