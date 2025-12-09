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
        Schema::create('bundle_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_deal_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Product Role in Bundle
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_optional')->default(false);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(1);
            
            // Pricing in Bundle
            $table->decimal('bundle_product_price', 10, 2)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->unique(['bundle_deal_id', 'product_id'], 'unique_bundle_product');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_products');
    }
};