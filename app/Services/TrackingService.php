<?php

namespace App\Services;

use App\Models\OrderShipment;
use App\Models\ShippingTrackingEvent;
use App\Models\ShippingCarrier;
use App\Jobs\SendShippingNotifications;
use App\Jobs\ProcessShipmentTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TrackingService
{
    protected $carrierIntegration;

    public function __construct(CarrierIntegrationService $carrierIntegration)
    {
        $this->carrierIntegration = $carrierIntegration;
    }

    /**
     * Update tracking information for a shipment
     */
    public function updateTracking(OrderShipment $shipment)
    {
        try {
            if (!$shipment->tracking_number || !$shipment->carrier) {
                return ['success' => false, 'message' => 'No tracking number or carrier available'];
            }

            // Get tracking updates from carrier
            $trackingData = $this->carrierIntegration->getTrackingInfo(
                $shipment->carrier,
                $shipment->tracking_number
            );

            if (!$trackingData['success']) {
                return $trackingData;
            }

            $events = $trackingData['events'] ?? [];
            $currentStatus = $trackingData['current_status'] ?? $shipment->status;
            $estimatedDelivery = $trackingData['estimated_delivery'] ?? $shipment->estimated_delivery;

            // Process tracking events
            $newEventsCount = 0;
            foreach ($events as $eventData) {
                if ($this->addTrackingEvent($shipment, $eventData)) {
                    $newEventsCount++;
                }
            }

            // Update shipment with latest status
            $statusChanged = $shipment->status !== $currentStatus;
            
            $shipment->update([
                'status' => $currentStatus,
                'estimated_delivery' => $estimatedDelivery,
                'delivered_at' => $currentStatus === 'delivered' && !$shipment->delivered_at 
                    ? now() 
                    : $shipment->delivered_at,
                'metadata' => array_merge($shipment->metadata ?? [], [
                    'last_tracking_update' => now(),
                    'tracking_events_count' => $shipment->trackingEvents()->count()
                ])
            ]);

            // Update order status if shipment status changed
            if ($statusChanged) {
                $this->updateOrderStatus($shipment);
                
                // Send notification for significant status changes
                if (in_array($currentStatus, ['out_for_delivery', 'delivered', 'exception'])) {
                    SendShippingNotifications::dispatch($shipment, 'status_update');
                }
            }

            // Schedule next tracking update if shipment is still in transit
            if (in_array($currentStatus, ['picked_up', 'in_transit', 'out_for_delivery'])) {
                ProcessShipmentTracking::dispatch($shipment)->delay(now()->addHours(4));
            }

            return [
                'success' => true,
                'new_events' => $newEventsCount,
                'status_changed' => $statusChanged,
                'current_status' => $currentStatus
            ];

        } catch (Exception $e) {
            Log::error('Tracking update failed', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Add tracking event to shipment
     */
    public function addTrackingEvent(OrderShipment $shipment, array $eventData)
    {
        // Check if event already exists
        $existingEvent = $shipment->trackingEvents()
            ->where('status', $eventData['status'])
            ->where('event_time', $eventData['event_time'])
            ->where('location', $eventData['location'] ?? '')
            ->first();

        if ($existingEvent) {
            return false; // Event already exists
        }

        // Create new tracking event
        $event = $shipment->trackingEvents()->create([
            'status' => $eventData['status'],
            'description' => $eventData['description'] ?? '',
            'location' => $eventData['location'] ?? '',
            'event_time' => $eventData['event_time'],
            'is_delivered' => in_array($eventData['status'], ['delivered', 'completed']),
            'is_exception' => in_array($eventData['status'], ['exception', 'failed', 'returned']),
            'metadata' => $eventData['metadata'] ?? []
        ]);

        return true;
    }

    /**
     * Update order status based on shipment status
     */
    protected function updateOrderStatus(OrderShipment $shipment)
    {
        $order = $shipment->order;
        
        $statusMapping = [
            'pending' => 'confirmed',
            'picked_up' => 'shipped',
            'in_transit' => 'shipped',
            'out_for_delivery' => 'shipped',
            'delivered' => 'delivered',
            'exception' => 'shipped', // Keep as shipped unless all shipments fail
            'returned' => 'returned'
        ];

        $newOrderStatus = $statusMapping[$shipment->status] ?? $order->status;

        // For orders with multiple shipments, check all shipments
        if ($order->shipments()->count() > 1) {
            $allShipments = $order->shipments;
            
            if ($allShipments->every(fn($s) => $s->status === 'delivered')) {
                $newOrderStatus = 'delivered';
            } elseif ($allShipments->some(fn($s) => in_array($s->status, ['picked_up', 'in_transit', 'out_for_delivery']))) {
                $newOrderStatus = 'shipped';
            } elseif ($allShipments->every(fn($s) => $s->status === 'exception')) {
                $newOrderStatus = 'failed';
            }
        }

        if ($order->status !== $newOrderStatus) {
            $order->update(['status' => $newOrderStatus]);
        }
    }

    /**
     * Handle webhook from shipping carrier
     */
    public function handleWebhook(string $carrierCode, array $webhookData)
    {
        try {
            $carrier = ShippingCarrier::where('code', $carrierCode)->first();
            
            if (!$carrier) {
                return ['success' => false, 'message' => 'Carrier not found'];
            }

            // Extract tracking number from webhook
            $trackingNumber = $this->extractTrackingNumber($carrierCode, $webhookData);
            
            if (!$trackingNumber) {
                return ['success' => false, 'message' => 'Tracking number not found in webhook'];
            }

            // Find shipment
            $shipment = OrderShipment::where('tracking_number', $trackingNumber)
                ->where('carrier_id', $carrier->id)
                ->first();

            if (!$shipment) {
                return ['success' => false, 'message' => 'Shipment not found'];
            }

            // Process webhook data
            $eventData = $this->parseWebhookEvent($carrierCode, $webhookData);
            
            if ($eventData) {
                $this->addTrackingEvent($shipment, $eventData);
                
                // Update shipment status if needed
                if ($eventData['status'] !== $shipment->status) {
                    $shipment->update(['status' => $eventData['status']]);
                    $this->updateOrderStatus($shipment);
                    
                    // Send notification for important updates
                    if (in_array($eventData['status'], ['out_for_delivery', 'delivered', 'exception'])) {
                        SendShippingNotifications::dispatch($shipment, 'webhook_update');
                    }
                }
            }

            return ['success' => true, 'message' => 'Webhook processed successfully'];

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'carrier_code' => $carrierCode,
                'webhook_data' => $webhookData,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract tracking number from webhook data
     */
    protected function extractTrackingNumber(string $carrierCode, array $webhookData): ?string
    {
        $trackingFields = [
            'tracking_number',
            'awb_number',
            'waybill_number',
            'shipment_id',
            'reference_number'
        ];

        foreach ($trackingFields as $field) {
            if (!empty($webhookData[$field])) {
                return $webhookData[$field];
            }
        }

        // Carrier-specific extraction
        return match($carrierCode) {
            'shiprocket' => $webhookData['awb'] ?? $webhookData['order_id'] ?? null,
            'delhivery' => $webhookData['waybill'] ?? null,
            'bluedart' => $webhookData['waybill_number'] ?? null,
            default => null
        };
    }

    /**
     * Parse webhook event data
     */
    protected function parseWebhookEvent(string $carrierCode, array $webhookData): ?array
    {
        $status = $this->normalizeStatus($carrierCode, $webhookData['status'] ?? '');
        
        if (!$status) {
            return null;
        }

        return [
            'status' => $status,
            'description' => $webhookData['description'] ?? $webhookData['message'] ?? '',
            'location' => $this->extractLocation($webhookData),
            'event_time' => $this->parseEventTime($webhookData),
            'metadata' => [
                'webhook_data' => $webhookData,
                'carrier_code' => $carrierCode,
                'processed_at' => now()
            ]
        ];
    }

    /**
     * Normalize carrier-specific status to standard status
     */
    protected function normalizeStatus(string $carrierCode, string $carrierStatus): ?string
    {
        $statusMapping = [
            'shiprocket' => [
                'Pickup Scheduled' => 'pending',
                'Shipped' => 'picked_up',
                'In Transit' => 'in_transit',
                'Out for Delivery' => 'out_for_delivery',
                'Delivered' => 'delivered',
                'RTO' => 'returned',
                'Exception' => 'exception'
            ],
            'delhivery' => [
                'Dispatched' => 'picked_up',
                'In transit' => 'in_transit',
                'Out for Delivery' => 'out_for_delivery',
                'Delivered' => 'delivered',
                'RTO' => 'returned'
            ],
            'default' => [
                'pending' => 'pending',
                'picked_up' => 'picked_up',
                'shipped' => 'picked_up',
                'in_transit' => 'in_transit',
                'out_for_delivery' => 'out_for_delivery',
                'delivered' => 'delivered',
                'exception' => 'exception',
                'returned' => 'returned',
                'rto' => 'returned'
            ]
        ];

        $mapping = $statusMapping[$carrierCode] ?? $statusMapping['default'];
        $normalizedStatus = strtolower(trim($carrierStatus));
        
        return $mapping[$normalizedStatus] ?? $mapping[$carrierStatus] ?? null;
    }

    /**
     * Extract location from webhook data
     */
    protected function extractLocation(array $webhookData): string
    {
        $locationFields = [
            'location',
            'city',
            'hub',
            'facility',
            'center'
        ];

        foreach ($locationFields as $field) {
            if (!empty($webhookData[$field])) {
                return $webhookData[$field];
            }
        }

        // Try to build location from address fields
        $parts = array_filter([
            $webhookData['city'] ?? '',
            $webhookData['state'] ?? '',
            $webhookData['country'] ?? ''
        ]);

        return implode(', ', $parts);
    }

    /**
     * Parse event time from webhook data
     */
    protected function parseEventTime(array $webhookData): Carbon
    {
        $timeFields = [
            'event_time',
            'timestamp',
            'date_time',
            'created_at',
            'updated_at'
        ];

        foreach ($timeFields as $field) {
            if (!empty($webhookData[$field])) {
                try {
                    return Carbon::parse($webhookData[$field]);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        return now();
    }

    /**
     * Get tracking information for display
     */
    public function getTrackingInfo(OrderShipment $shipment): array
    {
        $events = $shipment->trackingEvents()
            ->orderBy('event_time', 'desc')
            ->get();

        $progress = $this->calculateProgress($shipment->status);
        
        return [
            'shipment' => $shipment,
            'events' => $events,
            'progress' => $progress,
            'tracking_url' => $shipment->tracking_url,
            'estimated_delivery' => $shipment->estimated_delivery,
            'last_update' => $events->first()?->event_time,
            'status_description' => $this->getStatusDescription($shipment->status)
        ];
    }

    /**
     * Calculate delivery progress percentage
     */
    protected function calculateProgress(string $status): int
    {
        return match($status) {
            'pending' => 10,
            'picked_up' => 30,
            'in_transit' => 60,
            'out_for_delivery' => 85,
            'delivered' => 100,
            'exception' => 50,
            'returned' => 0,
            default => 0
        };
    }

    /**
     * Get user-friendly status description
     */
    protected function getStatusDescription(string $status): string
    {
        return match($status) {
            'pending' => 'Your order is being prepared for shipment',
            'picked_up' => 'Package has been picked up by the carrier',
            'in_transit' => 'Package is on its way to you',
            'out_for_delivery' => 'Package is out for delivery today',
            'delivered' => 'Package has been successfully delivered',
            'exception' => 'There was an issue with delivery - we\'re working on it',
            'returned' => 'Package is being returned to sender',
            default => 'Processing your order'
        };
    }

    /**
     * Bulk update tracking for multiple shipments
     */
    public function bulkUpdateTracking(array $shipmentIds = null)
    {
        $query = OrderShipment::whereNotNull('tracking_number')
            ->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery']);

        if ($shipmentIds) {
            $query->whereIn('id', $shipmentIds);
        }

        $shipments = $query->get();
        $results = [];

        foreach ($shipments as $shipment) {
            $result = $this->updateTracking($shipment);
            $results[] = [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Get tracking analytics
     */
    public function getTrackingAnalytics($period = '30 days')
    {
        $startDate = now()->sub($period);

        return [
            'total_tracking_updates' => ShippingTrackingEvent::where('created_at', '>=', $startDate)->count(),
            'delivery_success_rate' => $this->getDeliverySuccessRate($startDate),
            'average_tracking_events_per_shipment' => $this->getAverageTrackingEvents($startDate),
            'exception_rate' => $this->getExceptionRate($startDate),
            'status_distribution' => $this->getStatusDistribution($startDate)
        ];
    }

    /**
     * Get delivery success rate
     */
    protected function getDeliverySuccessRate($startDate): float
    {
        $totalShipments = OrderShipment::where('created_at', '>=', $startDate)->count();
        $deliveredShipments = OrderShipment::where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->count();

        return $totalShipments > 0 ? ($deliveredShipments / $totalShipments) * 100 : 0;
    }

    /**
     * Get average tracking events per shipment
     */
    protected function getAverageTrackingEvents($startDate): float
    {
        return ShippingTrackingEvent::where('created_at', '>=', $startDate)
            ->join('order_shipments', 'shipping_tracking_events.shipment_id', '=', 'order_shipments.id')
            ->selectRaw('COUNT(*) / COUNT(DISTINCT shipment_id) as avg_events')
            ->value('avg_events') ?? 0;
    }

    /**
     * Get exception rate
     */
    protected function getExceptionRate($startDate): float
    {
        $totalShipments = OrderShipment::where('created_at', '>=', $startDate)->count();
        $exceptionShipments = OrderShipment::where('status', 'exception')
            ->where('created_at', '>=', $startDate)
            ->count();

        return $totalShipments > 0 ? ($exceptionShipments / $totalShipments) * 100 : 0;
    }

    /**
     * Get status distribution
     */
    protected function getStatusDistribution($startDate): array
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();
    }
}