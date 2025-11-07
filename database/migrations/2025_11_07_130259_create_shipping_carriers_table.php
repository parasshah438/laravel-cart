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
        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('api_endpoint', 500)->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('tracking_url_template', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_cod')->default(false);
            $table->boolean('supports_express')->default(false);
            $table->decimal('base_rate', 8, 2)->default(0);
            $table->decimal('per_kg_rate', 8, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_carriers');
    }
};
