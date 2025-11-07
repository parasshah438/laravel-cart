<?php

namespace App\Jobs;

use App\Models\OrderShipment;
use App\Services\TrackingService;
use App\Jobs\SendShippingNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateShippingStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shipment;
    protected $newStatus;
    protected $description;
    protected $location;
    protected $notifyCustomer;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        OrderShipment $shipment, 
        string $newStatus, 
        string $description = null, 
        string $location = null,
        bool $notifyCustomer = true
    ) {
        $this->shipment = $shipment;
        $this->newStatus = $newStatus;
        $this->description = $description;
        $this->location = $location;
        $this->notifyCustomer = $notifyCustomer;
    }

    /**
     * Execute the job.
     */
    public function handle(TrackingService $trackingService)
    {
        try {
            Log::info('Updating shipping status', [
                'shipment_id' => $this->shipment->id,
                'old_status' => $this->shipment->status,
                'new_status' => $this->newStatus,
                'tracking_number' => $this->shipment->tracking_number
            ]);

            $oldStatus = $this->shipment->status;

            // Update the shipment status
            $this->shipment->updateStatus(
                $this->newStatus, 
                $this->description, 
                $this->location
            );

            // Update additional fields based on status
            $this->updateAdditionalFields();

            // Update order status if needed
            $this->updateOrderStatus();

            // Send notifications if required
            if ($this->notifyCustomer && $this->shouldNotifyCustomer($oldStatus, $this->newStatus)) {
                SendShippingNotifications::dispatch($this->shipment, 'status_update', [
                    'old_status' => $oldStatus,
                    'new_status' => $this->newStatus,
                    'description' => $this->description,
                    'location' => $this->location
                ]);
            }

            // Schedule next tracking update if needed
            $this->scheduleNextUpdate();

            Log::info('Shipping status updated successfully', [
                'shipment_id' => $this->shipment->id,
                'new_status' => $this->newStatus,
                'notifications_sent' => $this->notifyCustomer
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update shipping status', [
                'shipment_id' => $this->shipment->id,
                'new_status' => $this->newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Update additional fields based on status
     */
    protected function updateAdditionalFields()
    {
        $updates = [];

        switch ($this->newStatus) {
            case 'picked_up':
                if (!$this->shipment->shipped_at) {
                    $updates['shipped_at'] = now();
                }
                break;

            case 'delivered':
                if (!$this->shipment->delivered_at) {
                    $updates['delivered_at'] = now();
                }
                break;

            case 'out_for_delivery':
                // Set estimated delivery to today if not set
                if (!$this->shipment->estimated_delivery) {
                    $updates['estimated_delivery'] = now()->endOfDay();
                }
                break;

            case 'exception':
                // Add exception handling metadata
                $updates['metadata'] = array_merge($this->shipment->metadata ?? [], [
                    'exception_occurred_at' => now(),
                    'exception_description' => $this->description,
                    'exception_location' => $this->location,
                    'requires_attention' => true
                ]);
                break;
        }

        if (!empty($updates)) {
            $this->shipment->update($updates);
        }
    }

    /**
     * Update order status based on shipment status
     */
    protected function updateOrderStatus()
    {
        $order = $this->shipment->order;
        $allShipments = $order->shipments;

        // Determine new order status based on all shipments
        if ($allShipments->every(fn($s) => $s->status === 'delivered')) {
            $order->update(['status' => 'delivered']);
        } elseif ($allShipments->some(fn($s) => in_array($s->status, ['picked_up', 'in_transit', 'out_for_delivery']))) {
            $order->update(['status' => 'shipped']);
        } elseif ($allShipments->every(fn($s) => $s->status === 'exception')) {
            $order->update(['status' => 'on_hold']);
        } elseif ($allShipments->some(fn($s) => $s->status === 'returned')) {
            $order->update(['status' => 'returned']);
        }
    }

    /**
     * Determine if customer should be notified
     */
    protected function shouldNotifyCustomer(string $oldStatus, string $newStatus): bool
    {
        // Notify for important status changes
        $importantStatuses = [
            'picked_up',
            'out_for_delivery', 
            'delivered',
            'exception',
            'returned'
        ];

        return in_array($newStatus, $importantStatuses) && $oldStatus !== $newStatus;
    }

    /**
     * Schedule next tracking update if needed
     */
    protected function scheduleNextUpdate()
    {
        // Continue tracking for active shipments
        if (in_array($this->newStatus, ['picked_up', 'in_transit', 'out_for_delivery'])) {
            ProcessShipmentTracking::dispatch($this->shipment)
                ->delay(now()->addHours(2));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('UpdateShippingStatusJob failed permanently', [
            'shipment_id' => $this->shipment->id,
            'new_status' => $this->newStatus,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update shipment metadata to indicate status update failure
        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'status_update_failed' => true,
                'status_update_error' => $exception->getMessage(),
                'failed_status' => $this->newStatus,
                'failed_at' => now()
            ])
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags()
    {
        return [
            'shipment:' . $this->shipment->id,
            'status_update',
            'status:' . $this->newStatus
        ];
    }
}