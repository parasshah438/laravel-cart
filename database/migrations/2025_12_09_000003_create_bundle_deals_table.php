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
        Schema::create('bundle_deals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Bundle Settings
            $table->enum('bundle_type', ['fixed_combo', 'buy_x_get_y', 'mix_match', 'tiered_discount']);
            $table->integer('min_products')->default(2);
            $table->integer('max_products')->default(0);
            
            // Pricing
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('bundle_price', 10, 2)->nullable();
            
            // Conditions
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            $table->json('category_ids')->nullable();
            $table->json('brand_ids')->nullable();
            
            // Display
            $table->string('image', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // Timing
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('bundle_type');
            $table->index(['is_active', 'is_featured']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_deals');
    }
};