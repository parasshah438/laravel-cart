<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AdminLoginHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'ip_address',
        'user_agent',
        'status',
        'failed_email',
        'logged_out_at',
        'created_at',
    ];

    protected $casts = [
        'logged_out_at' => 'datetime',
        'created_at'    => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Record a login attempt.
     */
    public static function record(
        ?int $adminId,
        string $status,
        Request $request,
        ?string $failedEmail = null
    ): self {
        return static::create([
            'admin_id'     => $adminId,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'status'       => $status,
            'failed_email' => $failedEmail,
            'created_at'   => now(),
        ]);
    }

    /**
     * Parse browser name from user-agent string.
     */
    public static function parseBrowser(?string $ua): string
    {
        if (! $ua) return 'Unknown';

        $browsers = [
            'Edge'    => '/Edg\//i',
            'Chrome'  => '/Chrome\//i',
            'Firefox' => '/Firefox\//i',
            'Opera'   => '/OPR\//i',
            'Safari'  => '/Safari\//i',
            'IE'      => '/MSIE|Trident/i',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $ua)) return $name;
        }

        return 'Unknown';
    }

    /**
     * Parse OS name from user-agent string.
     */
    public static function parseOS(?string $ua): string
    {
        if (! $ua) return 'Unknown';

        $os = [
            'Android' => '/Android/i',
            'iOS'     => '/iPhone|iPad/i',
            'Windows' => '/Windows/i',
            'macOS'   => '/Macintosh|Mac OS/i',
            'Linux'   => '/Linux/i',
        ];

        foreach ($os as $name => $pattern) {
            if (preg_match($pattern, $ua)) return $name;
        }

        return 'Unknown';
    }

    /**
     * Human-readable browser + OS label.
     */
    public function getBrowserLabelAttribute(): string
    {
        return self::parseBrowser($this->user_agent)
            . ' on '
            . self::parseOS($this->user_agent);
    }

    /**
     * Session duration (logged_out_at - created_at).
     */
    public function getDurationAttribute(): ?string
    {
        if (! $this->logged_out_at) return null;

        $secs = $this->created_at->diffInSeconds($this->logged_out_at);

        if ($secs < 60)    return $secs . 's';
        if ($secs < 3600)  return floor($secs / 60) . 'm ' . ($secs % 60) . 's';

        $h = floor($secs / 3600);
        $m = floor(($secs % 3600) / 60);
        return $h . 'h ' . $m . 'm';
    }
}
