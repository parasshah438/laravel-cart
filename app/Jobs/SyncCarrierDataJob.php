<?php

namespace App\Jobs;

use App\Services\ShippingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncCarrierDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $carrier;
    protected $dataType;
    protected $forceUpdate;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $carrier = null, 
        string $dataType = 'all', 
        bool $forceUpdate = false
    ) {
        $this->carrier = $carrier;
        $this->dataType = $dataType;
        $this->forceUpdate = $forceUpdate;
    }

    /**
     * Execute the job.
     */
    public function handle(ShippingService $shippingService)
    {
        try {
            Log::info('Starting carrier data synchronization', [
                'carrier' => $this->carrier ?? 'all',
                'data_type' => $this->dataType,
                'force_update' => $this->forceUpdate
            ]);

            $carriers = $this->carrier ? [$this->carrier] : config('shipping.supported_carriers', []);
            $syncResults = [];

            foreach ($carriers as $carrier) {
                $carrierResults = $this->syncCarrierData($carrier, $shippingService);
                $syncResults[$carrier] = $carrierResults;
            }

            // Update sync status cache
            Cache::put('carrier_data_sync_status', [
                'last_sync' => now(),
                'results' => $syncResults,
                'status' => 'completed'
            ], 86400); // 24 hours

            Log::info('Carrier data synchronization completed', [
                'results' => $syncResults,
                'total_carriers' => count($carriers)
            ]);

        } catch (\Exception $e) {
            Log::error('Carrier data synchronization failed', [
                'carrier' => $this->carrier,
                'data_type' => $this->dataType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update sync status with error
            Cache::put('carrier_data_sync_status', [
                'last_sync' => now(),
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 86400);

            throw $e;
        }
    }

    /**
     * Sync data for a specific carrier
     */
    protected function syncCarrierData(string $carrier, ShippingService $shippingService): array
    {
        $results = [
            'carrier' => $carrier,
            'synced_data' => [],
            'errors' => [],
            'start_time' => now(),
        ];

        try {
            Log::info("Syncing data for carrier: {$carrier}");

            // Check if carrier is enabled
            if (!$this->isCarrierEnabled($carrier)) {
                $results['errors'][] = 'Carrier is disabled';
                return $results;
            }

            // Sync different types of data based on dataType parameter
            switch ($this->dataType) {
                case 'all':
                    $this->syncServiceCodes($carrier, $results);
                    $this->syncPincodeServiceability($carrier, $results);
                    $this->syncRateCards($carrier, $results);
                    $this->syncCarrierInfo($carrier, $results);
                    break;

                case 'service_codes':
                    $this->syncServiceCodes($carrier, $results);
                    break;

                case 'pincode_serviceability':
                    $this->syncPincodeServiceability($carrier, $results);
                    break;

                case 'rate_cards':
                    $this->syncRateCards($carrier, $results);
                    break;

                case 'carrier_info':
                    $this->syncCarrierInfo($carrier, $results);
                    break;

                default:
                    $results['errors'][] = "Unknown data type: {$this->dataType}";
            }

            $results['end_time'] = now();
            $results['duration'] = $results['end_time']->diffInSeconds($results['start_time']);

            Log::info("Completed syncing data for carrier: {$carrier}", [
                'duration' => $results['duration'],
                'synced_data_count' => count($results['synced_data'])
            ]);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error("Failed to sync data for carrier: {$carrier}", [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Check if carrier is enabled
     */
    protected function isCarrierEnabled(string $carrier): bool
    {
        return config("shipping.carriers.{$carrier}.enabled", false);
    }

    /**
     * Sync service codes for carrier
     */
    protected function syncServiceCodes(string $carrier, array &$results)
    {
        try {
            $cacheKey = "carrier_service_codes_{$carrier}";
            
            if (!$this->forceUpdate && Cache::has($cacheKey)) {
                Log::info("Service codes already cached for {$carrier}, skipping");
                return;
            }

            $serviceCodes = $this->fetchServiceCodes($carrier);
            
            if (!empty($serviceCodes)) {
                Cache::put($cacheKey, $serviceCodes, 86400 * 7); // Cache for 7 days
                $results['synced_data'][] = [
                    'type' => 'service_codes',
                    'count' => count($serviceCodes),
                    'cached_until' => now()->addDays(7)
                ];
                
                Log::info("Synced service codes for {$carrier}", [
                    'count' => count($serviceCodes)
                ]);
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Service codes sync failed: " . $e->getMessage();
            Log::error("Failed to sync service codes for {$carrier}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync pincode serviceability data
     */
    protected function syncPincodeServiceability(string $carrier, array &$results)
    {
        try {
            $cacheKey = "carrier_pincode_serviceability_{$carrier}";
            
            if (!$this->forceUpdate && Cache::has($cacheKey)) {
                Log::info("Pincode serviceability already cached for {$carrier}, skipping");
                return;
            }

            $serviceabilityData = $this->fetchPincodeServiceability($carrier);
            
            if (!empty($serviceabilityData)) {
                Cache::put($cacheKey, $serviceabilityData, 86400 * 3); // Cache for 3 days
                $results['synced_data'][] = [
                    'type' => 'pincode_serviceability',
                    'count' => count($serviceabilityData),
                    'cached_until' => now()->addDays(3)
                ];
                
                Log::info("Synced pincode serviceability for {$carrier}", [
                    'count' => count($serviceabilityData)
                ]);
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Pincode serviceability sync failed: " . $e->getMessage();
            Log::error("Failed to sync pincode serviceability for {$carrier}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync rate cards
     */
    protected function syncRateCards(string $carrier, array &$results)
    {
        try {
            $cacheKey = "carrier_rate_cards_{$carrier}";
            
            if (!$this->forceUpdate && Cache::has($cacheKey)) {
                Log::info("Rate cards already cached for {$carrier}, skipping");
                return;
            }

            $rateCards = $this->fetchRateCards($carrier);
            
            if (!empty($rateCards)) {
                Cache::put($cacheKey, $rateCards, 86400); // Cache for 1 day
                $results['synced_data'][] = [
                    'type' => 'rate_cards',
                    'count' => count($rateCards),
                    'cached_until' => now()->addDay()
                ];
                
                Log::info("Synced rate cards for {$carrier}", [
                    'count' => count($rateCards)
                ]);
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Rate cards sync failed: " . $e->getMessage();
            Log::error("Failed to sync rate cards for {$carrier}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync carrier information
     */
    protected function syncCarrierInfo(string $carrier, array &$results)
    {
        try {
            $cacheKey = "carrier_info_{$carrier}";
            
            if (!$this->forceUpdate && Cache::has($cacheKey)) {
                Log::info("Carrier info already cached for {$carrier}, skipping");
                return;
            }

            $carrierInfo = $this->fetchCarrierInfo($carrier);
            
            if (!empty($carrierInfo)) {
                Cache::put($cacheKey, $carrierInfo, 86400 * 7); // Cache for 7 days
                $results['synced_data'][] = [
                    'type' => 'carrier_info',
                    'data' => array_keys($carrierInfo),
                    'cached_until' => now()->addDays(7)
                ];
                
                Log::info("Synced carrier info for {$carrier}");
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Carrier info sync failed: " . $e->getMessage();
            Log::error("Failed to sync carrier info for {$carrier}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fetch service codes from carrier API
     */
    protected function fetchServiceCodes(string $carrier): array
    {
        switch ($carrier) {
            case 'shiprocket':
                return $this->fetchShipRocketServiceCodes();
            case 'delhivery':
                return $this->fetchDelhiveryServiceCodes();
            case 'bluedart':
                return $this->fetchBlueDartServiceCodes();
            default:
                Log::warning("Service codes fetch not implemented for carrier: {$carrier}");
                return [];
        }
    }

    /**
     * Fetch ShipRocket service codes
     */
    protected function fetchShipRocketServiceCodes(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('shipping.carriers.shiprocket.token'),
            'Content-Type' => 'application/json'
        ])->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability');

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        throw new \Exception('Failed to fetch ShipRocket service codes: ' . $response->body());
    }

    /**
     * Fetch Delhivery service codes
     */
    protected function fetchDelhiveryServiceCodes(): array
    {
        // Implementation for Delhivery API
        return [];
    }

    /**
     * Fetch BlueDart service codes
     */
    protected function fetchBlueDartServiceCodes(): array
    {
        // Implementation for BlueDart API
        return [];
    }

    /**
     * Fetch pincode serviceability data
     */
    protected function fetchPincodeServiceability(string $carrier): array
    {
        // This would typically involve downloading large datasets
        // For now, return empty array - implementation depends on carrier APIs
        Log::info("Fetching pincode serviceability for {$carrier}");
        return [];
    }

    /**
     * Fetch rate cards
     */
    protected function fetchRateCards(string $carrier): array
    {
        // Rate cards are usually large files or API endpoints
        // Implementation depends on carrier APIs
        Log::info("Fetching rate cards for {$carrier}");
        return [];
    }

    /**
     * Fetch carrier information
     */
    protected function fetchCarrierInfo(string $carrier): array
    {
        switch ($carrier) {
            case 'shiprocket':
                return [
                    'name' => 'ShipRocket',
                    'website' => 'https://shiprocket.in',
                    'supported_services' => ['surface', 'air', 'express'],
                    'cod_support' => true,
                    'tracking_url' => 'https://shiprocket.in/shipment/track/{tracking_number}',
                    'api_version' => 'v2',
                    'last_updated' => now()
                ];

            case 'delhivery':
                return [
                    'name' => 'Delhivery',
                    'website' => 'https://delhivery.com',
                    'supported_services' => ['surface', 'air', 'express'],
                    'cod_support' => true,
                    'tracking_url' => 'https://www.delhivery.com/track/package-{tracking_number}.html',
                    'api_version' => 'v1',
                    'last_updated' => now()
                ];

            case 'bluedart':
                return [
                    'name' => 'Blue Dart',
                    'website' => 'https://www.bluedart.com',
                    'supported_services' => ['air', 'express'],
                    'cod_support' => false,
                    'tracking_url' => 'https://www.bluedart.com/web/guest/trackdartresult?trackFor=0&trackNo={tracking_number}',
                    'api_version' => 'v1',
                    'last_updated' => now()
                ];

            default:
                return [];
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SyncCarrierDataJob failed permanently', [
            'carrier' => $this->carrier,
            'data_type' => $this->dataType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update sync status with permanent failure
        Cache::put('carrier_data_sync_status', [
            'last_sync' => now(),
            'status' => 'failed_permanently',
            'error' => $exception->getMessage(),
            'carrier' => $this->carrier,
            'data_type' => $this->dataType
        ], 86400);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags()
    {
        $tags = ['carrier_sync', 'data_type:' . $this->dataType];
        
        if ($this->carrier) {
            $tags[] = 'carrier:' . $this->carrier;
        }
        
        return $tags;
    }
}