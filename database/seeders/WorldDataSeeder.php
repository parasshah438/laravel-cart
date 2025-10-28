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

class WorldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php -d memory_limit=1G artisan db:seed --class=WorldDataSeeder
     */
    public function run(): void
    {
        $this->seedCountries();
        $this->seedStates();
        $this->seedCities();
        $this->seedPostalCodes();
    }

    private function seedCountries()
    {
        // Download countries JSON
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/refs/heads/master/json/countries.json';
        
        $response = Http::withOptions([
            'timeout' => 120, // Increase timeout for large data
            'connect_timeout' => 60,
            'verify' => false, // Disable SSL verification if necessary
            ])->get($url);
        
        if ($response->successful()) {
            $countries = $response->json();
            foreach ($countries as $country) {
                Country::updateOrCreate(
                    ['code' => $country['iso2']],
                    [
                        'name' => $country['name'],
                        'iso3' => $country['iso3'],
                        'phone_code' => $country['phonecode'],
                        'currency' => $country['currency'],
                        'is_active' => true,
                        'sort_order' => $this->getCountryPriority($country['iso2']),
                    ]
                );
            }
        }
        $this->command->info('Countries imported successfully!');
    }

    private function seedStates()
    {
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/refs/heads/master/json/states.json';
        $response = Http::withOptions([
            'timeout' => 120,
            'connect_timeout' => 60,
            'verify' => false,
        ])->get($url);
        
        if ($response->successful()) {
            $states = $response->json();
            
            foreach ($states as $state) {

                $country = Country::where('code', $state['country_code'])->first();
                
                if ($country) {
                    State::updateOrCreate(
                        [
                            'name' => $state['name'],
                            'country_id' => $country->id,
                        ],
                        [
                            'code' => $state['country_code'], // ✅ Now always has a value
                            'is_active' => true,
                            'sort_order' => 0,
                        ]
                    );
                }
            }
        }
        
        $this->command->info('States imported successfully!');
    }

    private function seedCities()
    {
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/cities.json.gz';

        $this->command->info('Downloading and importing Indian cities...');

        try {
            $response = Http::withOptions([
                'timeout' => 180,
                'connect_timeout' => 60,
                'verify' => false,
            ])->get($url);

            if ($response->successful()) {
                // Decompress gzipped content
                $gzData = $response->body();
                $jsonData = gzdecode($gzData);

                if ($jsonData === false) {
                    $this->command->error('Failed to decompress city data!');
                    return;
                }

                $cities = json_decode($jsonData, true);
                if (!$cities) {
                    $this->command->error('Invalid JSON structure!');
                    return;
                }

                // ✅ Filter for India only
                $indianCities = array_filter($cities, fn($city) => $city['country_code'] === 'IN');

                $this->command->info('Found ' . count($indianCities) . ' Indian cities.');

                $batchSize = 500;
                $batch = [];

                foreach ($indianCities as $city) {
                    $state = State::where('name', $city['state_name'])
                        ->where('country_id', 100) // hardcoded India
                        ->first();

                    if ($state) {
                        $batch[] = [
                            'name' => $city['name'],
                            'state_id' => $state->id,
                            'country_id' => 100, // fixed India ID
                            'is_major' => false,
                            'postal_code_pattern' => false,
                            'is_active' => true,
                            'sort_order' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Insert in batches for performance
                        if (count($batch) >= $batchSize) {
                            City::insert($batch);
                            $batch = [];
                        }
                    }
                }

                // Insert remaining
                if (!empty($batch)) {
                    City::insert($batch);
                }

                $this->command->info('Indian cities imported successfully!');
            } else {
                $this->command->error('Failed to fetch city data from URL.');
            }
        } catch (\Exception $e) {
            $this->command->error('Error importing cities: ' . $e->getMessage());
        }
    }

    private function getCountryPriority($countryCode)
    {
        $priorities = [
            'IN' => 1,  // India first
            'US' => 2,  // USA second  
            'GB' => 3,  // UK third
            'CA' => 4,  // Canada fourth
            'AU' => 5,  // Australia fifth
        ];
        
        return $priorities[$countryCode] ?? 999; // Default priority for other countries
    }

    private function seedPostalCodes()
    {
        $this->command->info('📦 Seeding Indian Postal Codes...');

        $url = 'https://raw.githubusercontent.com/deep5050/indian-pincodes-database/master/data.json';

        // Fetch data from GitHub
        $response = Http::withOptions([
            'timeout' => 180,
            'connect_timeout' => 60,
            'verify' => false,
        ])->get($url);

        if (!$response->successful()) {
            $this->command->error("❌ Failed to fetch data. HTTP Status: {$response->status()}");
            return;
        }

        // Clean BOM & Decode JSON
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $response->body());
        $json = json_decode($body, true);

        if (!isset($json['Sheet1'])) {
            $this->command->error('❌ Invalid JSON format.');
            return;
        }

        $rows = $json['Sheet1'];
        $this->command->info('✅ Loaded ' . count($rows) . ' postal records.');

        // Preload states & cities for faster lookup
        $countryId = 100; // India ID
        $states = State::where('country_id', $countryId)->pluck('id', 'name')->toArray();
        $cities = City::where('country_id', $countryId)->pluck('id', 'name')->toArray();

        $batchData = [];
        $inserted = 0;

        foreach ($rows as $row) {
            $pincode = trim($row['Pincode'] ?? '');
            $area = trim($row['PostOfficeName'] ?? '');
            $stateName = trim($row['State'] ?? '');
            $cityName = trim($row['City'] ?? '');

            if (!$pincode || !$stateName || !$cityName) continue;

            $stateId = $states[$stateName] ?? null;
            $cityId = $cities[$cityName] ?? null;

            if (!$stateId || !$cityId) continue;

            $batchData[] = [
                'code'        => $pincode,
                'area'        => $area,
                'state_id'    => $stateId,
                'city_id'     => $cityId,
                'country_id'  => $countryId,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Insert in chunks to avoid memory issues
            if (count($batchData) >= 1000) {
                PostalCode::upsert($batchData, ['code', 'country_id'], ['area', 'state_id', 'city_id', 'updated_at']);
                $inserted += count($batchData);
                $batchData = [];
                $this->command->info("Inserted {$inserted} records...");
            }
        }

        // Insert remaining data
        if (!empty($batchData)) {
            PostalCode::upsert($batchData, ['code', 'country_id'], ['area', 'state_id', 'city_id', 'updated_at']);
            $inserted += count($batchData);
        }

        $this->command->info("🎉 Completed seeding {$inserted} postal codes for India!");
    }
}
