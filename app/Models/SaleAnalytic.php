<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_event_id',
        'analytics_date',
        'hour_of_day',
        'page_views',
        'unique_visitors',
        'products_viewed',
        'add_to_cart_count',
        'checkout_initiated',
        'orders_completed',
        'gross_revenue',
        'net_revenue',
        'total_discount_given',
        'avg_order_value',
        'view_to_cart_rate',
        'cart_to_order_rate',
        'overall_conversion_rate',
        'top_selling_product_id',
        'top_product_revenue',
        'organic_traffic',
        'paid_traffic',
        'social_traffic',
        'email_traffic',
        'direct_traffic',
    ];

    protected $casts = [
        'analytics_date' => 'date',
        'gross_revenue' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'total_discount_given' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'view_to_cart_rate' => 'decimal:2',
        'cart_to_order_rate' => 'decimal:2',
        'overall_conversion_rate' => 'decimal:2',
        'top_product_revenue' => 'decimal:2',
    ];

    /**
     * Get the sale event
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the top selling product
     */
    public function topSellingProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'top_selling_product_id');
    }

    /**
     * Get total traffic
     */
    public function getTotalTraffic(): int
    {
        return $this->organic_traffic + 
               $this->paid_traffic + 
               $this->social_traffic + 
               $this->email_traffic + 
               $this->direct_traffic;
    }

    /**
     * Get traffic breakdown percentages
     */
    public function getTrafficBreakdown(): array
    {
        $total = $this->getTotalTraffic();
        
        if ($total === 0) {
            return [
                'organic' => 0,
                'paid' => 0,
                'social' => 0,
                'email' => 0,
                'direct' => 0,
            ];
        }

        return [
            'organic' => ($this->organic_traffic / $total) * 100,
            'paid' => ($this->paid_traffic / $total) * 100,
            'social' => ($this->social_traffic / $total) * 100,
            'email' => ($this->email_traffic / $total) * 100,
            'direct' => ($this->direct_traffic / $total) * 100,
        ];
    }

    /**
     * Calculate ROI (Return on Investment)
     */
    public function getROI(): float
    {
        if ($this->total_discount_given <= 0) {
            return 0;
        }

        return (($this->net_revenue - $this->total_discount_given) / $this->total_discount_given) * 100;
    }

    /**
     * Get conversion funnel data
     */
    public function getConversionFunnel(): array
    {
        return [
            'visitors' => $this->unique_visitors,
            'product_views' => $this->products_viewed,
            'cart_additions' => $this->add_to_cart_count,
            'checkout_starts' => $this->checkout_initiated,
            'orders' => $this->orders_completed,
        ];
    }

    /**
     * Update analytics data
     */
    public function updateMetrics(array $metrics): bool
    {
        foreach ($metrics as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }

        // Recalculate rates
        $this->calculateRates();

        return $this->save();
    }

    /**
     * Calculate conversion rates
     */
    protected function calculateRates(): void
    {
        // View to cart rate
        if ($this->products_viewed > 0) {
            $this->view_to_cart_rate = ($this->add_to_cart_count / $this->products_viewed) * 100;
        }

        // Cart to order rate
        if ($this->add_to_cart_count > 0) {
            $this->cart_to_order_rate = ($this->orders_completed / $this->add_to_cart_count) * 100;
        }

        // Overall conversion rate
        if ($this->unique_visitors > 0) {
            $this->overall_conversion_rate = ($this->orders_completed / $this->unique_visitors) * 100;
        }

        // Average order value
        if ($this->orders_completed > 0) {
            $this->avg_order_value = $this->gross_revenue / $this->orders_completed;
        }
    }
}