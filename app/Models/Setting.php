<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label', 'type', 'options'];

    protected $casts = ['options' => 'array'];

    /** @var Collection<string, Setting>|null */
    protected static ?Collection $cache = null;

    // ──────────────────────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (static::$cache === null) {
            static::$cache = static::all()->keyBy('key');
        }

        $setting = static::$cache->get($key);

        if ($setting === null) return $default;

        if ($setting->type === 'boolean') {
            return (bool) $setting->value;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value by key (updates existing row only).
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        static::$cache = null; // bust request-level cache
    }

    /**
     * Return all settings grouped by their group name, keyed by key.
     *
     * @return \Illuminate\Support\Collection<string, Collection>
     */
    public static function byGroup(): \Illuminate\Support\Collection
    {
        if (static::$cache === null) {
            static::$cache = static::orderBy('group')->orderBy('id')->get()->keyBy('key');
        }

        return static::$cache->values()->groupBy('group');
    }

    /**
     * Flush the request-level cache (call after bulk updates).
     */
    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
