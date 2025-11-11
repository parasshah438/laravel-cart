<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Jobs\ProcessCODRefundJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CODReturnService
{
    /**
     * Create a new return request
     */
    public function createReturnRequest(Order $order, array $data)
    {
        DB::beginTransaction();
        
        try {
            // Validate return eligibility
            $this->validateReturnEligibility($order);
            
            // Calculate refund amount
            $refundAmount = $this->calculateRefundAmount($order, $data['return_items']);
            
            // Create return record
            $orderReturn = OrderReturn::create([
                'order_id' => $order->id,
                'return_number' => OrderReturn::generateReturnNumber(),
                'return_type' => $data['return_type'] ?? 'return',
                'status' => 'requested',
                'return_reason' => $data['return_reason'],
                'return_comments' => $data['return_comments'] ?? null,
                'return_items' => $data['return_items'],
                'refund_amount' => $refundAmount,
                'refund_method' => $data['refund_method'] ?? 'bank_transfer',
                'refund_details' => $data['refund_details'] ?? [],
                'refund_status' => 'pending'
            ]);
            
            // Update order status
            $order->update(['status' => 'return_requested']);
            
            DB::commit();
            
            Log::info('Return request created', [
                'order_id' => $order->id,
                'return_id' => $orderReturn->id,
                'return_number' => $orderReturn->return_number,
                'refund_amount' => $refundAmount
            ]);
            
            return $orderReturn;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create return request', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Admin approves return request
     */
    public function approveReturn(OrderReturn $orderReturn, array $data = [])
    {
        if ($orderReturn->status !== 'requested') {
            throw new \Exception('Return cannot be approved in current status: ' . $orderReturn->status);
        }
        
        $orderReturn->update([
            'status' => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'admin_notes' => $data['admin_notes'] ?? null,
            'approved_refund_amount' => $data['approved_refund_amount'] ?? $orderReturn->refund_amount
        ]);
        
        // Schedule pickup
        $this->schedulePickup($orderReturn);
        
        Log::info('Return approved', [
            'return_id' => $orderReturn->id,
            'approved_by' => auth()->id(),
            'approved_amount' => $orderReturn->approved_refund_amount
        ]);
        
        return $orderReturn;
    }
    
    /**
     * Admin rejects return request
     */
    public function rejectReturn(OrderReturn $orderReturn, string $reason)
    {
        $orderReturn->update([
            'status' => 'rejected',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'admin_notes' => $reason,
            'refund_status' => 'failed'
        ]);
        
        // Revert order status
        $orderReturn->order->update(['status' => 'delivered']);
        
        Log::info('Return rejected', [
            'return_id' => $orderReturn->id,
            'rejected_by' => auth()->id(),
            'reason' => $reason
        ]);
    }
    
    /**
     * Schedule pickup with courier
     */
    public function schedulePickup(OrderReturn $orderReturn)
    {
        // In real implementation, integrate with courier API
        $pickupDate = now()->addDays(2); // Schedule pickup in 2 days
        
        $orderReturn->update([
            'status' => 'pickup_scheduled',
            'pickup_scheduled_date' => $pickupDate,
            'pickup_carrier_id' => 'local_courier', // Use same carrier as delivery
            'pickup_tracking_number' => 'RTN-' . time() . rand(1000, 9999)
        ]);
        
        Log::info('Pickup scheduled', [
            'return_id' => $orderReturn->id,
            'pickup_date' => $pickupDate,
            'tracking_number' => $orderReturn->pickup_tracking_number
        ]);
    }
    
    /**
     * Mark item as picked up
     */
    public function markPickedUp(OrderReturn $orderReturn)
    {
        $orderReturn->update([
            'status' => 'picked_up',
            'pickup_completed_date' => now()
        ]);
        
        // Automatically move to in_transit
        $this->markInTransit($orderReturn);
    }
    
    /**
     * Mark item as in transit to warehouse
     */
    public function markInTransit(OrderReturn $orderReturn)
    {
        $orderReturn->update(['status' => 'in_transit']);
    }
    
    /**
     * Mark item as received at warehouse
     */
    public function markReceived(OrderReturn $orderReturn)
    {
        $orderReturn->update([
            'status' => 'received'
        ]);
        
        // Automatically start quality check
        $this->startQualityCheck($orderReturn);
    }
    
    /**
     * Start quality check process
     */
    public function startQualityCheck(OrderReturn $orderReturn)
    {
        $orderReturn->update(['status' => 'quality_check']);
        
        Log::info('Quality check started', [
            'return_id' => $orderReturn->id
        ]);
    }
    
    /**
     * Complete quality check - PASS
     */
    public function passQualityCheck(OrderReturn $orderReturn, array $data = [])
    {
        $orderReturn->update([
            'status' => 'quality_passed',
            'quality_check_notes' => $data['notes'] ?? 'Item is in good condition',
            'quality_check_images' => $data['images'] ?? [],
            'approved_refund_amount' => $data['approved_amount'] ?? $orderReturn->refund_amount
        ]);
        
        // Initiate refund process
        $this->initiateRefund($orderReturn);
    }
    
    /**
     * Complete quality check - FAIL
     */
    public function failQualityCheck(OrderReturn $orderReturn, string $reason)
    {
        $orderReturn->update([
            'status' => 'quality_failed',
            'quality_check_notes' => $reason,
            'approved_refund_amount' => 0,
            'refund_status' => 'failed'
        ]);
        
        Log::info('Quality check failed', [
            'return_id' => $orderReturn->id,
            'reason' => $reason
        ]);
    }
    
    /**
     * Initiate refund process
     */
    public function initiateRefund(OrderReturn $orderReturn)
    {
        $orderReturn->update([
            'status' => 'refund_initiated',
            'refund_status' => 'initiated'
        ]);
        
        // Dispatch refund job based on payment method
        ProcessCODRefundJob::dispatch($orderReturn);
        
        Log::info('Refund initiated', [
            'return_id' => $orderReturn->id,
            'refund_method' => $orderReturn->refund_method,
            'refund_amount' => $orderReturn->approved_refund_amount
        ]);
    }
    
    /**
     * Validate if order is eligible for return
     */
    protected function validateReturnEligibility(Order $order)
    {
        // Check if order is delivered
        if ($order->status !== 'delivered') {
            throw new \Exception('Order must be delivered to request return');
        }
        
        // Check if within return window (30 days)
        $returnWindow = 30; // days
        if ($order->updated_at->diffInDays(now()) > $returnWindow) {
            throw new \Exception('Return window has expired. Returns are only allowed within ' . $returnWindow . ' days of delivery.');
        }
        
        // Check if order is COD
        if ($order->payment_method !== 'cod') {
            throw new \Exception('This service is only for COD orders');
        }
        
        // Check if return already exists
        if ($order->returns()->whereNotIn('status', ['rejected', 'closed'])->exists()) {
            throw new \Exception('A return request already exists for this order');
        }
    }
    
    /**
     * Calculate refund amount based on return items
     */
    protected function calculateRefundAmount(Order $order, array $returnItemIds)
    {
        $refundAmount = 0;
        
        foreach ($returnItemIds as $itemId) {
            $orderItem = $order->items()->find($itemId);
            if ($orderItem) {
                $refundAmount += ($orderItem->price * $orderItem->quantity);
            }
        }
        
        return $refundAmount;
    }
    
    /**
     * Get return statistics for admin dashboard
     */
    public function getReturnStatistics()
    {
        return [
            'total_returns' => OrderReturn::count(),
            'pending_approval' => OrderReturn::where('status', 'requested')->count(),
            'active_returns' => OrderReturn::active()->count(),
            'completed_returns' => OrderReturn::where('status', 'refund_completed')->count(),
            'total_refund_amount' => OrderReturn::where('refund_status', 'completed')->sum('approved_refund_amount'),
            'pending_refunds' => OrderReturn::whereIn('refund_status', ['pending', 'initiated', 'processing'])->sum('approved_refund_amount')
        ];
    }
}