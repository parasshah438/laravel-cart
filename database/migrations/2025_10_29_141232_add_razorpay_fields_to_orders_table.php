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
            // Add Razorpay payment fields
            $table->string('razorpay_order_id', 50)->nullable()->after('payment_status');
            $table->string('razorpay_payment_id', 50)->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature', 255)->nullable()->after('razorpay_payment_id');
            
            // Update notes column to be JSON for storing payment data
            $table->json('notes')->nullable()->change();
            
            // Add indexes for better query performance with proper length
            $table->index(['payment_method', 'payment_status'], 'orders_payment_idx');
            $table->index('razorpay_order_id', 'orders_rzp_order_idx');
            $table->index('razorpay_payment_id', 'orders_rzp_payment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop Razorpay fields
            $table->dropColumn(['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
            
            // Drop indexes
            $table->dropIndex('orders_payment_idx');
            $table->dropIndex('orders_rzp_order_idx');
            $table->dropIndex('orders_rzp_payment_idx');
            
            // Revert notes column back to text
            $table->text('notes')->nullable()->change();
        });
    }
};
