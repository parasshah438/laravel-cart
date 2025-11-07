<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\Cache;
use Exception;

class RateCalculatorService
{
    /**
     * Calculate shipping rate for an order
     */
    public function calculateRate(
        Order $order,
        ShippingCarrier $carrier,
        ShippingMethod $method,
        array $packageDetails = null
    ): float {
        // Get package details if not provided
        if (!$packageDetails) {
            $packageDetails = $this->calculatePackageDetails($order);
        }

        // Get shipping zone
        $zone = $this->getShippingZone($order->shippingAddress);

        // Calculate base rate
        $baseRate = $this->calculateBaseRate($carrier, $method);

        // Apply weight-based pricing
        $weightRate = $this->calculateWeightBasedRate($method, $packageDetails['weight']);

        // Apply zone-based pricing
        $zoneRate = $this->calculateZoneBasedRate($method, $zone, $packageDetails);

        // Apply distance-based pricing if applicable
        $distanceRate = $this->calculateDistanceBasedRate($method, $order, $packageDetails);

        // Apply volume-based pricing
        $volumeRate = $this->calculateVolumeBasedRate($method, $packageDetails);

        // Calculate total before discounts
        $subtotal = $baseRate + $weightRate + $zoneRate + $distanceRate + $volumeRate;

        // Apply discounts
        $discountedRate = $this->applyDiscounts($subtotal, $order, $carrier, $method);

        // Apply surcharges
        $finalRate = $this->applySurcharges($discountedRate, $order, $carrier, $method, $packageDetails);

        // Ensure minimum rate
        $minimumRate = $this->getMinimumRate($carrier, $method);
        
        return max($finalRate, $minimumRate);
    }

    /**
     * Calculate base rate
     */
    protected function calculateBaseRate(ShippingCarrier $carrier, ShippingMethod $method): float
    {
        return $method->base_cost ?? $carrier->base_rate ?? 0;
    }

    /**
     * Calculate weight-based rate
     */
    protected function calculateWeightBasedRate(ShippingMethod $method, float $weight): float
    {
        $weightPricing = $method->weight_based_pricing ?? [];
        
        if (empty($weightPricing)) {
            // Use per kg rate from carrier
            return ($method->carrier->per_kg_rate ?? 0) * $weight;
        }

        // Find applicable weight tier
        foreach ($weightPricing as $tier) {
            if ($weight <= ($tier['max_weight'] ?? PHP_FLOAT_MAX)) {
                return $tier['rate'] * $weight;
            }
        }

        // Default to last tier rate
        $lastTier = end($weightPricing);
        return ($lastTier['rate'] ?? 0) * $weight;
    }

    /**
     * Calculate zone-based rate
     */
    protected function calculateZoneBasedRate(ShippingMethod $method, ?ShippingZone $zone, array $packageDetails): float
    {
        if (!$zone) {
            return 0;
        }

        $zonePricing = $method->zone_based_pricing ?? [];
        
        if (empty($zonePricing)) {
            return 0;
        }

        // Find zone-specific pricing
        $zoneRate = 0;
        foreach ($zonePricing as $zonePrice) {
            if ($zonePrice['zone_id'] == $zone->id) {
                $zoneRate = $zonePrice['base_rate'] ?? 0;
                
                // Add weight multiplier if exists
                if (isset($zonePrice['per_kg_rate'])) {
                    $zoneRate += $zonePrice['per_kg_rate'] * $packageDetails['weight'];
                }
                
                break;
            }
        }

        return $zoneRate;
    }

    /**
     * Calculate distance-based rate
     */
    protected function calculateDistanceBasedRate(ShippingMethod $method, Order $order, array $packageDetails): float
    {
        $perKmRate = $method->per_km_cost ?? 0;
        
        if ($perKmRate <= 0) {
            return 0;
        }

        // Calculate distance between origin and destination
        $distance = $this->calculateDistance($order->shippingAddress);
        
        return $perKmRate * $distance;
    }

    /**
     * Calculate volume-based rate
     */
    protected function calculateVolumeBasedRate(ShippingMethod $method, array $packageDetails): float
    {
        $settings = $method->settings ?? [];
        $volumeRate = $settings['per_cubic_cm_rate'] ?? 0;
        
        if ($volumeRate <= 0) {
            return 0;
        }

        $volume = $packageDetails['volume'] ?? 0;
        return $volumeRate * $volume;
    }

