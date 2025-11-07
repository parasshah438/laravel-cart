<?php

namespace App\Jobs;

use App\Models\OrderShipment;
use App\Services\TrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessShipmentTracking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shipment;
    
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
    public function __construct(OrderShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     */
    public function handle(TrackingService $trackingService)
    {
        try {
            Log::info('Processing shipment tracking', [
                'shipment_id' => $this->shipment->id,
                'tracking_number' => $this->shipment->tracking_number
            ]);

            $result = $trackingService->updateTracking($this->shipment);

            if ($result['success']) {
                Log::info('Shipment tracking updated successfully', [
                    'shipment_id' => $this->shipment->id,
                    'new_events' => $result['new_events'] ?? 0,
                    'status_changed' => $result['status_changed'] ?? false,
                    'current_status' => $result['current_status'] ?? null
                ]);
            } else {
                Log::warning('Shipment tracking update failed', [
                    'shipment_id' => $this->shipment->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);

                // If this is not the last attempt, retry with delay
                if ($this->attempts() < $this->tries) {
                    $this->release(300); // Retry after 5 minutes
                }
            }

        } catch (\Exception $e) {
            Log::error('Exception in ProcessShipmentTracking job', [
                'shipment_id' => $this->shipment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // If this is not the last attempt, retry with delay
            if ($this->attempts() < $this->tries) {
                $this->release(600); // Retry after 10 minutes
            } else {
                // Mark shipment as having tracking issues after all retries
                $this->shipment->update([
                    'metadata' => array_merge($this->shipment->metadata ?? [], [
                        'tracking_error' => $e->getMessage(),
                        'last_tracking_attempt' => now(),
                        'tracking_failed' => true
                    ])
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('ProcessShipmentTracking job failed permanently', [
            'shipment_id' => $this->shipment->id,
            'tracking_number' => $this->shipment->tracking_number,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update shipment metadata to indicate tracking failure
        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'tracking_permanently_failed' => true,
                'tracking_failure_reason' => $exception->getMessage(),
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
            'tracking',
            'carrier:' . $this->shipment->carrier->code ?? 'unknown'
        ];
    }
}