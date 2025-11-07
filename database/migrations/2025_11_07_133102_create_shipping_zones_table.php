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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('base_rate', 8, 2)->default(0.00);
            $table->decimal('per_kg_rate', 8, 2)->default(0.00);
            $table->decimal('free_shipping_threshold', 10, 2)->default(0.00);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
