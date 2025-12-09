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
        Schema::create('user_sale_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // NULL for guest users
            $table->string('session_id'); // Track guest behavior
            
            // Sale Context
            $table->foreignId('sale_event_id')->constrained()->onDelete('cascade');
            
            // Behavior Tracking
            $table->enum('action_type', ['view_sale', 'view_product', 'add_to_cart', 'remove_from_cart', 'add_to_wishlist', 'apply_coupon', 'checkout_start', 'checkout_complete', 'share_sale']);
            
            // Related Entities
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('coupon_code', 50)->nullable();
            
            // Context Data
            $table->enum('device_type', ['desktop', 'mobile', 'tablet']);
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('referrer_url', 500)->nullable();
            
            // Metadata
            $table->json('action_metadata')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['user_id', 'sale_event_id', 'created_at'], 'idx_user_behavior');
            $table->index(['session_id', 'sale_event_id', 'created_at'], 'idx_session_behavior');
            $table->index('action_type');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sale_behaviors');
    }
};