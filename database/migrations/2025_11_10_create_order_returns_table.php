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
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('return_number')->unique(); // RTN-20251110-001
            $table->enum('return_type', ['return', 'exchange', 'cancel'])->default('return');
            $table->enum('status', [
                'requested',           // Customer requested return
                'approved',           // Admin approved return
                'rejected',           // Admin rejected return  
                'pickup_scheduled',   // Pickup scheduled with courier
                'picked_up',         // Item picked up from customer
                'in_transit',        // Item in transit to warehouse
                'received',          // Item received at warehouse
                'quality_check',     // Quality check in progress
                'quality_passed',    // Quality check passed
                'quality_failed',    // Quality check failed
                'refund_initiated',  // Refund process started
                'refund_completed',  // Refund completed
                'closed'             // Return process closed
            ])->default('requested');
            
            $table->string('return_reason');
            $table->text('return_comments')->nullable();
            $table->json('return_items'); // Array of order_item_ids
            
            // Refund Information
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->enum('refund_method', [
                'bank_transfer',
                'upi_transfer', 
                'store_credit',
                'cheque',
                'cash'
            ])->nullable();
            $table->json('refund_details')->nullable(); // Bank details, UPI ID, etc.
            $table->enum('refund_status', [
                'pending',
                'initiated', 
                'processing',
                'completed',
                'failed'
            ])->default('pending');
            
            // Pickup Information
            $table->string('pickup_carrier_id')->nullable();
            $table->string('pickup_tracking_number')->nullable();
            $table->timestamp('pickup_scheduled_date')->nullable();
            $table->timestamp('pickup_completed_date')->nullable();
            
            // Quality Check
            $table->text('quality_check_notes')->nullable();
            $table->json('quality_check_images')->nullable();
            $table->decimal('approved_refund_amount', 12, 2)->nullable();
            
            // Admin Information
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index(['return_number']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};