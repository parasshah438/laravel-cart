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
        Schema::create('spin_wheel_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Campaign Settings
            $table->integer('max_spins_per_user')->default(1);
            $table->integer('total_spins_allowed')->default(0);
            $table->integer('current_total_spins')->default(0);
            
            // Eligibility Conditions
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->boolean('requires_purchase')->default(false);
            $table->boolean('first_time_users_only')->default(false);
            
            // Sale Integration
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            
            // Display
            $table->json('wheel_config')->nullable();
            $table->string('background_image', 500)->nullable();
            
            // Timing
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('sale_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_campaigns');
    }
};