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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_carrier_id')->nullable()->after('shipping_method')->constrained('shipping_carriers');
            $table->foreignId('shipping_method_id')->nullable()->after('shipping_carrier_id')->constrained('shipping_methods');
            $table->timestamp('estimated_delivery_date')->nullable()->after('delivery_date');
            $table->decimal('package_weight', 8, 2)->nullable()->after('shipping_cost');
            $table->json('package_dimensions')->nullable()->after('package_weight');
            $table->boolean('requires_signature')->default(false)->after('package_dimensions');
            $table->text('delivery_notes')->nullable()->after('delivery_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_carrier_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn([
                'shipping_carrier_id',
                'shipping_method_id', 
                'estimated_delivery_date',
                'package_weight',
                'package_dimensions',
                'requires_signature',
                'delivery_notes'
            ]);
        });
    }
};