    /**
     * Apply discounts
     */
    protected function applyDiscounts(float $rate, Order $order, ShippingCarrier $carrier, ShippingMethod $method): float
    {
        $discountedRate = $rate;

        // Free shipping threshold
        if ($carrier->free_shipping_threshold > 0 && $order->subtotal >= $carrier->free_shipping_threshold) {
            return 0;
        }

        // Order value discount
        $orderValueDiscount = $this->calculateOrderValueDiscount($rate, $order);
        $discountedRate -= $orderValueDiscount;

        // Member discount
        $memberDiscount = $this->calculateMemberDiscount($rate, $order);
        $discountedRate -= $memberDiscount;

        // Promotional discount
        $promoDiscount = $this->calculatePromotionalDiscount($rate, $order, $carrier, $method);
        $discountedRate -= $promoDiscount;

        return max($discountedRate, 0);
    }

    /**
     * Apply surcharges
     */
    protected function applySurcharges(
        float $rate, 
        Order $order, 
        ShippingCarrier $carrier, 
        ShippingMethod $method, 
        array $packageDetails
    ): float {
        $surchargedRate = $rate;

        // COD surcharge
        if ($order->payment_method === 'cod' && $carrier->supports_cod) {
            $codSurcharge = $this->calculateCODSurcharge($order, $carrier);
            $surchargedRate += $codSurcharge;
        }

        // Express delivery surcharge
        if ($this->isExpressDelivery($method)) {
            $expressSurcharge = $this->calculateExpressSurcharge($rate, $method);
            $surchargedRate += $expressSurcharge;
        }

        // Oversized package surcharge
        $oversizeSurcharge = $this->calculateOversizeSurcharge($packageDetails, $carrier);
        $surchargedRate += $oversizeSurcharge;

        // Remote area surcharge
        $remoteAreaSurcharge = $this->calculateRemoteAreaSurcharge($order, $carrier);
        $surchargedRate += $remoteAreaSurcharge;

        // Holiday surcharge
        $holidaySurcharge = $this->calculateHolidaySurcharge($rate, $carrier);
        $surchargedRate += $holidaySurcharge;

        return $surchargedRate;
    }

    /**
     * Calculate package details from order
     */
    protected function calculatePackageDetails(Order $order): array
    {
        $totalWeight = 0;
        $totalVolume = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $quantity = $item->quantity;

            // Weight calculation
            if ($product->weight) {
                $totalWeight += $product->weight * $quantity;
            }

            // Dimension calculation
            if ($product->dimensions) {
                $dims = is_string($product->dimensions) 
                    ? json_decode($product->dimensions, true) 
                    : $product->dimensions;
                    
                if ($dims) {
                    $maxLength = max($maxLength, $dims['length'] ?? 0);
                    $maxWidth = max($maxWidth, $dims['width'] ?? 0);
                    $totalHeight += ($dims['height'] ?? 0) * $quantity;
                    $totalVolume += ($dims['length'] ?? 0) * ($dims['width'] ?? 0) * ($dims['height'] ?? 0) * $quantity;
                }
            }
        }

        // Add packaging weight (10% of product weight)
        $totalWeight = $totalWeight * 1.1;

        // Minimum package dimensions
        $length = max($maxLength, 10);
        $width = max($maxWidth, 10);
        $height = max($totalHeight, 5);

