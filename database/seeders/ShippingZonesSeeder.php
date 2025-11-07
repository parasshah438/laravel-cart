<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingZone;
use App\Models\ShippingZoneLocation;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class ShippingZonesSeeder extends Seeder
{
    public function run(): void
    {
        // Check if zones already exist
        if (ShippingZone::exists()) {
            $this->command->info('Shipping zones already exist, skipping seeding.');
            return;
        }

        // Get India country (assuming it exists)
        $india = Country::where('name', 'India')->orWhere('code', 'IN')->first();
        
        if (!$india) {
            $this->command->warn('India country not found in database. Please ensure countries are seeded first.');
            return;
        }

        // Zone 1: Metropolitan Cities (Tier 1)
        $metroZone = ShippingZone::create([
            'name' => 'Metro Cities (Tier 1)',
            'description' => 'Major metropolitan cities with fast delivery',
            'is_active' => true,
            'base_rate' => 50.00,
            'per_kg_rate' => 15.00,
            'free_shipping_threshold' => 500.00,
            'settings' => [
                'delivery_time' => '1-2 business days',
                'same_day_available' => true,
                'express_available' => true,
                'cod_available' => true
            ]
        ]);

        // Add metro cities to the zone
        $metroCities = ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune', 'Ahmedabad'];
        foreach ($metroCities as $cityName) {
            $city = City::where('name', 'like', "%{$cityName}%")->first();
            if ($city) {
                ShippingZoneLocation::create([
                    'zone_id' => $metroZone->id,
                    'country_id' => $india->id,
                    'state_id' => $city->state_id,
                    'city_id' => $city->id,
                    'additional_rate' => 0.00,
                    'is_active' => true
                ]);
            }
        }

        // Zone 2: Tier 2 Cities
        $tier2Zone = ShippingZone::create([
            'name' => 'Tier 2 Cities',
            'description' => 'Secondary cities with standard delivery',
            'is_active' => true,
            'base_rate' => 75.00,
            'per_kg_rate' => 20.00,
            'free_shipping_threshold' => 750.00,
            'settings' => [
                'delivery_time' => '2-4 business days',
                'same_day_available' => false,
                'express_available' => true,
                'cod_available' => true
            ]
        ]);

        // Add some tier 2 cities
        $tier2Cities = ['Jaipur', 'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Bhopal', 'Visakhapatnam', 'Patna'];
        foreach ($tier2Cities as $cityName) {
            $city = City::where('name', 'like', "%{$cityName}%")->first();
            if ($city) {
                ShippingZoneLocation::create([
                    'zone_id' => $tier2Zone->id,
                    'country_id' => $india->id,
                    'state_id' => $city->state_id,
                    'city_id' => $city->id,
                    'additional_rate' => 10.00,
                    'is_active' => true
                ]);
            }
        }

        // Zone 3: Rest of India
        $restOfIndiaZone = ShippingZone::create([
            'name' => 'Rest of India',
            'description' => 'All other locations in India',
            'is_active' => true,
            'base_rate' => 100.00,
            'per_kg_rate' => 25.00,
            'free_shipping_threshold' => 1000.00,
            'settings' => [
                'delivery_time' => '3-7 business days',
                'same_day_available' => false,
                'express_available' => false,
                'cod_available' => true
            ]
        ]);

        // Add all of India as a fallback
        ShippingZoneLocation::create([
            'zone_id' => $restOfIndiaZone->id,
            'country_id' => $india->id,
            'additional_rate' => 20.00,
            'is_active' => true
        ]);

        // Zone 4: Remote Areas (by postal code ranges)
        $remoteZone = ShippingZone::create([
            'name' => 'Remote Areas',
            'description' => 'Remote and difficult to reach areas',
            'is_active' => true,
            'base_rate' => 150.00,
            'per_kg_rate' => 35.00,
            'free_shipping_threshold' => 1500.00,
            'settings' => [
                'delivery_time' => '5-10 business days',
                'same_day_available' => false,
                'express_available' => false,
                'cod_available' => false
            ]
        ]);

        // Add some remote postal code ranges (example)
        $remotePostalRanges = [
            ['start' => '180001', 'end' => '194301'], // Kashmir
            ['start' => '796001', 'end' => '798612'], // Mizoram
            ['start' => '790001', 'end' => '792123'], // Meghalaya
            ['start' => '797001', 'end' => '798615'], // Manipur
            ['start' => '793001', 'end' => '794115'], // Tripura
        ];

        foreach ($remotePostalRanges as $range) {
            ShippingZoneLocation::create([
                'zone_id' => $remoteZone->id,
                'country_id' => $india->id,
                'postal_code_range_start' => $range['start'],
                'postal_code_range_end' => $range['end'],
                'additional_rate' => 50.00,
                'is_active' => true
            ]);
        }

        // Zone 5: International (if needed)
        $internationalZone = ShippingZone::create([
            'name' => 'International',
            'description' => 'International shipping (currently not supported)',
            'is_active' => false,
            'base_rate' => 500.00,
            'per_kg_rate' => 100.00,
            'free_shipping_threshold' => 0.00,
            'settings' => [
                'delivery_time' => '7-21 business days',
                'same_day_available' => false,
                'express_available' => false,
                'cod_available' => false,
                'customs_required' => true
            ]
        ]);

        $this->command->info('Shipping zones and locations seeded successfully!');
        $this->command->info('Created zones:');
        $this->command->info('- Metro Cities (Tier 1): ₹50 base + ₹15/kg');
        $this->command->info('- Tier 2 Cities: ₹75 base + ₹20/kg');
        $this->command->info('- Rest of India: ₹100 base + ₹25/kg');
        $this->command->info('- Remote Areas: ₹150 base + ₹35/kg');
        $this->command->info('- International: ₹500 base + ₹100/kg (disabled)');
    }
}
