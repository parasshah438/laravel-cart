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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Order relationship
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Payment identification
            $table->string('payment_id')->unique(); // Our internal payment ID
            $table->string('gateway')->default('razorpay'); // razorpay, stripe, etc.
            $table->string('gateway_payment_id')->nullable(); // Gateway's payment ID
            $table->string('gateway_order_id')->nullable(); // Gateway's order ID
            $table->string('transaction_id')->nullable(); // Bank transaction ID
            
            // Payment details
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2); // Payment amount
            $table->string('currency', 3)->default('INR'); // Currency code
            $table->string('method')->nullable(); // card, upi, netbanking, wallet, etc.
            $table->string('payment_method')->nullable(); // More specific method details
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            
            // Gateway response and metadata
            $table->json('gateway_response')->nullable(); // Full gateway response
            $table->json('metadata')->nullable(); // Additional payment metadata
            $table->text('failure_reason')->nullable(); // Reason for failure
            $table->string('receipt_number')->nullable(); // Receipt/invoice number
            
            // Timestamps for payment events
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Additional tracking
            $table->string('ip_address')->nullable(); // User's IP address
            $table->string('user_agent')->nullable(); // User's browser info
            $table->json('billing_details')->nullable(); // Billing information
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('gateway');
            $table->index('gateway_payment_id');
            $table->index(['status', 'payment_status']);
            $table->index(['user_id', 'created_at']);
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
