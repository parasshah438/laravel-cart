<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Create a notification and optionally broadcast it.
     */
    public static function notify(
        string $type,
        string $title,
        string $message,
        array  $data = []
    ): self {
        $notification = static::create([
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data ?: null,
        ]);

        // Broadcast to Reverb (silently fails if Reverb not running)
        try {
            broadcast(new \App\Events\AdminNotificationCreated($notification));
        } catch (\Throwable) {
            // Reverb not running — notifications still stored in DB
        }

        return $notification;
    }

    // ── Icon / Color helpers ───────────────────────────────────────────────────

    public static function iconFor(string $type): string
    {
        return match ($type) {
            'new_user'  => 'fas fa-user-plus',
            'low_stock' => 'fas fa-boxes-stacked',
            'new_order' => 'fas fa-shopping-cart',
            default     => 'fas fa-bell',
        };
    }

    public static function colorFor(string $type): string
    {
        return match ($type) {
            'new_user'  => 'text-primary',
            'low_stock' => 'text-warning',
            'new_order' => 'text-success',
            default     => 'text-secondary',
        };
    }

    public static function bgFor(string $type): string
    {
        return match ($type) {
            'new_user'  => '#4f8ef7',
            'low_stock' => '#f59e0b',
            'new_order' => '#22c55e',
            default     => '#64748b',
        };
    }
}
