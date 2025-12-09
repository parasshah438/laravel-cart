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
        Schema::create('sale_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification Type
            $table->enum('type', ['sale_started', 'sale_ending_soon', 'wishlist_on_sale', 'flash_deal_available', 'early_access_invite', 'price_drop_alert']);
            
            // Related Entities
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            // Notification Content
            $table->string('title');
            $table->text('message');
            $table->string('action_url', 500)->nullable();
            
            // Scheduling
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Channels
            $table->boolean('sent_via_email')->default(false);
            $table->boolean('sent_via_push')->default(false);
            $table->boolean('sent_via_sms')->default(false);
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['scheduled_for', 'sent_at']);
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_notifications');
    }
};