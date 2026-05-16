<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Log an activity for the currently authenticated admin.
     *
     * @param  array  $properties  Optional structured data, e.g. ['diff' => ['field' => ['old' => ..., 'new' => ...]]]
     */
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array  $properties = []
    ): void {
        static::create([
            'admin_id'     => auth('admin')->id(),
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'description'  => $description,
            'properties'   => empty($properties) ? null : $properties,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr(request()->userAgent() ?? '', 0, 500),
        ]);
    }

    /**
     * Compute a field-level diff between two flat arrays.
     * Returns only the fields that actually changed.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function diff(array $old, array $new): array
    {
        $changed = [];
        $keys    = array_unique(array_merge(array_keys($old), array_keys($new)));
        foreach ($keys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;
            if ((string) $oldVal !== (string) $newVal) {
                $changed[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }
        return $changed;
    }
}
