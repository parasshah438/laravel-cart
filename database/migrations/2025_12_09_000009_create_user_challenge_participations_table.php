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
        Schema::create('user_challenge_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_challenge_id')->constrained()->onDelete('cascade');
            
            // Progress Tracking
            $table->decimal('current_progress', 10, 2)->default(0);
            $table->decimal('target_progress', 10, 2);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            
            // Status
            $table->enum('status', ['active', 'completed', 'failed', 'rewarded'])->default('active');
            
            // Completion & Reward
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reward_claimed_at')->nullable();
            $table->string('reward_coupon_code', 50)->nullable();
            
            // Tracking
            $table->integer('orders_count')->default(0);
            $table->decimal('amount_spent', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'sale_challenge_id'], 'unique_user_challenge');
            $table->index(['user_id', 'status']);
            $table->index('progress_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenge_participations');
    }
};