<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_event_id')->constrained()->onDelete('cascade');
            
            // Date Tracking
            $table->date('analytics_date');
            $table->tinyInteger('hour_of_day')->nullable(); // 0-23 for hourly analytics
            
            // Performance Metrics
            $table->integer('page_views')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('products_viewed')->default(0);
            $table->integer('add_to_cart_count')->default(0);
            $table->integer('checkout_initiated')->default(0);
            $table->integer('orders_completed')->default(0);
            
            // Revenue Metrics
            $table->decimal('gross_revenue', 15, 2)->default(0);
            $table->decimal('net_revenue', 15, 2)->default(0);
            $table->decimal('total_discount_given', 15, 2)->default(0);
            $table->decimal('avg_order_value', 10, 2)->default(0);
            
            // Conversion Metrics
            $table->decimal('view_to_cart_rate', 5, 2)->default(0);
            $table->decimal('cart_to_order_rate', 5, 2)->default(0);
            $table->decimal('overall_conversion_rate', 5, 2)->default(0);
            
            // Product Performance
            $table->foreignId('top_selling_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->decimal('top_product_revenue', 15, 2)->default(0);
            
            // Traffic Sources
            $table->integer('organic_traffic')->default(0);
            $table->integer('paid_traffic')->default(0);
            $table->integer('social_traffic')->default(0);
            $table->integer('email_traffic')->default(0);
            $table->integer('direct_traffic')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['sale_event_id', 'analytics_date', 'hour_of_day'], 'unique_daily_analytics');
            $table->index(['sale_event_id', 'analytics_date']);
            $table->index('analytics_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_analytics');
    }
};