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
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('shipment_number', 100)->unique();
            $table->foreignId('carrier_id')->nullable()->constrained('shipping_carriers');
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods');
            $table->string('tracking_number')->nullable();
            $table->string('shiprocket_order_id')->nullable(); // ShipRocket specific
            $table->string('shiprocket_shipment_id')->nullable(); // ShipRocket specific
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'exception', 'returned'])->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('shipped_from_address')->nullable();
            $table->json('shipped_to_address')->nullable();
            $table->decimal('package_weight', 8, 2)->nullable();
            $table->json('package_dimensions')->nullable(); // {length, width, height}
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('insurance_cost', 8, 2)->default(0.00);
            $table->decimal('cod_amount', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // API responses, etc.
            $table->timestamps();
            
            $table->index('tracking_number');
            $table->index('status');
            $table->index('shipped_at');
            $table->index('shiprocket_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
