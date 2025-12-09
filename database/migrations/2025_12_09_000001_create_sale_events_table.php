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
        Schema::create('sale_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['flash_sale', 'mega_sale', 'deal_of_day', 'festival_sale', 'seasonal_sale', 'brand_day', 'category_sale']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'ended', 'cancelled'])->default('draft');
            
            // Timing
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('early_access_starts_at')->nullable();
            
            // Display & UI
            $table->string('banner_image', 500)->nullable();
            $table->string('mobile_banner_image', 500)->nullable();
            $table->string('theme_color', 7)->default('#ff6b6b');
            $table->string('landing_page_template', 100)->default('default');
            
            // Settings
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('requires_membership')->default(false);
            $table->decimal('max_discount_percentage', 5, 2)->default(0);
            
            // SEO & Marketing
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Analytics
            $table->integer('total_participants')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'starts_at', 'ends_at']);
            $table->index('type');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_events');
    }
};