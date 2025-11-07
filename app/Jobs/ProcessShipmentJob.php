<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Services\ShippingService;
use App\Services\RateCalculatorService;
use App\Services\CarrierIntegrationService;
use App\Services\ShippingStatusManager;
use App\Jobs\UpdateShippingStatusJob;
use App\Jobs\SendTrackingNotificationJob;

class ProcessShipmentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $order;
    protected $options;
    protected $priority;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Order $order, 
        array $options = [],
        string $priority = 'normal'
    ) {
        $this->order = $order;
        $this->options = $options;
        $this->priority = $priority;
        
        // Set queue based on priority
        $this->onQueue($this->getQueueName($priority));
    }

    /**
     * Execute the job.
     */
    public function handle(
        ShippingService $shippingService,
        RateCalculatorService $rateCalculator,
        CarrierIntegrationService $carrierService,
        ShippingStatusManager $statusManager
    ): void {
        try {
            Log::info('Processing comprehensive shipment workflow', [
                'order_id' => $this->order->id,
                'priority' => $this->priority,
                'options' => $this->options,
                'attempt' => $this->attempts()
            ]);

            DB::beginTransaction();

            // Step 1: Pre-processing validations
            $this->performPreProcessingValidations();

            // Step 2: Calculate optimal shipping rates
            $rateOptions = $this->calculateShippingRates($rateCalculator);

            // Step 3: Select best carrier and service
            $selectedOption = $this->selectOptimalCarrier($rateOptions);

            // Step 4: Create shipment record
            $shipment = $this->createShipmentRecord($selectedOption);

            // Step 5: Integrate with carrier API
            $carrierResponse = $this->integrateWithCarrier($carrierService, $shipment, $selectedOption);

            // Step 6: Update shipment with carrier details
            $this->updateShipmentWithCarrierData($shipment, $carrierResponse);

            // Step 7: Generate shipping labels and documents
            $documents = $this->generateShippingDocuments($carrierService, $shipment);

            // Step 8: Update order and shipment status
            $this->updateOrderAndShipmentStatus($statusManager, $shipment);

            // Step 9: Schedule tracking and notifications
            $this->scheduleTrackingAndNotifications($shipment);

            // Step 10: Handle post-processing tasks
            $this->performPostProcessingTasks($shipment, $documents);

            DB::commit();

            Log::info('Shipment processing completed successfully', [
                'order_id' => $this->order->id,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'carrier' => $selectedOption['carrier']['name'],
                'processing_time' => $this->getProcessingTime()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('ProcessShipmentJob failed', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'trace' => $e->getTraceAsString()
            ]);

            // Handle retryable vs non-retryable errors
            if ($this->isRetryableError($e)) {
                if ($this->attempts() < $this->tries) {
                    // Calculate exponential backoff delay
                    $delay = $this->calculateRetryDelay();
                    $this->release($delay);
                    
                    Log::info('Job released for retry', [
                        'order_id' => $this->order->id,
                        'retry_delay' => $delay,
                        'next_attempt' => $this->attempts() + 1
                    ]);
                    return;
                }
            }

            // Mark as failed if non-retryable or max attempts reached
            $this->handleJobFailure($e);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        DB::beginTransaction();
        
        try {
            Log::error('ProcessShipmentJob failed permanently', [
                'order_id' => $this->order->id,
                'error' => $exception->getMessage(),
                'total_attempts' => $this->attempts(),
                'priority' => $this->priority
            ]);

            // Mark order for manual processing
            $this->order->update([
                'shipment_processing_status' => 'failed',
                'shipment_processing_error' => $exception->getMessage(),
                'manual_processing_required' => true,
                'failed_at' => now()
            ]);

            // Create failure record for tracking
            $this->createFailureRecord($exception);

            // Send admin notifications
            $this->notifyAdminsOfFailure($exception);

            // Create manual processing ticket
            $this->createManualProcessingTicket($exception);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to handle job failure properly', [
                'order_id' => $this->order->id,
                'original_error' => $exception->getMessage(),
                'handling_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Perform pre-processing validations
     */
    protected function performPreProcessingValidations(): void
    {
        // Check order eligibility
        if (!$this->order->canCreateShipment()) {
            throw new \Exception(
                "Order {$this->order->id} is not eligible for shipment creation. " .
                "Status: {$this->order->status}, Payment: {$this->order->payment_status}"
            );
        }

        // Check for existing shipments
        if ($this->order->shipments()->exists()) {
            throw new \Exception("Shipment already exists for order {$this->order->id}");
        }

        // Validate shipping address
        if (!$this->order->shippingAddress) {
            throw new \Exception("No shipping address found for order {$this->order->id}");
        }

        // Validate order items
        if ($this->order->items->isEmpty()) {
            throw new \Exception("No items found for order {$this->order->id}");
        }

        // Check inventory availability
        foreach ($this->order->items as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new \Exception("Insufficient stock for product {$item->product->name}");
            }
        }

        Log::info('Pre-processing validations passed', ['order_id' => $this->order->id]);
    }

    /**
     * Calculate shipping rates from multiple carriers
     */
    protected function calculateShippingRates(RateCalculatorService $rateCalculator): array
    {
        $rateOptions = $rateCalculator->calculateRates(
            $this->order,
            $this->options['preferred_carriers'] ?? null,
            $this->options['service_types'] ?? null
        );

        if (empty($rateOptions)) {
            throw new \Exception('No shipping rates available for this order');
        }

        Log::info('Shipping rates calculated', [
            'order_id' => $this->order->id,
            'rate_options_count' => count($rateOptions),
            'cheapest_rate' => min(array_column($rateOptions, 'cost')),
            'fastest_service' => min(array_column($rateOptions, 'estimated_days'))
        ]);

        return $rateOptions;
    }

    /**
     * Select optimal carrier based on criteria
     */
    protected function selectOptimalCarrier(array $rateOptions): array
    {
        $selectionCriteria = $this->options['selection_criteria'] ?? 'balanced';

        switch ($selectionCriteria) {
            case 'cheapest':
                $selected = $this->selectCheapestOption($rateOptions);
                break;
            case 'fastest':
                $selected = $this->selectFastestOption($rateOptions);
                break;
            case 'reliable':
                $selected = $this->selectMostReliableOption($rateOptions);
                break;
            case 'balanced':
            default:
                $selected = $this->selectBalancedOption($rateOptions);
                break;
        }

        Log::info('Carrier selected', [
            'order_id' => $this->order->id,
            'selection_criteria' => $selectionCriteria,
            'selected_carrier' => $selected['carrier']['name'],
            'selected_service' => $selected['service']['name'],
            'cost' => $selected['cost'],
            'estimated_delivery' => $selected['estimated_delivery_date']
        ]);

        return $selected;
    }

    /**
     * Create shipment record in database
     */
    protected function createShipmentRecord(array $selectedOption): OrderShipment
    {
        $shipmentData = [
            'order_id' => $this->order->id,
            'carrier_id' => $selectedOption['carrier']['id'],
            'service_id' => $selectedOption['service']['id'],
            'status' => 'pending',
            'shipping_cost' => $selectedOption['cost'],
            'estimated_delivery_date' => $selectedOption['estimated_delivery_date'],
            'package_dimensions' => $selectedOption['package_dimensions'],
            'package_weight' => $selectedOption['package_weight'],
            'insurance_value' => $this->calculateInsuranceValue(),
            'metadata' => [
                'processing_priority' => $this->priority,
                'selection_criteria' => $this->options['selection_criteria'] ?? 'balanced',
                'available_options_count' => count($this->options),
                'created_by_job' => true,
                'job_attempt' => $this->attempts()
            ]
        ];

        $shipment = OrderShipment::create($shipmentData);

        Log::info('Shipment record created', [
            'order_id' => $this->order->id,
            'shipment_id' => $shipment->id,
            'carrier' => $selectedOption['carrier']['name']
        ]);

        return $shipment;
    }

    /**
     * Integrate with carrier API
     */
    protected function integrateWithCarrier(
        CarrierIntegrationService $carrierService, 
        OrderShipment $shipment, 
        array $selectedOption
    ): array {
        $carrierName = $selectedOption['carrier']['code'];
        
        $response = $carrierService->createShipment(
            $carrierName,
            $this->order,
            $shipment,
            $selectedOption
        );

        if (!$response['success']) {
            throw new \Exception("Carrier integration failed: " . $response['error']);
        }

        Log::info('Carrier integration successful', [
            'order_id' => $this->order->id,
            'shipment_id' => $shipment->id,
            'carrier' => $carrierName,
            'carrier_shipment_id' => $response['carrier_shipment_id'] ?? null
        ]);

        return $response;
    }

    /**
     * Update shipment with carrier data
     */
    protected function updateShipmentWithCarrierData(OrderShipment $shipment, array $carrierResponse): void
    {
        $updateData = [
            'carrier_shipment_id' => $carrierResponse['carrier_shipment_id'],
            'tracking_number' => $carrierResponse['tracking_number'] ?? null,
            'carrier_reference' => $carrierResponse['carrier_reference'] ?? null,
            'shipping_label_url' => $carrierResponse['label_url'] ?? null,
            'expected_pickup_date' => $carrierResponse['pickup_date'] ?? null,
            'metadata' => array_merge($shipment->metadata ?? [], [
                'carrier_response' => $carrierResponse,
                'integration_completed_at' => now()
            ])
        ];

        $shipment->update($updateData);

        Log::info('Shipment updated with carrier data', [
            'shipment_id' => $shipment->id,
            'tracking_number' => $updateData['tracking_number']
        ]);
    }

    /**
     * Generate shipping documents
     */
    protected function generateShippingDocuments(
        CarrierIntegrationService $carrierService,
        OrderShipment $shipment
    ): array {
        $documents = [];

        try {
            // Generate shipping label
            if ($labelUrl = $carrierService->generateLabel($shipment)) {
                $documents['label'] = $labelUrl;
            }

            // Generate invoice if required
            if ($this->requiresInvoice($shipment)) {
                $documents['invoice'] = $carrierService->generateInvoice($shipment);
            }

            // Generate packing slip
            $documents['packing_slip'] = $this->generatePackingSlip($shipment);

            Log::info('Shipping documents generated', [
                'shipment_id' => $shipment->id,
                'documents' => array_keys($documents)
            ]);

        } catch (\Exception $e) {
            Log::warning('Some shipping documents could not be generated', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
        }

        return $documents;
    }

    /**
     * Update order and shipment status
     */
    protected function updateOrderAndShipmentStatus(
        ShippingStatusManager $statusManager,
        OrderShipment $shipment
    ): void {
        // Update shipment to confirmed status
        $statusManager->updateStatus($shipment, 'confirmed', [
            'confirmed_by' => 'system',
            'confirmed_at' => now(),
            'processing_job_id' => $this->job->getJobId()
        ]);

        // Update order status
        $this->order->update([
            'status' => 'confirmed',
            'shipment_created_at' => now(),
            'shipment_processing_status' => 'completed'
        ]);

        Log::info('Order and shipment status updated', [
            'order_id' => $this->order->id,
            'shipment_id' => $shipment->id,
            'new_status' => 'confirmed'
        ]);
    }

    /**
     * Schedule tracking and notifications
     */
    protected function scheduleTrackingAndNotifications(OrderShipment $shipment): void
    {
        // Schedule immediate confirmation notification
        SendTrackingNotificationJob::dispatch(
            $shipment,
            [
                'type' => 'shipment_confirmed',
                'message' => 'Your order has been confirmed and will be shipped soon'
            ],
            ['email'],
            'customer'
        )->delay(now()->addMinutes(2));

        // Schedule pickup notification (if pickup is scheduled)
        if ($shipment->expected_pickup_date) {
            UpdateShippingStatusJob::dispatch(
                $shipment,
                'ready_for_pickup',
                ['scheduled_pickup' => $shipment->expected_pickup_date]
            )->delay($shipment->expected_pickup_date->subHours(2));
        }

        Log::info('Tracking and notifications scheduled', [
            'shipment_id' => $shipment->id,
            'pickup_date' => $shipment->expected_pickup_date
        ]);
    }

    /**
     * Perform post-processing tasks
     */
    protected function performPostProcessingTasks(OrderShipment $shipment, array $documents): void
    {
        // Update inventory
        $this->updateInventory();

        // Cache shipment data for quick access
        $this->cacheShipmentData($shipment);

        // Log for analytics
        $this->logForAnalytics($shipment, $documents);

        // Send internal notifications
        $this->sendInternalNotifications($shipment);

        Log::info('Post-processing tasks completed', [
            'shipment_id' => $shipment->id,
            'order_id' => $this->order->id
        ]);
    }

    // Helper methods
    protected function getQueueName(string $priority): string
    {
        return match($priority) {
            'urgent' => 'shipment-urgent',
            'high' => 'shipment-high',
            'low' => 'shipment-low',
            default => 'shipment-normal'
        };
    }

    protected function isRetryableError(\Exception $e): bool
    {
        $retryableErrors = [
            'network timeout',
            'connection refused',
            'service unavailable',
            'rate limit exceeded',
            'temporary error'
        ];

        $errorMessage = strtolower($e->getMessage());
        
        foreach ($retryableErrors as $pattern) {
            if (str_contains($errorMessage, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function calculateRetryDelay(): int
    {
        // Exponential backoff: 2^attempt minutes
        return pow(2, $this->attempts()) * 60;
    }

    protected function getProcessingTime(): float
    {
        return round(microtime(true) - LARAVEL_START, 2);
    }

    protected function selectCheapestOption(array $options): array
    {
        return collect($options)->sortBy('cost')->first();
    }

    protected function selectFastestOption(array $options): array
    {
        return collect($options)->sortBy('estimated_days')->first();
    }

    protected function selectMostReliableOption(array $options): array
    {
        return collect($options)->sortByDesc('carrier.reliability_score')->first();
    }

    protected function selectBalancedOption(array $options): array
    {
        return collect($options)->map(function ($option) {
            $option['score'] = $this->calculateBalancedScore($option);
            return $option;
        })->sortByDesc('score')->first();
    }

    protected function calculateBalancedScore(array $option): float
    {
        $costWeight = 0.4;
        $speedWeight = 0.3;
        $reliabilityWeight = 0.3;

        $maxCost = 1000; // Normalize cost
        $maxDays = 7;    // Normalize delivery days

        $costScore = (1 - ($option['cost'] / $maxCost)) * $costWeight;
        $speedScore = (1 - ($option['estimated_days'] / $maxDays)) * $speedWeight;
        $reliabilityScore = ($option['carrier']['reliability_score'] / 10) * $reliabilityWeight;

        return $costScore + $speedScore + $reliabilityScore;
    }

    protected function calculateInsuranceValue(): float
    {
        return $this->order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    protected function requiresInvoice(OrderShipment $shipment): bool
    {
        return $shipment->shipping_cost > 500 || $this->order->total > 10000;
    }

    protected function generatePackingSlip(OrderShipment $shipment): string
    {
        // Generate packing slip URL
        return route('admin.shipments.packing-slip', $shipment);
    }

    protected function updateInventory(): void
    {
        foreach ($this->order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }
    }

    protected function cacheShipmentData(OrderShipment $shipment): void
    {
        Cache::put(
            "shipment:{$shipment->id}", 
            $shipment->toArray(), 
            now()->addHours(24)
        );
    }

    protected function logForAnalytics(OrderShipment $shipment, array $documents): void
    {
        // Log shipment data for analytics
        Log::info('Shipment analytics data', [
            'shipment_id' => $shipment->id,
            'order_value' => $this->order->total,
            'shipping_cost' => $shipment->shipping_cost,
            'carrier' => $shipment->carrier->name,
            'processing_time' => $this->getProcessingTime(),
            'documents_generated' => count($documents)
        ]);
    }

    protected function sendInternalNotifications(OrderShipment $shipment): void
    {
        // Send notification to fulfillment team
        SendTrackingNotificationJob::dispatch(
            $shipment,
            [
                'type' => 'shipment_created',
                'priority' => $this->priority
            ],
            ['email'],
            'admin'
        );
    }

    protected function handleJobFailure(\Exception $exception): void
    {
        // Mark order as requiring manual processing
        $this->order->update([
            'shipment_processing_status' => 'failed',
            'manual_processing_required' => true
        ]);
    }

    protected function createFailureRecord(\Exception $exception): void
    {
        // Create failure record for tracking and analysis
        Log::error('Creating failure record', [
            'order_id' => $this->order->id,
            'job_class' => static::class,
            'error' => $exception->getMessage()
        ]);
    }

    protected function notifyAdminsOfFailure(\Exception $exception): void
    {
        // Send admin notification about shipment processing failure
        Log::info('Admin notification would be sent for shipment failure', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }

    protected function createManualProcessingTicket(\Exception $exception): void
    {
        // Create support ticket for manual processing
        Log::info('Manual processing ticket would be created', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'order:' . $this->order->id,
            'priority:' . $this->priority,
            'shipment_processing'
        ];
    }
}
