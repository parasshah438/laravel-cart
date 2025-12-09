<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SaleBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_event_id',
        'title',
        'description',
        'image_url',
        'position',
        'priority',
        'is_active',
        'device_type',
        'click_url',
        'display_conditions',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_conditions' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the sale event
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get banner interactions
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(BannerInteraction::class);
    }

    /**
     * Scope active banners
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_time')
                  ->orWhere('start_time', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_time')
                  ->orWhere('end_time', '>=', now());
            });
    }

    /**
     * Scope by position
     */
    public function scopeByPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    /**
     * Scope by device type
     */
    public function scopeByDevice(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType)
            ->orWhereNull('device_type');
    }

    /**
     * Check if banner is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_time && $this->start_time > $now) {
            return false;
        }

        if ($this->end_time && $this->end_time < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if banner meets display conditions
     */
    public function meetsDisplayConditions(array $userContext = []): bool
    {
        if (empty($this->display_conditions)) {
            return true;
        }

        foreach ($this->display_conditions as $condition => $value) {
            switch ($condition) {
                case 'min_cart_value':
                    if (!isset($userContext['cart_value']) || $userContext['cart_value'] < $value) {
                        return false;
                    }
                    break;

                case 'user_type':
                    if (!isset($userContext['user_type']) || $userContext['user_type'] !== $value) {
                        return false;
                    }
                    break;

                case 'location':
                    if (!isset($userContext['location']) || !in_array($userContext['location'], $value)) {
                        return false;
                    }
                    break;

                case 'visited_products':
                    if (!isset($userContext['visited_products']) || 
                        count(array_intersect($userContext['visited_products'], $value)) === 0) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Track banner click
     */
    public function trackClick(?int $userId = null, array $metadata = []): void
    {
        $this->interactions()->create([
            'user_id' => $userId,
            'interaction_type' => 'click',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Track banner view
     */
    public function trackView(?int $userId = null, array $metadata = []): void
    {
        $this->interactions()->create([
            'user_id' => $userId,
            'interaction_type' => 'view',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get banner performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $totalViews = $this->interactions()->where('interaction_type', 'view')->count();
        $totalClicks = $this->interactions()->where('interaction_type', 'click')->count();

        return [
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'click_through_rate' => $totalViews > 0 ? ($totalClicks / $totalViews) * 100 : 0,
            'unique_users_clicked' => $this->interactions()
                ->where('interaction_type', 'click')
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),
        ];
    }
}