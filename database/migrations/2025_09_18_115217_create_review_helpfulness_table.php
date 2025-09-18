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
        Schema::create('review_helpfulness', function (Blueprint $table) {
            $table->id();
            
            // ✅ CORE RELATIONSHIPS
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // ✅ VOTE TYPE
            $table->boolean('is_helpful'); // true = helpful, false = not helpful
            
            // ✅ METADATA
            $table->string('ip_address', 45)->nullable(); // For spam detection
            $table->timestamps();
            
            // ✅ PREVENT DUPLICATE VOTES
            $table->unique(['review_id', 'user_id'], 'unique_review_user_vote');
            
            // ✅ INDEXES
            $table->index(['review_id', 'is_helpful']); // Count helpful/not helpful
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_helpfulness');
    }
};
