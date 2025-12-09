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
        Schema::create('user_spin_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('spin_wheel_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('spin_wheel_prize_id')->constrained()->onDelete('cascade');
            
            // Prize Won
            $table->string('prize_won');
            $table->decimal('prize_value', 10, 2)->default(0);
            
            // Coupon Generated (if applicable)
            $table->string('generated_coupon_code', 50)->nullable();
            $table->boolean('coupon_claimed')->default(false);
            $table->timestamp('coupon_claimed_at')->nullable();
            
            // Order Context (if spin after purchase)
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['user_id', 'spin_wheel_campaign_id']);
            $table->index('created_at');
            $table->index('generated_coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_spin_history');
    }
};