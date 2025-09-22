<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WishlistShare extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'name',
        'description',
        'expires_at',
        'is_public',
        'view_count'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_public' => 'boolean',
        'view_count' => 'integer'
    ];

    /**
     * Get the user that owns the shared wishlist
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this shared wishlist
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistShareItem::class);
    }

    /**
     * Check if the shared wishlist is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the share URL
     */
    public function getShareUrlAttribute(): string
    {
        return route('wishlist.shared.view', $this->token);
    }

    /**
     * Get formatted expiration date
     */
    public function getExpiresAtFormattedAttribute(): ?string
    {
        return $this->expires_at?->format('M j, Y');
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        return $this->expires_at ? now()->diffInDays($this->expires_at, false) : null;
    }

    /**
     * Scope for active (non-expired) shares
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for public shares
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}