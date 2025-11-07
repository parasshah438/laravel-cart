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
        Schema::create('shipping_zone_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('cascade');
            $table->string('postal_code', 20)->nullable();
            $table->string('postal_code_range_start', 20)->nullable();
            $table->string('postal_code_range_end', 20)->nullable();
            $table->decimal('additional_rate', 8, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['zone_id', 'country_id']);
            $table->index(['zone_id', 'state_id']);
            $table->index(['zone_id', 'city_id']);
            $table->index(['postal_code']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_locations');
    }
};
