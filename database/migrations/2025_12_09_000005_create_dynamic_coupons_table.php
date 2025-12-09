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
        Schema::create('dynamic_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Coupon Type & Value
            $table->enum('type', ['percentage', 'fixed_cart', 'fixed_product', 'free_shipping', 'buy_x_get_y', 'cashback']);
            $table->decimal('value', 10, 2);
            
            // Usage Limits
            $table->integer('usage_limit')->default(0);
            $table->integer('used_count')->default(0);
            $table->integer('per_user_limit')->default(1);
            
            // Conditions
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            
            // Product/Category Restrictions
            $table->json('applicable_products')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_brands')->nullable();
            
            // User Restrictions
            $table->json('user_groups')->nullable();
            $table->boolean('first_order_only')->default(false);
            
            // Payment Method Offers
            $table->json('payment_methods')->nullable();
            
            // Timing
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            
            // Sale Integration
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_auto_apply')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('type');
            $table->index('is_auto_apply');
            $table->index('sale_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_coupons');
    }
};