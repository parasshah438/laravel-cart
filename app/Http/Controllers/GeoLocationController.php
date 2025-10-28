<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoLocationController extends Controller
{
    /**
     * Get country code based on client IP
     */
    public function getCountryCode(Request $request)
    {
        try {
            // Get client IP
            $clientIp = $this->getClientIp($request);
            
            // Use cache to avoid hitting the API too frequently
            $cacheKey = 'geo_location_' . md5($clientIp);
            
            $countryCode = Cache::remember($cacheKey, 3600, function () use ($clientIp) {
                return $this->fetchCountryCode($clientIp);
            });
            
            return response()->json([
                'success' => true,
                'country_code' => $countryCode,
                'ip' => $clientIp
            ]);
            
        } catch (\Exception $e) {
            Log::error('GeoLocation API Error: ' . $e->getMessage());
            
            // Return default country code on error
            return response()->json([
                'success' => false,
                'country_code' => 'us',
                'error' => 'Unable to determine location'
            ]);
        }
    }

    /**
     * Get detailed location information from coordinates
     */
    public function getLocationDetails(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            
            // Cache key based on coordinates (rounded to 4 decimal places for reasonable caching)
            $cacheKey = 'location_details_' . round($latitude, 4) . '_' . round($longitude, 4);
            
            $locationData = Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
                return $this->fetchLocationDetails($latitude, $longitude);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locationData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Location Details API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch location details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get location details from IP address
     */
    public function getLocationFromIP(Request $request)
    {
        try {
            $clientIp = $this->getClientIp($request);
            
            $cacheKey = 'ip_location_' . md5($clientIp);
            
            $locationData = Cache::remember($cacheKey, 3600, function () use ($clientIp) {
                return $this->fetchLocationFromIP($clientIp);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locationData,
                'ip' => $clientIp
            ]);
            
        } catch (\Exception $e) {
            Log::error('IP Location API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch location from IP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search locations by query (for autocomplete)
     */
    public function searchLocations(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3|max:100'
        ]);

        try {
            $query = $request->query;
            
            $cacheKey = 'location_search_' . md5(strtolower($query));
            
            $results = Cache::remember($cacheKey, 900, function () use ($query) {
                return $this->searchLocationsByQuery($query);
            });
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Location Search API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to search locations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pincode details - Now supports multiple countries
     */
    public function getPincodeDetails(Request $request)
    {
        $request->validate([
            'pincode' => 'required|string|min:3|max:10', // Allow various postal code formats
            'country_code' => 'string|size:2' // Optional country code
        ]);

        try {
            $pincode = $request->pincode;
            $countryCode = strtoupper($request->country_code ?? 'IN'); // Default to India
            
            $cacheKey = 'pincode_details_' . $countryCode . '_' . $pincode;
            
            $pincodeData = Cache::remember($cacheKey, 86400, function () use ($pincode, $countryCode) {
                return $this->fetchPincodeDetails($pincode, $countryCode);
            });
            
            return response()->json([
                'success' => true,
                'data' => $pincodeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pincode Details API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch pincode details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch detailed location information from coordinates using multiple services
     */
    private function fetchLocationDetails($latitude, $longitude)
    {
        $services = [
            // Nominatim (OpenStreetMap) - Free and reliable
            [
                'url' => "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1",
                'parser' => 'parseNominatimResponse'
            ],
            // Alternative HTTP endpoint for Nominatim (no SSL)
            [
                'url' => "http://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1",
                'parser' => 'parseNominatimResponse'
            ],
            // LocationIQ (free tier available)
            [
                'url' => "https://us1.locationiq.com/v1/reverse.php?key=demo&lat={$latitude}&lon={$longitude}&format=json&addressdetails=1",
                'parser' => 'parseLocationIQResponse'
            ],
            // Google Maps Geocoding API (requires API key)
            // [
            //     'url' => "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key=" . env('GOOGLE_MAPS_API_KEY'),
            //     'parser' => 'parseGoogleResponse'
            // ],
            // MapBox (requires API key)
            // [
            //     'url' => "https://api.mapbox.com/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json?access_token=" . env('MAPBOX_API_KEY'),
            //     'parser' => 'parseMapBoxResponse'
            // ]
        ];

        foreach ($services as $service) {
            try {
                Log::info("Trying location service: " . $service['url']);
                
                $httpClient = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App',
                        'Accept' => 'application/json'
                    ]);
                
                // Disable SSL verification for development environment
                if (app()->environment('local')) {
                    $httpClient = $httpClient->withOptions([
                        'verify' => false,
                        'curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ]
                    ]);
                }
                
                $response = $httpClient->get($service['url']);
                
                Log::info("Location service response status: " . $response->status());
                
                if ($response->successful()) {
                    $data = $response->json();
                    Log::info("Location service response data: " . json_encode($data));
                    
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        Log::info("Successfully got location details from service: " . $service['url']);
                        return $result;
                    }
                } else {
                    Log::warning("Location service returned status: " . $response->status() . " for URL: " . $service['url']);
                }
            } catch (\Exception $e) {
                Log::warning("Location service failed: " . $service['url'] . " - Error: " . $e->getMessage());
                continue;
            }
        }

        // If all services fail, try to provide approximate location based on coordinates
        $approximateLocation = $this->getApproximateLocationFromCoordinates($latitude, $longitude);
        if ($approximateLocation) {
            Log::info("Using approximate location data for coordinates: {$latitude}, {$longitude}");
            return $approximateLocation;
        }

        throw new \Exception('All location services failed');
    }

    /**
     * Fetch location details from IP address
     */
    private function fetchLocationFromIP($ip)
    {
        if ($this->isLocalIp($ip)) {
            // For local development, return sample data
            return [
                'country' => 'India',
                'country_code' => 'IN',
                'state' => 'Maharashtra',
                'city' => 'Mumbai',
                'area' => 'Andheri',
                'pincode' => '400058',
                'latitude' => 19.1136,
                'longitude' => 72.8697,
                'formatted_address' => 'Andheri, Mumbai, Maharashtra, India'
            ];
        }

        $services = [
            [
                'url' => "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,region,regionName,city,zip,lat,lon,timezone",
                'parser' => 'parseIPApiResponse'
            ],
            [
                'url' => "https://ipapi.co/{$ip}/json/",
                'parser' => 'parseIPApiCoResponse'
            ]
        ];

        foreach ($services as $service) {
            try {
                $response = Http::timeout(8)->get($service['url']);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("IP location service failed: " . $e->getMessage());
                continue;
            }
        }

        throw new \Exception('All IP location services failed');
    }

    /**
     * Search locations by query
     */
    private function searchLocationsByQuery($query)
    {
        $services = [
            [
                'url' => "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query) . "&limit=10&addressdetails=1",
                'parser' => 'parseNominatimSearchResponse'
            ]
        ];

        foreach ($services as $service) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App'
                    ])
                    ->get($service['url']);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Location search service failed: " . $e->getMessage());
                continue;
            }
        }

        return [];
    }

    /**
     * Fetch pincode details from multiple APIs with global country support
     */
    private function fetchPincodeDetails($pincode, $countryCode = 'IN')
    {
        $services = [];
        
        // Country-specific services
        if ($countryCode === 'IN') {
            // Indian postal code services
            $services = [
                [
                    'url' => "https://api.postalpincode.in/pincode/{$pincode}",
                    'parser' => 'parsePostalPincodeResponse'
                ],
                [
                    'url' => "https://api.zippopotam.us/in/{$pincode}",
                    'parser' => 'parseZippopotamResponse'
                ],
                [
                    'url' => "http://api.postalpincode.in/pincode/{$pincode}",
                    'parser' => 'parsePostalPincodeResponse'
                ]
            ];
        } else {
            // Global postal code service (Zippopotam supports many countries)
            $services = [
                [
                    'url' => "https://api.zippopotam.us/" . strtolower($countryCode) . "/{$pincode}",
                    'parser' => 'parseZippopotamResponse'
                ]
            ];
        }

        foreach ($services as $service) {
            try {
                Log::info("Trying pincode service: " . $service['url']);
                
                $httpClient = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App',
                        'Accept' => 'application/json'
                    ]);
                
                // Disable SSL verification for development environment
                if (app()->environment('local')) {
                    $httpClient = $httpClient->withOptions([
                        'verify' => false,
                        'curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ]
                    ]);
                }
                
                $response = $httpClient->get($service['url']);
                
                Log::info("Pincode API response status: " . $response->status());
                
                if ($response->successful()) {
                    $data = $response->json();
                    Log::info("Pincode API response data: " . json_encode($data));
                    
                    $parser = $service['parser'];
                    $result = $this->$parser($data, $pincode);
                    
                    if ($result) {
                        Log::info("Successfully parsed pincode data from: " . $service['url']);
                        return $result;
                    }
                } else {
                    Log::warning("Pincode API returned status: " . $response->status() . " for URL: " . $service['url']);
                }
            } catch (\Exception $e) {
                Log::error("Pincode service failed: " . $service['url'] . " - Error: " . $e->getMessage());
                continue;
            }
        }

        // If all APIs fail, try to provide a basic response for common pincodes
        $fallbackData = $this->getFallbackPincodeData($pincode, $countryCode);
        if ($fallbackData) {
            Log::info("Using fallback data for pincode: " . $pincode . " in country: " . $countryCode);
            return $fallbackData;
        }

        throw new \Exception("Unable to fetch pincode details for {$pincode} in {$countryCode}. All services failed.");
    }

    /**
     * Parse response from api.postalpincode.in
     */
    private function parsePostalPincodeResponse($data, $pincode)
    {
        if (!is_array($data) || !isset($data[0])) {
            Log::warning("Invalid response structure from postalpincode API");
            return null;
        }

        $firstResult = $data[0];
        
        if (!isset($firstResult['Status']) || $firstResult['Status'] !== 'Success') {
            Log::warning("Postalpincode API returned status: " . ($firstResult['Status'] ?? 'Unknown'));
            return null;
        }

        if (!isset($firstResult['PostOffice']) || !is_array($firstResult['PostOffice']) || empty($firstResult['PostOffice'])) {
            Log::warning("No post office data found in postalpincode API response");
            return null;
        }

        $postOffice = $firstResult['PostOffice'][0];
        
        return [
            'pincode' => $pincode,
            'area' => $postOffice['Name'] ?? '',
            'city' => $postOffice['District'] ?? '',
            'state' => $postOffice['State'] ?? '',
            'country' => $postOffice['Country'] ?? 'India',
            'region' => $postOffice['Region'] ?? '',
            'division' => $postOffice['Division'] ?? '',
            'formatted_address' => $this->formatAddress([
                $postOffice['Name'] ?? '',
                $postOffice['District'] ?? '',
                $postOffice['State'] ?? '',
                $postOffice['Country'] ?? 'India'
            ])
        ];
    }

    /**
     * Parse response from zippopotam.us
     */
    private function parseZippopotamResponse($data, $pincode)
    {
        if (!isset($data['places']) || !is_array($data['places']) || empty($data['places'])) {
            Log::warning("No places data found in zippopotam API response");
            return null;
        }

        $place = $data['places'][0];
        
        return [
            'pincode' => $pincode,
            'area' => $place['place name'] ?? '',
            'city' => $place['place name'] ?? '',
            'state' => $place['state'] ?? '',
            'country' => $data['country'] ?? 'India',
            'region' => '',
            'division' => '',
            'formatted_address' => $this->formatAddress([
                $place['place name'] ?? '',
                $place['state'] ?? '',
                $data['country'] ?? 'India'
            ])
        ];
    }

    /**
     * Get fallback data for common pincodes globally
     */
    private function getFallbackPincodeData($pincode, $countryCode = 'IN')
    {
        if ($countryCode === 'IN') {
            // Indian postal codes (existing data)
            $fallbackData = [
                // Maharashtra
                '400' => ['city' => 'Mumbai', 'state' => 'Maharashtra'],
                '401' => ['city' => 'Thane', 'state' => 'Maharashtra'],
            '411' => ['city' => 'Pune', 'state' => 'Maharashtra'],
            '412' => ['city' => 'Pune', 'state' => 'Maharashtra'],
            '413' => ['city' => 'Solapur', 'state' => 'Maharashtra'],
            '414' => ['city' => 'Ahmednagar', 'state' => 'Maharashtra'],
            '415' => ['city' => 'Sangli', 'state' => 'Maharashtra'],
            '416' => ['city' => 'Kolhapur', 'state' => 'Maharashtra'],
            
            // Delhi & NCR
            '110' => ['city' => 'New Delhi', 'state' => 'Delhi'],
            '121' => ['city' => 'Faridabad', 'state' => 'Haryana'],
            '122' => ['city' => 'Gurgaon', 'state' => 'Haryana'],
            '201' => ['city' => 'Ghaziabad', 'state' => 'Uttar Pradesh'],
            
            // Karnataka
            '560' => ['city' => 'Bangalore', 'state' => 'Karnataka'],
            '561' => ['city' => 'Bangalore Rural', 'state' => 'Karnataka'],
            '562' => ['city' => 'Chikkaballapur', 'state' => 'Karnataka'],
            '563' => ['city' => 'Kolar', 'state' => 'Karnataka'],
            '570' => ['city' => 'Mysore', 'state' => 'Karnataka'],
            '575' => ['city' => 'Mangalore', 'state' => 'Karnataka'],
            '580' => ['city' => 'Hubli', 'state' => 'Karnataka'],
            
            // Tamil Nadu
            '600' => ['city' => 'Chennai', 'state' => 'Tamil Nadu'],
            '601' => ['city' => 'Kanchipuram', 'state' => 'Tamil Nadu'],
            '602' => ['city' => 'Tiruvallur', 'state' => 'Tamil Nadu'],
            '603' => ['city' => 'Vellore', 'state' => 'Tamil Nadu'],
            '620' => ['city' => 'Tiruchirappalli', 'state' => 'Tamil Nadu'],
            '625' => ['city' => 'Madurai', 'state' => 'Tamil Nadu'],
            '630' => ['city' => 'Thanjavur', 'state' => 'Tamil Nadu'],
            '641' => ['city' => 'Coimbatore', 'state' => 'Tamil Nadu'],
            
            // Telangana & Andhra Pradesh
            '500' => ['city' => 'Hyderabad', 'state' => 'Telangana'],
            '501' => ['city' => 'Hyderabad', 'state' => 'Telangana'],
            '502' => ['city' => 'Medak', 'state' => 'Telangana'],
            '503' => ['city' => 'Nizamabad', 'state' => 'Telangana'],
            '504' => ['city' => 'Adilabad', 'state' => 'Telangana'],
            '505' => ['city' => 'Karimnagar', 'state' => 'Telangana'],
            '506' => ['city' => 'Warangal', 'state' => 'Telangana'],
            '507' => ['city' => 'Khammam', 'state' => 'Telangana'],
            '508' => ['city' => 'Nalgonda', 'state' => 'Telangana'],
            '509' => ['city' => 'Mahabubnagar', 'state' => 'Telangana'],
            '515' => ['city' => 'Anantapur', 'state' => 'Andhra Pradesh'],
            '516' => ['city' => 'Kadapa', 'state' => 'Andhra Pradesh'],
            '517' => ['city' => 'Chittoor', 'state' => 'Andhra Pradesh'],
            '518' => ['city' => 'Kurnool', 'state' => 'Andhra Pradesh'],
            '520' => ['city' => 'Vijayawada', 'state' => 'Andhra Pradesh'],
            '521' => ['city' => 'Krishna', 'state' => 'Andhra Pradesh'],
            '522' => ['city' => 'Guntur', 'state' => 'Andhra Pradesh'],
            '523' => ['city' => 'Prakasam', 'state' => 'Andhra Pradesh'],
            '530' => ['city' => 'Visakhapatnam', 'state' => 'Andhra Pradesh'],
            '531' => ['city' => 'Vizianagaram', 'state' => 'Andhra Pradesh'],
            '532' => ['city' => 'Srikakulam', 'state' => 'Andhra Pradesh'],
            '533' => ['city' => 'East Godavari', 'state' => 'Andhra Pradesh'],
            '534' => ['city' => 'West Godavari', 'state' => 'Andhra Pradesh'],
            
            // West Bengal
            '700' => ['city' => 'Kolkata', 'state' => 'West Bengal'],
            '701' => ['city' => 'North 24 Parganas', 'state' => 'West Bengal'],
            '711' => ['city' => 'Howrah', 'state' => 'West Bengal'],
            '712' => ['city' => 'Hooghly', 'state' => 'West Bengal'],
            '713' => ['city' => 'Bardhaman', 'state' => 'West Bengal'],
            '721' => ['city' => 'Midnapore', 'state' => 'West Bengal'],
            '731' => ['city' => 'Malda', 'state' => 'West Bengal'],
            '732' => ['city' => 'Darjeeling', 'state' => 'West Bengal'],
            '733' => ['city' => 'Jalpaiguri', 'state' => 'West Bengal'],
            '734' => ['city' => 'Cooch Behar', 'state' => 'West Bengal'],
            '735' => ['city' => 'Alipurduar', 'state' => 'West Bengal'],
            '736' => ['city' => 'Kalimpong', 'state' => 'West Bengal'],
            '741' => ['city' => 'Nadia', 'state' => 'West Bengal'],
            '742' => ['city' => 'Murshidabad', 'state' => 'West Bengal'],
            '743' => ['city' => 'Birbhum', 'state' => 'West Bengal'],
            
            // Gujarat
            '360' => ['city' => 'Rajkot', 'state' => 'Gujarat'],
            '361' => ['city' => 'Jamnagar', 'state' => 'Gujarat'],
            '362' => ['city' => 'Porbandar', 'state' => 'Gujarat'],
            '363' => ['city' => 'Surendranagar', 'state' => 'Gujarat'], // This covers the 363530 pincode
            '364' => ['city' => 'Bhavnagar', 'state' => 'Gujarat'],
            '365' => ['city' => 'Amreli', 'state' => 'Gujarat'],
            '370' => ['city' => 'Kachchh', 'state' => 'Gujarat'],
            '380' => ['city' => 'Ahmedabad', 'state' => 'Gujarat'],
            '381' => ['city' => 'Mehsana', 'state' => 'Gujarat'],
            '382' => ['city' => 'Sabarkantha', 'state' => 'Gujarat'],
            '383' => ['city' => 'Banaskantha', 'state' => 'Gujarat'],
            '384' => ['city' => 'Patan', 'state' => 'Gujarat'],
            '385' => ['city' => 'Kachchh', 'state' => 'Gujarat'],
            '387' => ['city' => 'Gandhinagar', 'state' => 'Gujarat'],
            '388' => ['city' => 'Anand', 'state' => 'Gujarat'],
            '389' => ['city' => 'Kheda', 'state' => 'Gujarat'],
            '390' => ['city' => 'Vadodara', 'state' => 'Gujarat'],
            '391' => ['city' => 'Bharuch', 'state' => 'Gujarat'],
            '392' => ['city' => 'Narmada', 'state' => 'Gujarat'],
            '393' => ['city' => 'Narmada', 'state' => 'Gujarat'],
            '394' => ['city' => 'Surat', 'state' => 'Gujarat'],
            '395' => ['city' => 'Navsari', 'state' => 'Gujarat'],
            '396' => ['city' => 'Valsad', 'state' => 'Gujarat'],
            
            // Rajasthan
            '301' => ['city' => 'Alwar', 'state' => 'Rajasthan'],
            '302' => ['city' => 'Jaipur', 'state' => 'Rajasthan'],
            '303' => ['city' => 'Dausa', 'state' => 'Rajasthan'],
            '304' => ['city' => 'Tonk', 'state' => 'Rajasthan'],
            '305' => ['city' => 'Ajmer', 'state' => 'Rajasthan'],
            '306' => ['city' => 'Pali', 'state' => 'Rajasthan'],
            '307' => ['city' => 'Sirohi', 'state' => 'Rajasthan'],
            '311' => ['city' => 'Bhilwara', 'state' => 'Rajasthan'],
            '312' => ['city' => 'Chittorgarh', 'state' => 'Rajasthan'],
            '313' => ['city' => 'Udaipur', 'state' => 'Rajasthan'],
            '314' => ['city' => 'Dungarpur', 'state' => 'Rajasthan'],
            '321' => ['city' => 'Bharatpur', 'state' => 'Rajasthan'],
            '322' => ['city' => 'Sawai Madhopur', 'state' => 'Rajasthan'],
            '323' => ['city' => 'Bundi', 'state' => 'Rajasthan'],
            '324' => ['city' => 'Kota', 'state' => 'Rajasthan'],
            '325' => ['city' => 'Baran', 'state' => 'Rajasthan'],
            '326' => ['city' => 'Jhalawar', 'state' => 'Rajasthan'],
            '331' => ['city' => 'Churu', 'state' => 'Rajasthan'],
            '332' => ['city' => 'Sikar', 'state' => 'Rajasthan'],
            '333' => ['city' => 'Jhunjhunu', 'state' => 'Rajasthan'],
            '334' => ['city' => 'Bikaner', 'state' => 'Rajasthan'],
            '335' => ['city' => 'Hanumangarh', 'state' => 'Rajasthan'],
            '341' => ['city' => 'Jodhpur', 'state' => 'Rajasthan'],
            '342' => ['city' => 'Barmer', 'state' => 'Rajasthan'],
            '343' => ['city' => 'Jalore', 'state' => 'Rajasthan'],
            '344' => ['city' => 'Jaisalmer', 'state' => 'Rajasthan'],
            '345' => ['city' => 'Jaisalmer', 'state' => 'Rajasthan'],
        ];

        $prefix = substr($pincode, 0, 3);
        
        if (isset($fallbackData[$prefix])) {
            $data = $fallbackData[$prefix];
            return [
                'pincode' => $pincode,
                'area' => $data['city'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => 'India',
                'region' => '',
                'division' => '',
                'formatted_address' => $this->formatAddress([
                    $data['city'],
                    $data['state'],
                    'India'
                ])
            ];
        }
        } else {
            // Global postal code patterns for other countries
            $globalFallbackData = [
                'US' => [
                    '100' => ['city' => 'New York', 'state' => 'New York'],
                    '200' => ['city' => 'Washington', 'state' => 'District of Columbia'],
                    '300' => ['city' => 'Philadelphia', 'state' => 'Pennsylvania'],
                    '900' => ['city' => 'Los Angeles', 'state' => 'California'],
                ],
                'GB' => [
                    'SW1' => ['city' => 'London', 'state' => 'England'],
                    'M1' => ['city' => 'Manchester', 'state' => 'England'],
                    'B1' => ['city' => 'Birmingham', 'state' => 'England'],
                ],
                'CA' => [
                    'M5V' => ['city' => 'Toronto', 'state' => 'Ontario'],
                    'H3A' => ['city' => 'Montreal', 'state' => 'Quebec'],
                    'V6B' => ['city' => 'Vancouver', 'state' => 'British Columbia'],
                ],
                'AU' => [
                    '200' => ['city' => 'Sydney', 'state' => 'New South Wales'],
                    '300' => ['city' => 'Melbourne', 'state' => 'Victoria'],
                    '400' => ['city' => 'Brisbane', 'state' => 'Queensland'],
                ],
                'DE' => [
                    '10' => ['city' => 'Berlin', 'state' => 'Berlin'],
                    '20' => ['city' => 'Hamburg', 'state' => 'Hamburg'],
                    '80' => ['city' => 'Munich', 'state' => 'Bavaria'],
                ],
                'FR' => [
                    '75' => ['city' => 'Paris', 'state' => 'Île-de-France'],
                    '69' => ['city' => 'Lyon', 'state' => 'Auvergne-Rhône-Alpes'],
                    '13' => ['city' => 'Marseille', 'state' => 'Provence-Alpes-Côte d\'Azur'],
                ]
            ];
            
            if (isset($globalFallbackData[$countryCode])) {
                $countryData = $globalFallbackData[$countryCode];
                $prefix = substr($pincode, 0, ($countryCode === 'US' ? 3 : 2));
                
                if (isset($countryData[$prefix])) {
                    $data = $countryData[$prefix];
                    $countryName = $this->getCountryName($countryCode);
                    
                    return [
                        'pincode' => $pincode,
                        'area' => $data['city'],
                        'city' => $data['city'],
                        'state' => $data['state'],
                        'country' => $countryName,
                        'region' => '',
                        'division' => '',
                        'formatted_address' => $this->formatAddress([
                            $data['city'],
                            $data['state'],
                            $countryName
                        ])
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Format address from array of components
     */
    private function formatAddress($components)
    {
        $filtered = array_filter($components, function($component) {
            return !empty(trim($component));
        });
        
        return implode(', ', $filtered);
    }

    /**
     * Parse LocationIQ response (similar to Nominatim)
     */
    private function parseLocationIQResponse($data)
    {
        return $this->parseNominatimResponse($data);
    }

    /**
     * Get approximate location based on coordinates (global fallback method)
     */
    private function getApproximateLocationFromCoordinates($latitude, $longitude)
    {
        // Global coordinate-based location approximation
        $globalRegions = [
            // India
            ['lat_min' => 28.0, 'lat_max' => 32.0, 'lon_min' => 76.0, 'lon_max' => 78.5, 'city' => 'New Delhi', 'state' => 'Delhi', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 18.8, 'lat_max' => 19.3, 'lon_min' => 72.7, 'lon_max' => 73.2, 'city' => 'Mumbai', 'state' => 'Maharashtra', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 22.9, 'lat_max' => 23.3, 'lon_min' => 72.4, 'lon_max' => 72.8, 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 12.8, 'lat_max' => 13.2, 'lon_min' => 77.4, 'lon_max' => 77.8, 'city' => 'Bangalore', 'state' => 'Karnataka', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 12.8, 'lat_max' => 13.2, 'lon_min' => 80.1, 'lon_max' => 80.4, 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 17.2, 'lat_max' => 17.6, 'lon_min' => 78.2, 'lon_max' => 78.7, 'city' => 'Hyderabad', 'state' => 'Telangana', 'country' => 'India', 'country_code' => 'IN'],
            ['lat_min' => 22.4, 'lat_max' => 22.8, 'lon_min' => 88.2, 'lon_max' => 88.5, 'city' => 'Kolkata', 'state' => 'West Bengal', 'country' => 'India', 'country_code' => 'IN'],
            
            // United States
            ['lat_min' => 40.4, 'lat_max' => 40.9, 'lon_min' => -74.3, 'lon_max' => -73.7, 'city' => 'New York', 'state' => 'New York', 'country' => 'United States', 'country_code' => 'US'],
            ['lat_min' => 34.0, 'lat_max' => 34.3, 'lon_min' => -118.7, 'lon_max' => -118.1, 'city' => 'Los Angeles', 'state' => 'California', 'country' => 'United States', 'country_code' => 'US'],
            ['lat_min' => 41.8, 'lat_max' => 42.0, 'lon_min' => -87.8, 'lon_max' => -87.5, 'city' => 'Chicago', 'state' => 'Illinois', 'country' => 'United States', 'country_code' => 'US'],
            ['lat_min' => 25.6, 'lat_max' => 25.9, 'lon_min' => -80.3, 'lon_max' => -80.1, 'city' => 'Miami', 'state' => 'Florida', 'country' => 'United States', 'country_code' => 'US'],
            
            // United Kingdom
            ['lat_min' => 51.3, 'lat_max' => 51.7, 'lon_min' => -0.5, 'lon_max' => 0.3, 'city' => 'London', 'state' => 'England', 'country' => 'United Kingdom', 'country_code' => 'GB'],
            ['lat_min' => 53.3, 'lat_max' => 53.6, 'lon_min' => -2.4, 'lon_max' => -2.1, 'city' => 'Manchester', 'state' => 'England', 'country' => 'United Kingdom', 'country_code' => 'GB'],
            
            // Canada
            ['lat_min' => 43.6, 'lat_max' => 43.9, 'lon_min' => -79.7, 'lon_max' => -79.1, 'city' => 'Toronto', 'state' => 'Ontario', 'country' => 'Canada', 'country_code' => 'CA'],
            ['lat_min' => 45.4, 'lat_max' => 45.6, 'lon_min' => -73.8, 'lon_max' => -73.4, 'city' => 'Montreal', 'state' => 'Quebec', 'country' => 'Canada', 'country_code' => 'CA'],
            
            // Australia
            ['lat_min' => -34.0, 'lat_max' => -33.7, 'lon_min' => 151.0, 'lon_max' => 151.3, 'city' => 'Sydney', 'state' => 'New South Wales', 'country' => 'Australia', 'country_code' => 'AU'],
            ['lat_min' => -37.9, 'lat_max' => -37.6, 'lon_min' => 144.8, 'lon_max' => 145.1, 'city' => 'Melbourne', 'state' => 'Victoria', 'country' => 'Australia', 'country_code' => 'AU'],
            
            // Germany
            ['lat_min' => 52.4, 'lat_max' => 52.6, 'lon_min' => 13.2, 'lon_max' => 13.6, 'city' => 'Berlin', 'state' => 'Berlin', 'country' => 'Germany', 'country_code' => 'DE'],
            ['lat_min' => 53.4, 'lat_max' => 53.7, 'lon_min' => 9.8, 'lon_max' => 10.2, 'city' => 'Hamburg', 'state' => 'Hamburg', 'country' => 'Germany', 'country_code' => 'DE'],
            
            // France
            ['lat_min' => 48.8, 'lat_max' => 48.9, 'lon_min' => 2.2, 'lon_max' => 2.5, 'city' => 'Paris', 'state' => 'Île-de-France', 'country' => 'France', 'country_code' => 'FR'],
            
            // Japan
            ['lat_min' => 35.6, 'lat_max' => 35.8, 'lon_min' => 139.6, 'lon_max' => 139.8, 'city' => 'Tokyo', 'state' => 'Tokyo', 'country' => 'Japan', 'country_code' => 'JP'],
            
            // China
            ['lat_min' => 39.8, 'lat_max' => 40.0, 'lon_min' => 116.2, 'lon_max' => 116.6, 'city' => 'Beijing', 'state' => 'Beijing', 'country' => 'China', 'country_code' => 'CN'],
            ['lat_min' => 31.1, 'lat_max' => 31.4, 'lon_min' => 121.3, 'lon_max' => 121.7, 'city' => 'Shanghai', 'state' => 'Shanghai', 'country' => 'China', 'country_code' => 'CN'],
        ];

        foreach ($globalRegions as $region) {
            if ($latitude >= $region['lat_min'] && $latitude <= $region['lat_max'] &&
                $longitude >= $region['lon_min'] && $longitude <= $region['lon_max']) {
                
                return [
                    'country' => $region['country'],
                    'country_code' => $region['country_code'],
                    'state' => $region['state'],
                    'city' => $region['city'],
                    'area' => $region['city'],
                    'pincode' => '',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'formatted_address' => $region['city'] . ', ' . $region['state'] . ', ' . $region['country']
                ];
            }
        }

        // If no specific region matches, provide generic location based on broad coordinates
        $continentData = $this->getContinentFromCoordinates($latitude, $longitude);
        return [
            'country' => $continentData['country'],
            'country_code' => $continentData['country_code'],
            'state' => 'Unknown',
            'city' => 'Unknown',
            'area' => 'Unknown',
            'pincode' => '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'formatted_address' => $continentData['country']
        ];
    }

    /**
     * Parse Nominatim response
     */
    private function parseNominatimResponse($data)
    {
        if (!isset($data['address'])) {
            return null;
        }

        $address = $data['address'];
        
        return [
            'country' => $address['country'] ?? '',
            'country_code' => $address['country_code'] ?? '',
            'state' => $address['state'] ?? $address['region'] ?? '',
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
            'area' => $address['suburb'] ?? $address['neighbourhood'] ?? $address['hamlet'] ?? '',
            'pincode' => $address['postcode'] ?? '',
            'latitude' => (float) $data['lat'],
            'longitude' => (float) $data['lon'],
            'formatted_address' => $data['display_name'] ?? '',
            'road' => $address['road'] ?? '',
            'house_number' => $address['house_number'] ?? ''
        ];
    }

    /**
     * Parse IP-API response
     */
    private function parseIPApiResponse($data)
    {
        if (!isset($data['status']) || $data['status'] !== 'success') {
            return null;
        }

        return [
            'country' => $data['country'] ?? '',
            'country_code' => $data['countryCode'] ?? '',
            'state' => $data['regionName'] ?? '',
            'city' => $data['city'] ?? '',
            'area' => '',
            'pincode' => $data['zip'] ?? '',
            'latitude' => (float) $data['lat'],
            'longitude' => (float) $data['lon'],
            'formatted_address' => ($data['city'] ?? '') . ', ' . ($data['regionName'] ?? '') . ', ' . ($data['country'] ?? ''),
            'timezone' => $data['timezone'] ?? ''
        ];
    }

    /**
     * Parse IPApi.co response
     */
    private function parseIPApiCoResponse($data)
    {
        if (!isset($data['country_name'])) {
            return null;
        }

        return [
            'country' => $data['country_name'] ?? '',
            'country_code' => $data['country_code'] ?? '',
            'state' => $data['region'] ?? '',
            'city' => $data['city'] ?? '',
            'area' => '',
            'pincode' => $data['postal'] ?? '',
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'formatted_address' => ($data['city'] ?? '') . ', ' . ($data['region'] ?? '') . ', ' . ($data['country_name'] ?? ''),
            'timezone' => $data['timezone'] ?? ''
        ];
    }

    /**
     * Parse Nominatim search response
     */
    private function parseNominatimSearchResponse($data)
    {
        $results = [];
        
        foreach ($data as $item) {
            if (isset($item['address'])) {
                $address = $item['address'];
                
                $results[] = [
                    'display_name' => $item['display_name'],
                    'latitude' => (float) $item['lat'],
                    'longitude' => (float) $item['lon'],
                    'country' => $address['country'] ?? '',
                    'state' => $address['state'] ?? $address['region'] ?? '',
                    'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
                    'area' => $address['suburb'] ?? $address['neighbourhood'] ?? '',
                    'pincode' => $address['postcode'] ?? '',
                    'type' => $item['type'] ?? '',
                    'importance' => $item['importance'] ?? 0
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request)
    {
        // Check for various headers that might contain the real IP
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipHeaders as $header) {
            if ($request->server($header)) {
                $ips = explode(',', $request->server($header));
                $ip = trim($ips[0]);
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        $requestIp = $request->ip();
        
        // If we get a local IP, try to get the real public IP
        if ($this->isLocalIp($requestIp)) {
            return $this->getPublicIp();
        }
        
        return $requestIp;
    }
    
    /**
     * Check if IP is a local/private IP
     */
    private function isLocalIp($ip)
    {
        return $ip === '127.0.0.1' || 
               $ip === '::1' || 
               strpos($ip, '192.168.') === 0 || 
               strpos($ip, '10.') === 0 ||
               strpos($ip, '172.') === 0;
    }
    
    /**
     * Get public IP address when running locally
     */
    private function getPublicIp()
    {
        try {
            // Try multiple services to get public IP
            $ipServices = [
                'https://api.ipify.org',
                'https://ipinfo.io/ip',
                'https://icanhazip.com',
                'https://ident.me'
            ];
            
            foreach ($ipServices as $service) {
                try {
                    $response = Http::timeout(3)->get($service);
                    if ($response->successful()) {
                        $ip = trim($response->body());
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                            Log::info("Got public IP from {$service}: {$ip}");
                            return $ip;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("IP service {$service} failed: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get public IP: ' . $e->getMessage());
        }
        
        // Fallback to localhost
        return '127.0.0.1';
    }
    
    /**
     * Fetch country code from geolocation service
     */
    private function fetchCountryCode($ip)
    {
        // Only return default for localhost if we couldn't get public IP
        if ($this->isLocalIp($ip)) {
            Log::info("Using localhost IP {$ip}, returning default country");
            return 'in'; // Default to India since you mentioned you're in India
        }
        
        Log::info("Fetching country code for IP: {$ip}");
        
        try {
            // Try multiple services for better reliability
            $services = [
                "http://ip-api.com/json/{$ip}?fields=countryCode,country",
                "https://ipapi.co/{$ip}/json/",
                "http://www.geoplugin.net/json.gp?ip={$ip}",
                "https://ipinfo.io/{$ip}/json"
            ];
            
            foreach ($services as $index => $service) {
                try {
                    $response = Http::timeout(8)->get($service);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Handle different API response formats
                        if ($index === 0 && isset($data['countryCode'])) {
                            // ip-api.com
                            Log::info("Got country from ip-api.com: {$data['countryCode']} for IP {$ip}");
                            return strtolower($data['countryCode']);
                        } elseif ($index === 1 && isset($data['country_code'])) {
                            // ipapi.co
                            Log::info("Got country from ipapi.co: {$data['country_code']} for IP {$ip}");
                            return strtolower($data['country_code']);
                        } elseif ($index === 2 && isset($data['geoplugin_countryCode'])) {
                            // geoplugin.net
                            Log::info("Got country from geoplugin: {$data['geoplugin_countryCode']} for IP {$ip}");
                            return strtolower($data['geoplugin_countryCode']);
                        } elseif ($index === 3 && isset($data['country'])) {
                            // ipinfo.io
                            Log::info("Got country from ipinfo.io: {$data['country']} for IP {$ip}");
                            return strtolower($data['country']);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("GeoLocation service {$service} failed: " . $e->getMessage());
                    continue;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('All GeoLocation services failed: ' . $e->getMessage());
        }
        
        // Default fallback based on environment or detected user location
        Log::info("All services failed, returning default country: in");
        return env('DEFAULT_COUNTRY_CODE', 'in'); // You can set this in .env file
    }
    
    /**
     * Get continent and approximate country from coordinates
     */
    private function getContinentFromCoordinates($latitude, $longitude)
    {
        // Broad continental regions
        if ($latitude >= 23.5 && $latitude <= 71 && $longitude >= 60 && $longitude <= 180) {
            return ['country' => 'Unknown Asian Country', 'country_code' => 'AS'];
        } elseif ($latitude >= 35 && $latitude <= 71 && $longitude >= -25 && $longitude <= 60) {
            return ['country' => 'Unknown European Country', 'country_code' => 'EU'];
        } elseif ($latitude >= -35 && $latitude <= 37 && $longitude >= -20 && $longitude <= 50) {
            return ['country' => 'Unknown African Country', 'country_code' => 'AF'];
        } elseif ($latitude >= 5 && $latitude <= 83 && $longitude >= -180 && $longitude <= -30) {
            return ['country' => 'Unknown North American Country', 'country_code' => 'NA'];
        } elseif ($latitude >= -60 && $latitude <= 15 && $longitude >= -85 && $longitude <= -30) {
            return ['country' => 'Unknown South American Country', 'country_code' => 'SA'];
        } elseif ($latitude >= -50 && $latitude <= -10 && $longitude >= 110 && $longitude <= 180) {
            return ['country' => 'Unknown Oceanic Country', 'country_code' => 'OC'];
        }
        
        return ['country' => 'Unknown', 'country_code' => 'XX'];
    }
    
    /**
     * Get country name from country code
     */
    private function getCountryName($countryCode)
    {
        $countries = [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'JP' => 'Japan',
            'CN' => 'China',
            'IN' => 'India',
            'BR' => 'Brazil',
            'RU' => 'Russia',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'MX' => 'Mexico',
            'KR' => 'South Korea',
            'NL' => 'Netherlands',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland'
        ];
        
        return $countries[$countryCode] ?? 'Unknown Country';
    }
}