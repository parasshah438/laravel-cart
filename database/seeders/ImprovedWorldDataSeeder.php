<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\PostalCode;

class ImprovedWorldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php -d memory_limit=2G artisan db:seed --class=ImprovedWorldDataSeeder
     */
    public function run(): void
    {
        $this->command->info('🌍 Starting World Data Import...');
        
        // Import in order
        $this->seedCountries();
        $this->seedStates();
        $this->seedCities();
        $this->seedPostalCodes();
        
        $this->command->info('✅ World Data Import Completed!');
    }

    /**
     * Import countries from multiple sources for reliability
     */
    private function seedCountries()
    {
        $this->command->info('📍 Importing Countries...');

        // Primary source
        $primaryUrl = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/countries.json';
        
        // Backup source
        $backupUrl = 'https://raw.githubusercontent.com/hejnal/world-countries/master/countries.json';
        
        $countries = $this->fetchJsonData($primaryUrl, $backupUrl);
        
        if (!$countries) {
            $this->command->error('❌ Failed to fetch countries data!');
            return;
        }

        $batch = [];
        foreach ($countries as $country) {
            $batch[] = [
                'code' => $country['iso2'] ?? $country['code'] ?? null,
                'name' => $country['name'],
                'iso3' => $country['iso3'] ?? null,
                'phone_code' => $country['phonecode'] ?? $country['phone'] ?? null,
                'currency' => $country['currency'] ?? null,
                'is_active' => true,
                'sort_order' => $this->getCountryPriority($country['iso2'] ?? $country['code']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks for performance
        collect($batch)->chunk(100)->each(function ($chunk) {
            Country::insertOrIgnore($chunk->toArray());
        });

        $this->command->info("✅ Imported " . count($batch) . " countries");
    }

    /**
     * Import states with improved mapping
     */
    private function seedStates()
    {
        $this->command->info('🏛️ Importing States...');

        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/states.json';
        $states = $this->fetchJsonData($url);
        
        if (!$states) {
            $this->command->error('❌ Failed to fetch states data!');
            return;
        }

        // Pre-load countries for faster lookup
        $countries = Country::pluck('id', 'code')->toArray();
        
        $batch = [];
        $processed = 0;
        
        foreach ($states as $state) {
            $countryId = $countries[$state['country_code']] ?? null;
            
            if ($countryId) {
                $batch[] = [
                    'name' => $state['name'],
                    'code' => $state['state_code'] ?? $state['iso2'] ?? null,
                    'country_id' => $countryId,
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $processed++;
                
                // Insert in batches
                if (count($batch) >= 1000) {
                    State::insertOrIgnore($batch);
                    $batch = [];
                    $this->command->info("Processed {$processed} states...");
                }
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            State::insertOrIgnore($batch);
        }

        $this->command->info("✅ Imported {$processed} states");
    }

    /**
     * Import cities with optimized performance
     */
    private function seedCities()
    {
        $this->command->info('🏙️ Importing Cities...');

        // Multiple sources for cities
        $sources = [
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/cities.json',
            'https://raw.githubusercontent.com/lutangar/cities.json/master/cities.json'
        ];

        foreach ($sources as $url) {
            $this->command->info("Trying source: {$url}");
            
            try {
                $cities = $this->fetchJsonData($url);
                if ($cities) {
                    $this->processCities($cities);
                    break; // Success, exit loop
                }
            } catch (\Exception $e) {
                $this->command->warn("Source failed: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Process cities data with optimized queries
     */
    private function processCities($cities)
    {
        // Focus on major countries first
        $priorityCountries = ['IN', 'US', 'GB', 'CA', 'AU', 'DE', 'FR'];
        
        // Pre-load all countries and states for faster lookup
        $countries = Country::pluck('id', 'code')->toArray();
        $states = State::with('country')
            ->get()
            ->groupBy('country_id')
            ->mapWithKeys(function ($stateGroup, $countryId) {
                return [
                    $countryId => $stateGroup->pluck('id', 'name')->toArray()
                ];
            })->toArray();

        foreach ($priorityCountries as $countryCode) {
            $countryId = $countries[$countryCode] ?? null;
            if (!$countryId) continue;

            $this->command->info("Processing cities for {$countryCode}...");

            $countryCities = array_filter($cities, function($city) use ($countryCode) {
                return ($city['country_code'] ?? '') === $countryCode;
            });

            $this->insertCitiesForCountry($countryCities, $countryId, $states[$countryId] ?? []);
        }
    }

    /**
     * Insert cities for a specific country
     */
    private function insertCitiesForCountry($cities, $countryId, $countryStates)
    {
        $batch = [];
        $processed = 0;

        foreach ($cities as $city) {
            $stateName = $city['state_name'] ?? '';
            $stateId = $countryStates[$stateName] ?? null;

            if ($stateId) {
                $batch[] = [
                    'name' => $city['name'],
                    'state_id' => $stateId,
                    'country_id' => $countryId,
                    'latitude' => $city['latitude'] ?? null,
                    'longitude' => $city['longitude'] ?? null,
                    'is_major' => $this->isMajorCity($city['name']),
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $processed++;

                if (count($batch) >= 500) {
                    City::insertOrIgnore($batch);
                    $batch = [];
                    $this->command->info("Inserted {$processed} cities...");
                }
            }
        }

        if (!empty($batch)) {
            City::insertOrIgnore($batch);
        }

        $this->command->info("✅ Processed {$processed} cities");
    }

    /**
     * Enhanced postal code seeding with multiple sources
     */
    private function seedPostalCodes()
    {
        $this->command->info('📮 Importing Postal Codes...');

        // India postal codes (high priority)
        $this->seedIndianPostalCodes();
        
        // US postal codes
        $this->seedUSPostalCodes();
        
        // Other countries from GeoNames
        $this->seedGeoNamesPostalCodes();
    }

    /**
     * Import Indian postal codes from multiple sources
     */
    private function seedIndianPostalCodes()
    {
        $this->command->info('🇮🇳 Importing Indian Postal Codes...');

        $sources = [
            // Primary source - GitHub data
            'https://raw.githubusercontent.com/datameet/indian-pincodes/master/data/pincodes.json',
            // Backup source - Local file
            database_path('data/postal/IN.txt'),
            // Alternative API source
            'https://api.postalpincode.in/postoffice/Pincode'
        ];

        foreach ($sources as $source) {
            if ($this->processIndianPostalSource($source)) {
                break; // Success, exit loop
            }
        }
    }

    /**
     * Process Indian postal codes from a source
     */
    private function processIndianPostalSource($source)
    {
        try {
            if (str_starts_with($source, 'http')) {
                // API source
                $data = $this->fetchJsonData($source);
                if ($data) {
                    return $this->insertIndianPostalCodes($data);
                }
            } else {
                // File source
                if (File::exists($source)) {
                    return $this->processIndianPostalFile($source);
                }
            }
        } catch (\Exception $e) {
            $this->command->warn("Indian postal source failed: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Import US postal codes
     */
    private function seedUSPostalCodes()
    {
        $this->command->info('🇺🇸 Importing US Postal Codes...');

        $url = 'https://raw.githubusercontent.com/scpike/us-state-county-zip/master/geo-data.json';
        $data = $this->fetchJsonData($url);

        if ($data) {
            $this->processUSPostalCodes($data);
        }
    }

    /**
     * Import postal codes from GeoNames
     */
    private function seedGeoNamesPostalCodes()
    {
        $this->command->info('🌐 Importing GeoNames Postal Codes...');

        // Download postal codes for major countries
        $countries = ['GB', 'CA', 'AU', 'DE', 'FR'];
        
        foreach ($countries as $countryCode) {
            $url = "http://download.geonames.org/export/zip/{$countryCode}.zip";
            $this->processGeoNamesCountry($countryCode, $url);
        }
    }

    /**
     * Helper method to fetch JSON data with retry logic
     */
    private function fetchJsonData($primaryUrl, $backupUrl = null, $timeout = 120)
    {
        $urls = array_filter([$primaryUrl, $backupUrl]);

        foreach ($urls as $url) {
            try {
                $this->command->info("Fetching: {$url}");

                $response = Http::withOptions([
                    'timeout' => $timeout,
                    'connect_timeout' => 60,
                    'verify' => false,
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data) {
                        $this->command->info("✅ Successfully fetched data from: {$url}");
                        return $data;
                    }
                }
            } catch (\Exception $e) {
                $this->command->warn("Failed to fetch from {$url}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Get country priority for sorting
     */
    private function getCountryPriority($countryCode)
    {
        $priorities = [
            'IN' => 1,  // India
            'US' => 2,  // USA
            'GB' => 3,  // UK
            'CA' => 4,  // Canada
            'AU' => 5,  // Australia
            'DE' => 6,  // Germany
            'FR' => 7,  // France
            'JP' => 8,  // Japan
            'CN' => 9,  // China
            'BR' => 10, // Brazil
        ];

        return $priorities[$countryCode] ?? 999;
    }

    /**
     * Check if city is major (for faster queries)
     */
    private function isMajorCity($cityName)
    {
        $majorCities = [
            // India
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune', 'Ahmedabad',
            // US
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego',
            // UK
            'London', 'Birmingham', 'Liverpool', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Edinburgh',
            // Global
            'Tokyo', 'Paris', 'Berlin', 'Sydney', 'Toronto', 'Vancouver'
        ];

        return in_array($cityName, $majorCities);
    }

    /**
     * Process Indian postal file (existing logic)
     */
    private function processIndianPostalFile($path)
    {
        // Your existing postal code processing logic here
        // This is the same as your current seedPostalCodes() method
        $country = Country::where('code', 'IN')->first();
        if (!$country) return false;

        // ... rest of your existing logic
        return true;
    }
}