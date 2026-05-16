<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MaintenanceWindow extends Model
{
    protected $fillable = [
        'title',
        'message',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Return the currently active maintenance window (if any).
     * "Active" = is_active=true AND now is within [starts_at, ends_at].
     */
    public static function current(): ?self
    {
        return static::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at',   '>=', now())
            ->orderByDesc('starts_at')
            ->first();
    }

    /**
     * Return the next upcoming maintenance window (not yet started).
     */
    public static function upcoming(): ?self
    {
        return static::where('is_active', true)
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->first();
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'Disabled';
        if (now()->lt($this->starts_at)) return 'Scheduled';
        if (now()->between($this->starts_at, $this->ends_at)) return 'Active';
        return 'Ended';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status_label) {
            'Active'     => 'danger',
            'Scheduled'  => 'warning',
            'Ended'      => 'secondary',
            default      => 'secondary',
        };
    }
}