        return [
            'weight' => round($totalWeight, 2),
            'dimensions' => [
                'length' => round($length, 2),
                'width' => round($width, 2),
                'height' => round($height, 2)
            ],
            'volume' => round($totalVolume, 2)
        ];
    }

    /**
     * Get shipping zone for address
     */
    protected function getShippingZone($address): ?ShippingZone
    {
        return Cache::remember(
            "shipping_zone_{$address->country_id}_{$address->state_id}_{$address->city_id}",
            3600, // 1 hour
            function () use ($address) {
                return ShippingZone::whereHas('locations', function($query) use ($address) {
                    $query->where(function($q) use ($address) {
                        $q->where('country_id', $address->country_id)
                          ->where('state_id', $address->state_id)
                          ->where('city_id', $address->city_id);
                    })->orWhere(function($q) use ($address) {
                        $q->where('country_id', $address->country_id)
                          ->where('state_id', $address->state_id)
                          ->whereNull('city_id');
                    })->orWhere(function($q) use ($address) {
                        $q->where('country_id', $address->country_id)
                          ->whereNull('state_id')
                          ->whereNull('city_id');
                    });
                })->first();
            }
        );
    }

    /**
     * Calculate distance to destination
     */
    protected function calculateDistance($address): float
    {
        // For now, return a static distance based on state
        // In production, use Google Maps API or similar
        $originLat = config('shipping.origin.latitude', 19.0760);
        $originLng = config('shipping.origin.longitude', 72.8777);
        
        // Approximate coordinates for major Indian cities
        $cityCoordinates = [
            'Mumbai' => ['lat' => 19.0760, 'lng' => 72.8777],
            'Delhi' => ['lat' => 28.7041, 'lng' => 77.1025],
            'Bangalore' => ['lat' => 12.9716, 'lng' => 77.5946],
            'Chennai' => ['lat' => 13.0827, 'lng' => 80.2707],
            'Kolkata' => ['lat' => 22.5726, 'lng' => 88.3639],
            'Hyderabad' => ['lat' => 17.3850, 'lng' => 78.4867],
            'Pune' => ['lat' => 18.5204, 'lng' => 73.8567],
            'Ahmedabad' => ['lat' => 23.0225, 'lng' => 72.5714]
        ];

        $cityName = $address->city->name ?? $address->city ?? 'Mumbai';
        $destCoords = $cityCoordinates[$cityName] ?? $cityCoordinates['Mumbai'];

        return $this->haversineDistance(
            $originLat, $originLng,
            $destCoords['lat'], $destCoords['lng']
        );
    }

    /**
     * Calculate haversine distance between two points
     */
    protected function haversineDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Calculate order value discount
     */
    protected function calculateOrderValueDiscount(float $rate, Order $order): float
    {
        $orderTotal = $order->subtotal;
        
        if ($orderTotal >= 5000) {
            return $rate * 0.2; // 20% discount for orders above ₹5000
        } elseif ($orderTotal >= 2500) {
            return $rate * 0.1; // 10% discount for orders above ₹2500
        }

        return 0;
    }

    /**
     * Calculate member discount
     */
    protected function calculateMemberDiscount(float $rate, Order $order): float
    {
        $user = $order->user;
        
        // Premium member discount
        if ($user && $user->membership_type === 'premium') {
            return $rate * 0.15; // 15% discount for premium members
        }

        return 0;
    }

    /**
     * Calculate promotional discount
     */
    protected function calculatePromotionalDiscount(
        float $rate, 
        Order $order, 
        ShippingCarrier $carrier, 
        ShippingMethod $method
    ): float {
        // Check for active shipping promotions
        // This would integrate with your promotions system
        return 0;
    }

    /**
     * Calculate COD surcharge
     */
    protected function calculateCODSurcharge(Order $order, ShippingCarrier $carrier): float
    {
        $settings = $carrier->settings ?? [];
        $codRate = $settings['cod_rate'] ?? 0.02; // 2% of order value
        $maxCodCharge = $settings['max_cod_charge'] ?? 100;
        
        return min($order->total * $codRate, $maxCodCharge);
    }

    /**
     * Check if method is express delivery
     */
    protected function isExpressDelivery(ShippingMethod $method): bool
    {
        $expressKeywords = ['express', 'fast', 'priority', 'urgent', 'next day', 'same day'];
        $methodName = strtolower($method->name);
        
        foreach ($expressKeywords as $keyword) {
            if (str_contains($methodName, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate express surcharge
     */
    protected function calculateExpressSurcharge(float $rate, ShippingMethod $method): float
    {
        $settings = $method->settings ?? [];
        return $settings['express_surcharge'] ?? ($rate * 0.3); // 30% surcharge for express
    }

    /**
     * Calculate oversize surcharge
     */
    protected function calculateOversizeSurcharge(array $packageDetails, ShippingCarrier $carrier): float
    {
        $dimensions = $packageDetails['dimensions'];
        $weight = $packageDetails['weight'];
        
        $settings = $carrier->settings ?? [];
        $maxWeight = $settings['max_weight'] ?? 30;
        $maxDimension = $settings['max_dimension'] ?? 120;
        
        $surcharge = 0;
        
        // Weight surcharge
        if ($weight > $maxWeight) {
            $surcharge += ($weight - $maxWeight) * ($settings['overweight_rate'] ?? 50);
        }
        
        // Dimension surcharge
        $maxPackageDimension = max($dimensions['length'], $dimensions['width'], $dimensions['height']);
        if ($maxPackageDimension > $maxDimension) {
            $surcharge += $settings['oversize_surcharge'] ?? 200;
        }
        
        return $surcharge;
    }

    /**
     * Calculate remote area surcharge
     */
    protected function calculateRemoteAreaSurcharge(Order $order, ShippingCarrier $carrier): float
    {
        // Check if delivery location is in remote area
        $address = $order->shippingAddress;
        $remoteAreas = $carrier->settings['remote_areas'] ?? [];
        
        $isRemote = in_array($address->postal_code, $remoteAreas) ||
                   in_array($address->city->name ?? $address->city, $remoteAreas);
        
        if ($isRemote) {
            return $carrier->settings['remote_area_surcharge'] ?? 150;
        }
        
        return 0;
    }

    /**
     * Calculate holiday surcharge
     */
    protected function calculateHolidaySurcharge(float $rate, ShippingCarrier $carrier): float
    {
        // Check if current date is a holiday
        $holidays = [
            '01-01', // New Year
            '01-26', // Republic Day
            '08-15', // Independence Day
            '10-02', // Gandhi Jayanti
            // Add more holidays as needed
        ];
        
        $today = now()->format('m-d');
        
        if (in_array($today, $holidays)) {
            return $carrier->settings['holiday_surcharge'] ?? ($rate * 0.1);
        }
        
        return 0;
    }

    /**
     * Get minimum rate
     */
    protected function getMinimumRate(ShippingCarrier $carrier, ShippingMethod $method): float
    {
        return $method->settings['minimum_rate'] ?? $carrier->settings['minimum_rate'] ?? 30;
    }

    /**
     * Calculate rates for multiple carriers
     */
    public function calculateRatesForAllCarriers(Order $order): array
    {
        $carriers = ShippingCarrier::active()->with('methods')->get();
        $rates = [];

        foreach ($carriers as $carrier) {
            foreach ($carrier->methods as $method) {
                if (!$method->is_active) continue;

                try {
                    $rate = $this->calculateRate($order, $carrier, $method);
                    $rates[] = [
                        'carrier' => $carrier,
                        'method' => $method,
                        'rate' => $rate,
                        'delivery_time' => $method->delivery_time,
                        'supports_cod' => $carrier->supports_cod
                    ];
                } catch (Exception $e) {
                    // Log error and continue
                    \Log::warning('Rate calculation failed', [
                        'carrier' => $carrier->name,
                        'method' => $method->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Sort by rate
        usort($rates, function($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });

        return $rates;
    }

    /**
     * Get rate breakdown for transparency
     */
    public function getRateBreakdown(Order $order, ShippingCarrier $carrier, ShippingMethod $method): array
    {
        $packageDetails = $this->calculatePackageDetails($order);
        $zone = $this->getShippingZone($order->shippingAddress);

        $breakdown = [
            'base_rate' => $this->calculateBaseRate($carrier, $method),
            'weight_rate' => $this->calculateWeightBasedRate($method, $packageDetails['weight']),
            'zone_rate' => $this->calculateZoneBasedRate($method, $zone, $packageDetails),
            'distance_rate' => $this->calculateDistanceBasedRate($method, $order, $packageDetails),
            'volume_rate' => $this->calculateVolumeBasedRate($method, $packageDetails),
            'cod_surcharge' => $order->payment_method === 'cod' ? $this->calculateCODSurcharge($order, $carrier) : 0,
            'express_surcharge' => $this->isExpressDelivery($method) ? $this->calculateExpressSurcharge(0, $method) : 0,
            'oversize_surcharge' => $this->calculateOversizeSurcharge($packageDetails, $carrier),
            'remote_area_surcharge' => $this->calculateRemoteAreaSurcharge($order, $carrier),
            'holiday_surcharge' => $this->calculateHolidaySurcharge(0, $carrier)
        ];

        $subtotal = array_sum($breakdown);
        $discounts = $this->calculateOrderValueDiscount($subtotal, $order) + 
                    $this->calculateMemberDiscount($subtotal, $order);

        $breakdown['subtotal'] = $subtotal;
        $breakdown['discounts'] = -$discounts;
        $breakdown['total'] = max($subtotal - $discounts, $this->getMinimumRate($carrier, $method));

        return $breakdown;
    }
}