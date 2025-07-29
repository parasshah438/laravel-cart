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
     */
    public function run(): void
    {
        //$this->seedCountries();
        //$this->seedStates();
        //$this->seedCities();
        $this->seedPostalCodes();

    }

    private function seedCountries()
    {
        // Download countries JSON
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/countries.json';
        $response = Http::withOptions([
            'timeout' => 120, // Increase timeout for large data
            'connect_timeout' => 60,
            'verify' => false, // Disable SSL verification if necessary
            ])->get($url);
        
        if ($response->successful()) {
            $countries = $response->json();
            
            foreach ($countries as $country) {

                //dd($country);
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
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/states.json';
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
                    // Generate unique code for states without state_code
                    //$stateCode = $state['state_code'] ?? $this->generateStateCode($state['name'], $country->id);
                    
                    State::updateOrCreate(
                        [
                            'name' => $state['name'],
                            'country_id' => $country->id,
                        ],
                        [
                            'code' => $state['state_code'], // ✅ Now always has a value
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
        // Focus on Indian cities first
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/cities.json';
        $response = Http::withOptions([
            'timeout' => 120, // Increase timeout for large data
            'connect_timeout' => 60,
            'verify' => false, // Disable SSL verification if necessary
            ])->get($url);
        
        if ($response->successful()) {
            $cities = $response->json();
            
            // Filter for India first (to avoid memory issues)
            $indianCities = array_filter($cities, function($city) {
                return $city['country_code'] === 'IN';
            });
            
            foreach ($indianCities as $city) {
                $country = Country::where('code', $city['country_code'])->first();
                $state = State::where('name', $city['state_name'])
                             ->where('country_id', $country->id ?? 0)
                             ->first();
                
                if ($country && $state) {
                    City::updateOrCreate(
                        [
                            'name' => $city['name'],
                            'state_id' => $state->id,
                            'country_id' => $country->id,
                        ],
                        [
                            'is_major' => false,
                            'postal_code_pattern' => false,
                            'is_active' => true,
                            'sort_order' => 0,
                        ]
                    );
                }
            }
        }
        
        $this->command->info('Cities imported successfully!');
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

        $country = Country::where('code', 'IN')->first();
        if (!$country) {
            $this->command->error('Country IN not found.');
            return;
        }

        $path = database_path('data/postal/IN.txt');
        $rows = File::lines($path);

         foreach ($rows as $line) {
            $columns = explode("\t", $line);

            $postalCode = $columns[1] ?? null;
            $area       = $columns[2] ?? null;
            $stateName  = $columns[3] ?? null;
            $cityName   = $columns[7] ?? null;

            if (!$postalCode || !$cityName || !$stateName) continue;

            $state = State::where('name', $stateName)
                ->where('country_id', $country->id)
                ->first();

            if (!$state) continue;    

            $city = City::where('name', $cityName)
                ->where('state_id', $state->id)
                ->where('country_id', $country->id)
                ->first();
            
            if (!$city) continue;
    

            PostalCode::updateOrCreate(
                [
                    'code'       => $postalCode,
                    'country_id' => $country->id,
                ],
                [
                    'area'       => $area,
                    'state_id'   => $state->id,
                    'city_id'    => $city->id,
                    'is_active'  => true,
                ]
            );
        }
        $this->command->info('Postal codes seeded from IN.txt');
    }
}
