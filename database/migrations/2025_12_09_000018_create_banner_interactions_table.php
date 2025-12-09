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
        Schema::create('banner_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_banner_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // NULL for guest
            $table->string('session_id');
            
            // Interaction Details
            $table->enum('interaction_type', ['impression', 'click', 'close', 'hover']);
            
            // Context
            $table->string('page_url', 500)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet']);
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['sale_banner_id', 'interaction_type', 'created_at'], 'idx_banner_stats');
            $table->index(['session_id', 'interaction_type']);
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_interactions');
    }
};