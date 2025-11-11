<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;

class SeedShippingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:shipping-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed shipping carriers and methods data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚢 Seeding shipping carriers and methods...');

        // Seed Shipping Carriers
        $this->seedCarriers();
        
        // Seed Shipping Methods
        $this->seedMethods();

        $this->info('✅ Shipping data seeded successfully!');
    }

    protected function seedCarriers()
    {
        $carriers = [
            [
                'name' => 'Delhivery',
                'code' => 'delhivery',
                'api_endpoint' => 'https://track.delhivery.com/api/v1',
                'tracking_url_template' => 'https://www.delhivery.com/track/package/{tracking_number}',
                'is_active' => true,
                'supports_cod' => true,
                'supports_express' => true,
                'base_rate' => 50.00,
                'per_kg_rate' => 15.00,
                'configuration' => [
                    'max_weight' => 50,
                    'delivery_days' => '2-4',
                    'coverage' => 'Pan India',
                    'api_key_required' => true,
                    'supported_services' => ['Standard', 'Express', 'Surface']
                ]
            ],
            [
                'name' => 'Blue Dart',
                'code' => 'bluedart',
                'api_endpoint' => 'https://api.bluedart.com/v1',
                'tracking_url_template' => 'https://www.bluedart.com/web/guest/trackdartresult?trackFor={tracking_number}',
                'is_active' => true,
                'supports_cod' => true,
                'supports_express' => true,
                'base_rate' => 80.00,
                'per_kg_rate' => 20.00,
                'configuration' => [
                    'max_weight' => 50,
                    'delivery_days' => '1-3',
                    'coverage' => 'Pan India',
                    'premium_service' => true,
                    'api_key_required' => true
                ]
            ],
            [
                'name' => 'DTDC',
                'code' => 'dtdc',
                'api_endpoint' => 'https://api.dtdc.in/v1',
                'tracking_url_template' => 'https://www.dtdc.in/tracking/{tracking_number}',
                'is_active' => true,
                'supports_cod' => true,
                'supports_express' => false,
                'base_rate' => 40.00,
                'per_kg_rate' => 12.00,
                'configuration' => [
                    'max_weight' => 50,
                    'delivery_days' => '3-5',
                    'coverage' => 'Pan India',
                    'economical' => true
                ]
            ],
            [
                'name' => 'Local Courier',
                'code' => 'local_courier',
                'tracking_url_template' => 'https://local-courier.com/track/{tracking_number}',
                'is_active' => true,
                'supports_cod' => true,
                'supports_express' => false,
                'base_rate' => 50.00,
                'per_kg_rate' => 10.00,
                'configuration' => [
                    'max_weight' => 50,
                    'delivery_days' => '3-7',
                    'coverage' => 'Local Area',
                    'manual_tracking' => true
                ]
            ]
        ];

        foreach ($carriers as $carrierData) {
            $carrier = ShippingCarrier::updateOrCreate(
                ['code' => $carrierData['code']],
                $carrierData
            );
            $this->info("✓ Carrier: {$carrier->name}");
        }
    }

    protected function seedMethods()
    {
        // Get carriers
        $localCourier = ShippingCarrier::where('code', 'local_courier')->first();
        $delhivery = ShippingCarrier::where('code', 'delhivery')->first();
        $bluedart = ShippingCarrier::where('code', 'bluedart')->first();
        $dtdc = ShippingCarrier::where('code', 'dtdc')->first();

        if (!$localCourier) {
            $this->error('Local courier not found. Please run carriers seeding first.');
            return;
        }

        $methods = [
            // Local Courier Methods
            [
                'carrier_id' => $localCourier->id,
                'name' => 'Standard Delivery',
                'code' => 'standard',
                'description' => 'Standard delivery within 3-5 business days',
                'delivery_time' => '3-5 business days',
                'is_active' => true,
                'base_cost' => 50.00,
                'per_km_cost' => 2.00,
                'settings' => [
                    'cod_available' => true,
                    'tracking_available' => true,
                    'insurance_available' => false,
                    'max_weight' => 50,
                    'estimated_days' => 5
                ]
            ],
            [
                'carrier_id' => $localCourier->id,
                'name' => 'Express Delivery',
                'code' => 'express',
                'description' => 'Express delivery within 1-2 business days',
                'delivery_time' => '1-2 business days',
                'is_active' => true,
                'base_cost' => 150.00,
                'per_km_cost' => 5.00,
                'settings' => [
                    'cod_available' => true,
                    'tracking_available' => true,
                    'insurance_available' => true,
                    'max_weight' => 25,
                    'estimated_days' => 2
                ]
            ],
            [
                'carrier_id' => $localCourier->id,
                'name' => 'Economy Delivery',
                'code' => 'economy',
                'description' => 'Economy delivery within 5-7 business days',
                'delivery_time' => '5-7 business days',
                'is_active' => true,
                'base_cost' => 30.00,
                'per_km_cost' => 1.00,
                'settings' => [
                    'cod_available' => true,
                    'tracking_available' => true,
                    'insurance_available' => false,
                    'max_weight' => 50,
                    'estimated_days' => 7
                ]
            ],
            [
                'carrier_id' => $localCourier->id,
                'name' => 'Free Delivery',
                'code' => 'free',
                'description' => 'Free delivery for orders above ₹500',
                'delivery_time' => '3-5 business days',
                'is_active' => true,
                'base_cost' => 0.00,
                'per_km_cost' => 0.00,
                'settings' => [
                    'cod_available' => true,
                    'tracking_available' => true,
                    'insurance_available' => false,
                    'max_weight' => 25,
                    'minimum_order_amount' => 500,
                    'estimated_days' => 5
                ]
            ]
        ];

        foreach ($methods as $methodData) {
            $method = ShippingMethod::updateOrCreate(
                [
                    'carrier_id' => $methodData['carrier_id'],
                    'code' => $methodData['code']
                ],
                $methodData
            );
            $this->info("✓ Method: {$method->name} ({$method->carrier->name})");
        }
    }
}
