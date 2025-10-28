<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class PostalCodeController extends Controller
{
    /**
     * Display the postal code checker page
     */
    public function index()
    {
        return view('postal-code-checker');
    }

    /**
     * Validate postal code using Zippopotam.us API
     */
    public function validatePostalCode(Request $request): JsonResponse
    {
        $request->validate([
            'country' => 'required|string|size:2',
            'postal_code' => 'required|string|max:10'
        ]);

        $country = strtolower($request->input('country'));
        $postalCode = $request->input('postal_code');

        try {
            // Make API call to Zippopotam.us
            $response = Http::timeout(10)->get("http://api.zippopotam.us/{$country}/{$postalCode}");

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Valid postal code',
                    'data' => [
                        'country' => $data['country'] ?? null,
                        'country_abbreviation' => $data['country abbreviation'] ?? null,
                        'places' => $data['places'] ?? [],
                        'post_code' => $data['post code'] ?? null
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'valid' => false,
                    'message' => 'Invalid postal code or country not supported',
                    'data' => null
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Error occurred while validating postal code: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get list of supported countries
     */
    public function getSupportedCountries(): JsonResponse
    {
        // Common supported countries by Zippopotam.us
        $countries = [
            'US' => 'United States',
            'GB' => 'United Kingdom', 
            'CA' => 'Canada',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'AT' => 'Austria',
            'CH' => 'Switzerland',
            'DK' => 'Denmark',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'FI' => 'Finland',
            'PL' => 'Poland',
            'CZ' => 'Czech Republic',
            'SK' => 'Slovakia',
            'HU' => 'Hungary',
            'SI' => 'Slovenia',
            'HR' => 'Croatia',
            'PT' => 'Portugal',
            'GR' => 'Greece',
            'BG' => 'Bulgaria',
            'RO' => 'Romania',
            'LT' => 'Lithuania',
            'LV' => 'Latvia',
            'EE' => 'Estonia',
            'LU' => 'Luxembourg',
            'MT' => 'Malta',
            'CY' => 'Cyprus',
            'IE' => 'Ireland',
            'IS' => 'Iceland',
            'MX' => 'Mexico',
            'BR' => 'Brazil',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'IN' => 'India',
            'ZA' => 'South Africa',
            'RU' => 'Russia',
            'TR' => 'Turkey'
        ];

        return response()->json([
            'success' => true,
            'countries' => $countries
        ]);
    }
}