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
        Schema::create('tier_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiered_discount_id')->constrained()->onDelete('cascade');
            
            // Tier Conditions
            $table->integer('min_quantity')->default(0);
            $table->decimal('min_amount', 10, 2)->default(0);
            
            // Tier Benefits
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 10, 2);
            
            // Display
            $table->string('tier_name', 100)->nullable();
            $table->integer('tier_order')->default(0);
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['tiered_discount_id', 'tier_order']);
            $table->index('min_quantity');
            $table->index('min_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_rules');
    }
};