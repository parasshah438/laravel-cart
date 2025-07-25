<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    protected $fillable = ['product_id', 'media_type', 'file_path', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getMediaTypeAttribute($value)
    {
        return ucfirst($value);
    }

    public function setFilePathAttribute($value)
    {
        $this->attributes['file_path'] = trim($value);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('media_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function getMediaUrlAttribute()
    {
        return asset($this->file_path);
    }

    public function getSortOrderAttribute($value)
    {
        return (int) $value;
    }
}
