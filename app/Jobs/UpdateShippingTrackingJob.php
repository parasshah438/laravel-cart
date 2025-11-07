<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\OrderShipment;
use App\Services\ShipRocketService;

class UpdateShippingTrackingJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job - Update tracking for all active shipments
     */
    public function handle(ShipRocketService $shipRocketService): void
    {
        try {
            Log::info('Starting tracking update job');

            // Get shipments that need tracking updates
            $shipments = OrderShipment::with(['carrier', 'order'])
                ->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery'])
                ->where('tracking_number', '!=', null)
                ->where('updated_at', '<', now()->subMinutes(30)) // Only update if last updated more than 30 minutes ago
                ->limit(50) // Process max 50 shipments per job run
                ->get();

            Log::info('Found shipments to update', ['count' => $shipments->count()]);

            $updated = 0;
            $errors = 0;

            foreach ($shipments as $shipment) {
                try {
                    if ($shipment->carrier->code === 'shiprocket' && $shipment->tracking_number) {
                        $this->updateShipmentTracking($shipment, $shipRocketService);
                        $updated++;
                        
                        // Small delay to avoid API rate limiting
                        usleep(100000); // 0.1 second delay
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::warning('Failed to update tracking for shipment', [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Tracking update job completed', [
                'updated' => $updated,
                'errors' => $errors,
                'total_processed' => $shipments->count()
            ]);

        } catch (\Exception $e) {
            Log::error('UpdateShippingTrackingJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update tracking for a specific shipment
     */
    private function updateShipmentTracking(OrderShipment $shipment, ShipRocketService $shipRocketService)
    {
        $trackingData = $shipRocketService->trackShipment($shipment->tracking_number);
        
        if (!$trackingData) {
            return;
        }

        // Extract tracking information
        $currentStatus = $trackingData['tracking_data']['track_status'] ?? null;
        $expectedDelivery = $trackingData['tracking_data']['expected_delivery_date'] ?? null;
        
        if (!$currentStatus) {
            return;
        }

        // Map ShipRocket status to our internal status
        $internalStatus = $this->mapShipRocketStatus($currentStatus);
        
        if ($internalStatus && $internalStatus !== $shipment->status) {
            // Update estimated delivery if provided
            if ($expectedDelivery) {
                try {
                    $estimatedDelivery = \Carbon\Carbon::parse($expectedDelivery);
                    $shipment->update(['estimated_delivery' => $estimatedDelivery]);
                } catch (\Exception $e) {
                    Log::warning('Failed to parse expected delivery date', [
                        'shipment_id' => $shipment->id,
                        'date' => $expectedDelivery
                    ]);
                }
            }

            // Update shipment status
            $description = "Status updated from ShipRocket API";
            if (isset($trackingData['tracking_data']['current_status'])) {
                $description = $trackingData['tracking_data']['current_status'];
            }

            $shipment->updateStatus($internalStatus, $description);
            
            Log::info('Shipment tracking updated', [
                'shipment_id' => $shipment->id,
                'old_status' => $shipment->status,
                'new_status' => $internalStatus
            ]);
        }
    }

    /**
     * Map ShipRocket status to internal status
     */
    private function mapShipRocketStatus($shipRocketStatus)
    {
        $statusMap = [
            'SHIPPED' => 'picked_up',
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO' => 'returned',
            'LOST' => 'exception',
            'DAMAGED' => 'exception',
            'UNDELIVERED' => 'exception',
        ];

        $normalizedStatus = strtoupper(str_replace([' ', '-'], '_', $shipRocketStatus));
        
        return $statusMap[$normalizedStatus] ?? null;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateShippingTrackingJob failed permanently', [
            'error' => $exception->getMessage()
        ]);
    }
}
