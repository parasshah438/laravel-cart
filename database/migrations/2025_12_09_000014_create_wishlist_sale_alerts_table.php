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
        Schema::create('wishlist_sale_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Alert Settings
            $table->decimal('desired_price', 10, 2)->nullable();
            $table->decimal('percentage_drop', 5, 2)->nullable();
            
            // Alert Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_notified_at')->nullable();
            $table->integer('notification_count')->default(0);
            
            // Sale Context
            $table->foreignId('current_sale_event_id')->nullable()->constrained('sale_events')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'product_id'], 'unique_wishlist_alert');
            $table->index(['is_active', 'user_id']);
            $table->index('last_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_sale_alerts');
    }
};