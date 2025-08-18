<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'button_text',
        'button_link',
        'bg_color',
        'bg_color_secondary',
        'is_active',
        'sort_order'
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
