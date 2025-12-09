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
        Schema::create('sale_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Banner Type & Position
            $table->enum('type', ['hero_banner', 'category_banner', 'product_strip', 'popup_modal', 'notification_bar', 'sticky_footer']);
            $table->enum('position', ['homepage_top', 'homepage_middle', 'category_top', 'product_sidebar', 'cart_page', 'checkout_page']);
            
            // Display Content
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('cta_text', 100)->nullable();
            $table->string('cta_url', 500)->nullable();
            
            // Images & Media
            $table->string('desktop_image', 500)->nullable();
            $table->string('mobile_image', 500)->nullable();
            $table->string('background_color', 7)->default('#ffffff');
            $table->string('text_color', 7)->default('#000000');
            
            // Targeting
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            $table->json('product_categories')->nullable();
            $table->json('user_segments')->nullable();
            
            // Display Rules
            $table->integer('display_priority')->default(0);
            $table->integer('max_impressions_per_user')->default(0);
            
            // A/B Testing
            $table->string('variant_name', 100)->nullable();
            $table->bigInteger('ab_test_group_id')->nullable();
            
            // Timing
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['type', 'position']);
            $table->index(['is_active', 'display_priority']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_banners');
    }
};