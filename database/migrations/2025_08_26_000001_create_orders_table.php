<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained('user_addresses')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('status')->default('pending'); // pending, paid, shipped, delivered, cancelled
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->date('delivery_date')->nullable();
            $table->enum('shipping_method', ['morning', 'standard', 'express', 'midnight'])->default('standard');
            $table->string('time_slot')->nullable(); // e.g., '09:00-12:00', '12:00-17:00', '17:00-21:00', '21:00-23:59'
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->text('delivery_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
