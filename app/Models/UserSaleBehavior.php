<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSaleBehavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'sale_event_id',
        'action_type',
        'product_id',
        'coupon_code',
        'device_type',
        'user_agent',
        'ip_address',
        'referrer_url',
        'action_metadata',
    ];

    protected $casts = [
        'action_metadata' => 'array',
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
     * Get the sale event
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Track user behavior
     */
    public static function track(array $data): self
    {
        return self::create(array_merge($data, [
            'created_at' => now(),
        ]));
    }

    /**
     * Get behavior summary for session
     */
    public static function getSessionSummary(string $sessionId, int $saleEventId): array
    {
        $behaviors = self::where('session_id', $sessionId)
            ->where('sale_event_id', $saleEventId)
            ->get();

        $summary = [
            'total_actions' => $behaviors->count(),
            'products_viewed' => $behaviors->where('action_type', 'view_product')->count(),
            'cart_actions' => $behaviors->whereIn('action_type', ['add_to_cart', 'remove_from_cart'])->count(),
            'wishlist_actions' => $behaviors->where('action_type', 'add_to_wishlist')->count(),
            'coupons_applied' => $behaviors->where('action_type', 'apply_coupon')->count(),
            'completed_checkout' => $behaviors->where('action_type', 'checkout_complete')->exists(),
            'device_type' => $behaviors->first()?->device_type,
            'session_duration' => $behaviors->count() > 1 ? 
                $behaviors->last()->created_at->diffInMinutes($behaviors->first()->created_at) : 0,
        ];

        return $summary;
    }
}