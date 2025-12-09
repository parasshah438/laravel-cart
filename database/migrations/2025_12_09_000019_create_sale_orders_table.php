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
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            
            // Sale Context
            $table->json('coupons_used')->nullable();
            $table->json('bundles_purchased')->nullable();
            $table->json('challenges_completed')->nullable();
            
            // Sale Metrics
            $table->decimal('original_amount', 15, 2);
            $table->decimal('sale_discount_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2);
            
            // Attribution
            $table->string('referral_source')->nullable();
            $table->string('campaign_source')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->unique('order_id', 'unique_sale_order');
            $table->index('sale_event_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};