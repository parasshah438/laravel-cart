<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'order_item_id',
        'quantity',
        'weight',
        'dimensions',
        'declared_value',
        'sku',
        'product_name',
        'description'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'declared_value' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class, 'shipment_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function getTotalWeightAttribute()
    {
        return $this->weight * $this->quantity;
    }

    public function getTotalValueAttribute()
    {
        return $this->declared_value * $this->quantity;
    }

    public function getDimensionsStringAttribute()
    {
        if (!$this->dimensions) {
            return 'N/A';
        }

        return sprintf(
            '%s × %s × %s cm',
            $this->dimensions['length'] ?? '0',
            $this->dimensions['width'] ?? '0',
            $this->dimensions['height'] ?? '0'
        );
    }

    public function getVolumeAttribute()
    {
        if (!$this->dimensions || 
            !isset($this->dimensions['length']) || 
            !isset($this->dimensions['width']) || 
            !isset($this->dimensions['height'])) {
            return 0;
        }

        return $this->dimensions['length'] * $this->dimensions['width'] * $this->dimensions['height'];
    }

    public function getTotalVolumeAttribute()
    {
        return $this->getVolumeAttribute() * $this->quantity;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipmentItem) {
            // Auto-populate fields from order item if not provided
            if ($shipmentItem->order_item_id && !$shipmentItem->product_name) {
                $orderItem = OrderItem::find($shipmentItem->order_item_id);
                if ($orderItem) {
                    $shipmentItem->product_name = $orderItem->product_name;
                    $shipmentItem->sku = $orderItem->product_sku;
                    $shipmentItem->declared_value = $orderItem->price;
                    
                    // Set weight from product if available
                    if (!$shipmentItem->weight && $orderItem->product) {
                        $shipmentItem->weight = $orderItem->product->weight ?? 0.5; // Default weight
                    }
                    
                    // Set dimensions from product if available
                    if (!$shipmentItem->dimensions && $orderItem->product) {
                        $product = $orderItem->product;
                        if ($product->length && $product->width && $product->height) {
                            $shipmentItem->dimensions = [
                                'length' => $product->length,
                                'width' => $product->width,
                                'height' => $product->height
                            ];
                        }
                    }
                }
            }
        });
    }
}
