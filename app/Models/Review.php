<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id', 
        'order_id',
        'rating',
        'title',
        'comment',
        'verified_purchase',
        'photos',
        'videos',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'product_variant',
        'would_recommend',
        'ip_address',
        'user_agent',
        'last_updated_by_user'
    ];

    protected $casts = [
        'photos' => 'array',
        'videos' => 'array',
        'verified_purchase' => 'boolean',
        'would_recommend' => 'boolean',
        'approved_at' => 'datetime',
        'last_updated_by_user' => 'datetime'
    ];

    protected $dates = [
        'approved_at',
        'last_updated_by_user'
    ];

    // ================================================================================================
    // 🔗 RELATIONSHIPS
    // ================================================================================================
    
    /**
     * Get the user who wrote this review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order this review is associated with
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin who approved this review
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all helpfulness votes for this review
     */
    public function helpfulnessVotes(): HasMany
    {
        return $this->hasMany(ReviewHelpfulness::class);
    }

    /**
     * Get users who voted this review as helpful
     */
    public function helpfulVoters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_helpfulness')
                    ->wherePivot('is_helpful', true)
                    ->withTimestamps();
    }

    /**
     * Get users who voted this review as not helpful
     */
    public function notHelpfulVoters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_helpfulness')
                    ->wherePivot('is_helpful', false)
                    ->withTimestamps();
    }

    // ================================================================================================
    // 🎯 SCOPES
    // ================================================================================================
    
    /**
     * Scope for approved reviews only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for verified purchase reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }

    /**
     * Scope for reviews with specific rating
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope for reviews with photos
     */
    public function scopeWithPhotos($query)
    {
        return $query->whereNotNull('photos');
    }

    /**
     * Scope for most helpful reviews
     */
    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    /**
     * Scope for newest reviews
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope for oldest reviews
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    // ================================================================================================
    // 🛠️ HELPER METHODS
    // ================================================================================================
    
    /**
     * Check if review is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if review is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user has voted on this review's helpfulness
     */
    public function hasUserVoted(User $user): bool
    {
        return $this->helpfulnessVotes()
                    ->where('user_id', $user->id)
                    ->exists();
    }

    /**
     * Get user's vote on this review (true = helpful, false = not helpful, null = no vote)
     */
    public function getUserVote(User $user): ?bool
    {
        $vote = $this->helpfulnessVotes()
                     ->where('user_id', $user->id)
                     ->first();
        
        return $vote ? $vote->is_helpful : null;
    }

    /**
     * Get star rating display (★★★★☆)
     */
    public function getStarsAttribute(): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $this->rating ? '★' : '☆';
        }
        return $stars;
    }

    /**
     * Get rating percentage for display
     */
    public function getRatingPercentageAttribute(): int
    {
        return ($this->rating / 5) * 100;
    }

    /**
     * Get helpful percentage
     */
    public function getHelpfulPercentageAttribute(): int
    {
        $totalVotes = $this->helpful_count + $this->not_helpful_count;
        if ($totalVotes === 0) return 0;
        
        return round(($this->helpful_count / $totalVotes) * 100);
    }

    /**
     * Get review excerpt (first 150 characters)
     */
    public function getExcerptAttribute(): string
    {
        if (!$this->comment) return '';
        
        return strlen($this->comment) > 150 
            ? substr($this->comment, 0, 150) . '...'
            : $this->comment;
    }

    /**
     * Check if review has photos
     */
    public function hasPhotos(): bool
    {
        return !empty($this->photos);
    }

    /**
     * Get photo count
     */
    public function getPhotoCountAttribute(): int
    {
        return $this->photos ? count($this->photos) : 0;
    }

    /**
     * Approve the review
     */
    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id
        ]);
    }

    /**
     * Reject the review
     */
    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason
        ]);
    }
}
