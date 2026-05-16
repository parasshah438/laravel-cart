<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'flag',
        'is_rtl',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_rtl'     => 'boolean',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get all active languages (cached IDs, fetched fresh to avoid serialization issues).
     */
    public static function active(): \Illuminate\Database\Eloquent\Collection
    {
        $ids = Cache::remember('languages.active', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('id')
                ->toArray();
        });

        if (empty($ids)) {
            return static::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        }

        return static::whereIn('id', $ids)->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Get the default language (cached by ID only).
     */
    public static function getDefault(): ?self
    {
        $id = Cache::remember('languages.default', 3600, function () {
            $lang = static::where('is_default', true)->where('is_active', true)->first()
                ?? static::where('is_active', true)->orderBy('sort_order')->first();
            return $lang?->id;
        });

        return $id ? static::find($id) : null;
    }

    /**
     * Clear the language caches.
     */
    public static function clearCache(): void
    {
        Cache::forget('languages.active');
        Cache::forget('languages.default');
    }

    /**
     * Set this language as the default (clears others).
     */
    public function makeDefault(): void
    {
        static::where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true, 'is_active' => true]);
        static::clearCache();
    }
}
