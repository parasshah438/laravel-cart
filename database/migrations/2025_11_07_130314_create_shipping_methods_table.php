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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('shipping_carriers')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('delivery_time', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('base_cost', 8, 2)->default(0.00);
            $table->decimal('per_km_cost', 8, 2)->default(0.00);
            $table->json('weight_based_pricing')->nullable();
            $table->json('zone_based_pricing')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->unique(['carrier_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
