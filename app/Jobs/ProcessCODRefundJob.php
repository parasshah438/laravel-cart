<?php

namespace App\Jobs;

use App\Models\OrderReturn;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;
use App\Services\NotificationService;

class ProcessCODRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderReturn;

    public function __construct(OrderReturn $orderReturn)
    {
        $this->orderReturn = $orderReturn;
    }

    /**
     * Execute the job - Process COD refund based on method
     */
    public function handle(): void
    {
        Log::info('Processing COD refund', [
            'return_id' => $this->orderReturn->id,
            'return_number' => $this->orderReturn->return_number,
            'refund_method' => $this->orderReturn->refund_method,
            'refund_amount' => $this->orderReturn->approved_refund_amount
        ]);

        try {
            switch ($this->orderReturn->refund_method) {
                case 'bank_transfer':
                    $this->processBankTransfer();
                    break;

                case 'upi_transfer':
                    $this->processUPITransfer();
                    break;

                case 'store_credit':
                    $this->processStoreCredit();
                    break;

                case 'cheque':
                    $this->processCheque();
                    break;

                default:
                    Log::error('Unknown refund method', [
                        'return_id' => $this->orderReturn->id,
                        'method' => $this->orderReturn->refund_method
                    ]);
                    $this->fail('Unknown refund method: ' . $this->orderReturn->refund_method);
                    return;
            }

            // Update return status
            $this->orderReturn->update([
                'status' => 'refund_completed',
                'refund_status' => 'completed',
                'processed_at' => now()
            ]);

            // Send success notification
            $this->sendRefundCompletedNotification();

            Log::info('COD refund processed successfully', [
                'return_id' => $this->orderReturn->id,
                'refund_amount' => $this->orderReturn->approved_refund_amount
            ]);

        } catch (\Exception $e) {
            Log::error('COD refund processing failed', [
                'return_id' => $this->orderReturn->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->orderReturn->update([
                'refund_status' => 'failed',
                'admin_notes' => 'Refund failed: ' . $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Process bank transfer refund
     */
    protected function processBankTransfer()
    {
        Log::info('Processing bank transfer refund', [
            'return_id' => $this->orderReturn->id,
            'amount' => $this->orderReturn->approved_refund_amount
        ]);

        $refundDetails = $this->orderReturn->refund_details;
        
        // Simulate bank transfer API call
        // In real implementation, integrate with payment gateway
        $transferResult = $this->simulateBankTransfer([
            'account_number' => $refundDetails['account_number'] ?? '',
            'ifsc_code' => $refundDetails['ifsc_code'] ?? '',
            'account_holder_name' => $refundDetails['account_holder_name'] ?? '',
            'amount' => $this->orderReturn->approved_refund_amount,
            'reference' => $this->orderReturn->return_number
        ]);

        if (!$transferResult['success']) {
            throw new \Exception('Bank transfer failed: ' . $transferResult['message']);
        }

        // Update refund details with transaction info
        $this->orderReturn->update([
            'refund_details' => array_merge($refundDetails, [
                'transaction_id' => $transferResult['transaction_id'],
                'transfer_date' => now(),
                'bank_reference' => $transferResult['bank_reference']
            ])
        ]);
    }

    /**
     * Process UPI transfer refund
     */
    protected function processUPITransfer()
    {
        Log::info('Processing UPI transfer refund', [
            'return_id' => $this->orderReturn->id,
            'amount' => $this->orderReturn->approved_refund_amount
        ]);

        $refundDetails = $this->orderReturn->refund_details;
        
        // Simulate UPI transfer
        $upiResult = $this->simulateUPITransfer([
            'upi_id' => $refundDetails['upi_id'] ?? '',
            'amount' => $this->orderReturn->approved_refund_amount,
            'reference' => $this->orderReturn->return_number
        ]);

        if (!$upiResult['success']) {
            throw new \Exception('UPI transfer failed: ' . $upiResult['message']);
        }

        $this->orderReturn->update([
            'refund_details' => array_merge($refundDetails, [
                'transaction_id' => $upiResult['transaction_id'],
                'upi_reference' => $upiResult['upi_reference'],
                'transfer_date' => now()
            ])
        ]);
    }

    /**
     * Process store credit refund
     */
    protected function processStoreCredit()
    {
        Log::info('Processing store credit refund', [
            'return_id' => $this->orderReturn->id,
            'amount' => $this->orderReturn->approved_refund_amount
        ]);

        $user = $this->orderReturn->order->user;
        
        // Add store credit to user wallet
        // Assuming you have a wallet system
        $user->increment('wallet_balance', $this->orderReturn->approved_refund_amount);

        $this->orderReturn->update([
            'refund_details' => [
                'wallet_credited' => true,
                'credit_date' => now(),
                'previous_balance' => $user->wallet_balance - $this->orderReturn->approved_refund_amount,
                'new_balance' => $user->wallet_balance
            ]
        ]);
    }

    /**
     * Process cheque refund
     */
    protected function processCheque()
    {
        Log::info('Processing cheque refund', [
            'return_id' => $this->orderReturn->id,
            'amount' => $this->orderReturn->approved_refund_amount
        ]);

        // Update status to indicate cheque is being processed
        $this->orderReturn->update([
            'refund_details' => array_merge($this->orderReturn->refund_details ?? [], [
                'cheque_number' => 'CHQ-' . $this->orderReturn->return_number,
                'cheque_date' => now(),
                'dispatch_date' => now()->addDays(2)
            ])
        ]);
    }

    /**
     * Simulate bank transfer (replace with actual payment gateway integration)
     */
    protected function simulateBankTransfer($data)
    {
        // Simulate success/failure
        $success = rand(1, 100) <= 95; // 95% success rate
        
        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                'bank_reference' => 'BNK' . time(),
                'message' => 'Transfer completed successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Bank transfer failed due to insufficient funds or invalid account details'
            ];
        }
    }

    /**
     * Simulate UPI transfer
     */
    protected function simulateUPITransfer($data)
    {
        $success = rand(1, 100) <= 98; // 98% success rate for UPI
        
        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'UPI' . time() . rand(1000, 9999),
                'upi_reference' => time() . rand(100000, 999999),
                'message' => 'UPI transfer completed'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'UPI transfer failed'
            ];
        }
    }

    /**
     * Send refund completed notification
     */
    protected function sendRefundCompletedNotification()
    {
        try {
            // Send email/SMS to customer
            if (class_exists(NotificationService::class)) {
                app(NotificationService::class)->sendRefundCompletedNotification($this->orderReturn);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send refund notification', [
                'return_id' => $this->orderReturn->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}