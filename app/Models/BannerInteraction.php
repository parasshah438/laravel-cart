<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_banner_id',
        'user_id',
        'session_id',
        'interaction_type',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the sale banner
     */
    public function saleBanner(): BelongsTo
    {
        return $this->belongsTo(SaleBanner::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Track banner interaction
     */
    public static function track(array $data): self
    {
        return self::create(array_merge($data, [
            'created_at' => now(),
        ]));
    }

    /**
     * Get interactions by type for a banner
     */
    public static function getByType(int $bannerId, string $type): int
    {
        return self::where('sale_banner_id', $bannerId)
            ->where('interaction_type', $type)
            ->count();
    }

    /**
     * Get unique user interactions for a banner
     */
    public static function getUniqueUsers(int $bannerId, string $type): int
    {
        return self::where('sale_banner_id', $bannerId)
            ->where('interaction_type', $type)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();
    }
}