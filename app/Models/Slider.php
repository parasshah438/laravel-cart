<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'image_path',
        'link',
        'button_text',
        'description',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the active sliders ordered by sort order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function activeSliders()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }    
}
