<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        // Check if data already exists
        if (ShippingCarrier::where('code', 'shiprocket')->exists()) {
            $this->command->info('Shipping data already exists, skipping seeding.');
            return;
        }

        // Create ShipRocket carrier
        $shipRocket = ShippingCarrier::create([
            'name' => 'ShipRocket',
            'code' => 'shiprocket',
            'api_endpoint' => 'https://apiv2.shiprocket.co/v1/external',
            'api_key' => config('services.shiprocket.token'),
            'api_secret' => config('services.shiprocket.email'),
            'is_active' => true,
            'supports_cod' => true,
            'supports_express' => true,
            'base_rate' => 50.00,
            'per_kg_rate' => 15.00,
            'free_shipping_threshold' => 500.00,
            'configuration' => json_encode([
                'email' => config('services.shiprocket.email'),
                'password' => config('services.shiprocket.password'),
                'channel_id' => config('services.shiprocket.channel_id'),
                'length_unit' => 'cm',
                'weight_unit' => 'kg',
                'cod_enabled' => true,
                'insurance_enabled' => true
            ])
        ]);

        // ShipRocket shipping methods
        $methods = [
            [
                'name' => 'Standard Delivery',
                'code' => 'standard',
                'description' => 'Standard delivery in 3-5 business days',
                'delivery_time' => '3-5 business days',
                'base_cost' => 50.00,
                'per_km_cost' => 2.00,
                'is_active' => true,
                'settings' => json_encode([
                    'pickup_location' => 'Primary',
                    'cod_charges' => 25.00,
                    'insurance_rate' => 0.005
                ])
            ],
            [
                'name' => 'Express Delivery',
                'code' => 'express',
                'description' => 'Express delivery in 1-2 business days',
                'delivery_time' => '1-2 business days',
                'base_cost' => 100.00,
                'per_km_cost' => 5.00,
                'is_active' => true,
                'settings' => json_encode([
                    'pickup_location' => 'Primary',
                    'cod_charges' => 35.00,
                    'insurance_rate' => 0.005
                ])
            ],
            [
                'name' => 'Same Day Delivery',
                'code' => 'same_day',
                'description' => 'Same day delivery (Metro cities only)',
                'delivery_time' => '4-8 hours',
                'base_cost' => 200.00,
                'per_km_cost' => 10.00,
                'is_active' => true,
                'settings' => json_encode([
                    'pickup_location' => 'Primary',
                    'cod_charges' => 50.00,
                    'insurance_rate' => 0.005,
                    'available_cities' => ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune']
                ])
            ]
        ];

        foreach ($methods as $method) {
            $method['carrier_id'] = $shipRocket->id;
            ShippingMethod::create($method);
        }

        // Create a local pickup option
        $localCarrier = ShippingCarrier::create([
            'name' => 'Local Pickup',
            'code' => 'local_pickup',
            'api_endpoint' => null,
            'api_key' => null,
            'api_secret' => null,
            'is_active' => true,
            'supports_cod' => false,
            'supports_express' => false,
            'base_rate' => 0.00,
            'per_kg_rate' => 0.00,
            'free_shipping_threshold' => 0.00,
            'configuration' => json_encode([
                'pickup_address' => 'Store Address Here',
                'pickup_hours' => 'Mon-Sat: 10 AM - 7 PM, Sun: 11 AM - 5 PM',
                'advance_notice' => 'Please call 24 hours in advance'
            ])
        ]);

        ShippingMethod::create([
            'carrier_id' => $localCarrier->id,
            'name' => 'Store Pickup',
            'code' => 'store_pickup',
            'description' => 'Pickup from store location',
            'delivery_time' => '1-2 hours',
            'base_cost' => 0.00,
            'per_km_cost' => 0.00,
            'is_active' => true,
            'settings' => json_encode([
                'pickup_required' => true,
                'id_verification' => true
            ])
        ]);

        $this->command->info('Shipping carriers and methods seeded successfully!');
    }
}
