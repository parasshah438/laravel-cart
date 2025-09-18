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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // ✅ CORE REVIEW DATA
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null'); // Order from which this review was made
            
            // ✅ REVIEW CONTENT
            $table->tinyInteger('rating')->unsigned(); // 1-5 stars
            $table->string('title', 200)->nullable(); // Review headline
            $table->text('comment')->nullable(); // Review text
            
            // ✅ REVIEW INTERACTIONS
            $table->integer('helpful_count')->default(0); // How many found this helpful
            $table->integer('not_helpful_count')->default(0); // How many found this not helpful
            $table->boolean('verified_purchase')->default(false); // Verified buyer
            
            // ✅ REVIEW MEDIA
            $table->json('photos')->nullable(); // Array of photo URLs
            $table->json('videos')->nullable(); // Array of video URLs (future)
            
            // ✅ REVIEW STATUS & MODERATION
            $table->enum('status', ['pending', 'approved', 'rejected', 'hidden'])->default('pending');
            $table->text('admin_notes')->nullable(); // Admin moderation notes
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // ✅ PRODUCT ATTRIBUTES (What was reviewed)
            $table->string('product_variant')->nullable(); // Size, color, etc.
            $table->boolean('would_recommend')->nullable(); // Would recommend to friend
            
            // ✅ METADATA
            $table->string('ip_address', 45)->nullable(); // For spam detection
            $table->string('user_agent')->nullable(); // Browser info
            $table->integer('report_count')->default(0); // How many times reported
            $table->timestamp('last_updated_by_user')->nullable(); // Last edit time
            
            $table->timestamps();
            
            // ✅ INDEXES FOR PERFORMANCE
            $table->index(['product_id', 'status', 'created_at']); // Product reviews listing
            $table->index(['user_id', 'created_at']); // User's reviews
            $table->index(['rating', 'status']); // Filter by rating
            $table->index(['verified_purchase', 'status']); // Verified reviews
            $table->index('helpful_count'); // Sort by helpfulness
            
            // ✅ UNIQUE CONSTRAINT (One review per user per product)
            $table->unique(['user_id', 'product_id'], 'unique_user_product_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
