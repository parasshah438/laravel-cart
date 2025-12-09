<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class WishlistSaleAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'desired_price',
        'percentage_drop',
        'is_active',
        'last_notified_at',
        'notification_count',
        'current_sale_event_id',
    ];

    protected $casts = [
        'desired_price' => 'decimal:2',
        'percentage_drop' => 'decimal:2',
        'is_active' => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the current sale event
     */
    public function currentSaleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class, 'current_sale_event_id');
    }

    /**
     * Scope: Active alerts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if product price qualifies for alert
     */
    public function shouldAlert(float $currentPrice): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check desired price
        if ($this->desired_price && $currentPrice <= $this->desired_price) {
            return true;
        }

        // Check percentage drop
        if ($this->percentage_drop && $this->product) {
            $originalPrice = $this->product->original_price ?? $this->product->price;
            $dropPercentage = (($originalPrice - $currentPrice) / $originalPrice) * 100;
            
            if ($dropPercentage >= $this->percentage_drop) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send alert to user
     */
    public function sendAlert(float $currentPrice, ?int $saleEventId = null): bool
    {
        if (!$this->shouldAlert($currentPrice)) {
            return false;
        }

        // Create notification
        SaleNotification::create([
            'user_id' => $this->user_id,
            'type' => 'price_drop_alert',
            'sale_event_id' => $saleEventId,
            'product_id' => $this->product_id,
            'title' => 'Price Drop Alert!',
            'message' => "Great news! {$this->product->name} is now available for ₹{$currentPrice}",
            'action_url' => route('product.show', $this->product->slug),
        ]);

        // Update alert record
        $this->last_notified_at = now();
        $this->notification_count++;
        $this->current_sale_event_id = $saleEventId;
        $this->save();

        return true;
    }

    /**
     * Deactivate alert
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }
}