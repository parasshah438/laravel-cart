<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonationLog extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'admin_name',
        'admin_email',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
        'reason',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────────────────────
    // Computed attributes
    // ──────────────────────────────────────────────────────────────

    /**
     * Session duration formatted as h:mm:ss or mm:ss.
     */
    public function getDurationAttribute(): ?string
    {
        if (! $this->ended_at || ! $this->started_at) return null;

        // Always call from the earlier date to avoid negative results
        $secs = (int) $this->started_at->diffInSeconds($this->ended_at);

        if ($secs < 60)   return $secs . 's';

        $h = intdiv($secs, 3600);
        $m = intdiv($secs % 3600, 60);
        $s = $secs % 60;

        if ($h > 0) return sprintf('%dh %02dm', $h, $m);

        return sprintf('%dm %02ds', $m, $s);
    }

    /**
     * Is this session still active (never ended)?
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->ended_at === null;
    }
}
