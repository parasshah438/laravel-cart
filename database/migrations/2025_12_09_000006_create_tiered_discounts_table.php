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
        Schema::create('tiered_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tier Settings
            $table->enum('tier_type', ['quantity', 'amount']);
            
            // Conditions
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('brand_ids')->nullable();
            
            // Sale Integration
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            
            // Status & Timing
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'tier_type']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiered_discounts');
    }
};