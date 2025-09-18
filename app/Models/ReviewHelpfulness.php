<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewHelpfulness extends Model
{
    use HasFactory;

    protected $table = 'review_helpfulness';

    protected $fillable = [
        'review_id',
        'user_id',
        'is_helpful',
        'ip_address'
    ];

    protected $casts = [
        'is_helpful' => 'boolean'
    ];

    // ================================================================================================
    // 🔗 RELATIONSHIPS
    // ================================================================================================
    
    /**
     * Get the review this vote belongs to
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who voted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}