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
        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Discount Settings
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle']);
            $table->decimal('discount_value', 10, 2);
            
            // Special Pricing
            $table->decimal('sale_price', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            
            // Quantity & Inventory
            $table->integer('sale_quantity_limit')->nullable();
            $table->integer('sold_quantity')->default(0);
            $table->integer('per_user_limit')->default(0);
            
            // Flash Sale Specific
            $table->integer('flash_sale_duration_minutes')->default(0);
            
            // Priority & Display
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured_in_sale')->default(false);
            
            // Timing (can override sale event timing)
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['sale_event_id', 'product_id'], 'unique_sale_product');
            $table->index(['sale_event_id', 'starts_at', 'ends_at']);
            $table->index('is_featured_in_sale');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_products');
    }
};