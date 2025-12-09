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
        Schema::create('spin_wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_wheel_campaign_id')->constrained()->onDelete('cascade');
            
            // Prize Details
            $table->string('prize_name');
            $table->enum('prize_type', ['discount_coupon', 'cashback', 'free_product', 'free_shipping', 'points', 'nothing']);
            $table->decimal('prize_value', 10, 2)->default(0);
            
            // Prize Settings
            $table->decimal('probability_percentage', 5, 2);
            $table->integer('max_winners')->default(0);
            $table->integer('current_winners')->default(0);
            
            // Prize Configuration
            $table->json('coupon_config')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            // Display
            $table->string('display_text')->nullable();
            $table->string('icon', 500)->nullable();
            $table->string('color', 7)->default('#ffd700');
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('spin_wheel_campaign_id');
            $table->index('prize_type');
            $table->index('probability_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_prizes');
    }
};