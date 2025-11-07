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
        Schema::create('shipping_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('order_shipments')->onDelete('cascade');
            $table->string('status', 100);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('event_time');
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_exception')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['shipment_id', 'status']);
            $table->index('event_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_tracking_events');
    }
};
