<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{UserAddress, Country, State, City, PostalCode};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * Display a listing of user addresses
     */
    public function index()
    {
        $addresses = UserAddress::with(['country', 'state', 'city'])
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('address.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address
     */
    public function create()
    {
        $countries = Country::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('address.create', compact('countries'));
    }

    /**
     * Store a newly created address
     */

public function store(Request $request)
{
    try {
        // Validate the request
        $validated = $this->validateAddress($request);
        
        // Log for debugging (remove dd)
        \Log::info('Address Form Data:', $request->all());
        
        // Handle default address logic
        if ($request->filled('is_default') && $request->is_default) {
            $this->clearDefaultAddresses();
        }

        if ($request->filled('is_default_billing') && $request->is_default_billing) {
            $this->clearDefaultBilling();
        }

        if ($request->filled('is_default_shipping') && $request->is_default_shipping) {
            $this->clearDefaultShipping();
        }

        $validated['user_id'] = Auth::id();
        $address = UserAddress::create($validated);

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully!',
                'address' => $address->load(['country', 'state', 'city'])
            ]);
        }

        // For regular form submission
        return redirect()->back()->with('success', 'Address saved successfully!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // For regular form submission
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();

    } catch (\Exception $e) {
        // Handle other errors
        \Log::error('Address Save Error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Something went wrong. Please try again.')
            ->withInput();
    }
}

    /**
     * Show the form for editing an address
     */
    public function edit(UserAddress $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $countries = Country::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $states = State::where('country_id', $address->country_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cities = City::where('state_id', $address->state_id)
            ->where('is_active', true)
            ->orderBy('is_major', 'desc')
            ->orderBy('name')
            ->get();

        return view('address.edit', compact('address', 'countries', 'states', 'cities'));
    }

    /**
     * Update the specified address
     */
   

public function update(Request $request, UserAddress $address)
{
    try {
        // Security check
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Validate the request
        $validated = $this->validateAddress($request, $address->id);

        $validated['is_default'] = $request->has('is_default') ? (bool)$request->is_default : false;
        $validated['is_default_billing'] = $request->has('is_default_billing') ? (bool)$request->is_default_billing : false;
        $validated['is_default_shipping'] = $request->has('is_default_shipping') ? (bool)$request->is_default_shipping : false;

        // Handle default address logic - only if checkbox is checked
        if ($validated['is_default']) {
            $this->clearDefaultAddresses($address->id);
        }

        if ($validated['is_default_billing']) {
            $this->clearDefaultBilling($address->id);
        }

        if ($validated['is_default_shipping']) {
            $this->clearDefaultShipping($address->id);
        }

        // Update the address
        $address->update($validated);

        // Load relationships for response
        $address->load(['country', 'state', 'city']);

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully!',
                'address' => $address
            ]);
        }

        // For regular form submission
        return redirect()->back()->with('success', 'Address updated successfully!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();

    } catch (\Exception $e) {
        // Log the error
        \Log::error('Address Update Error:', [
            'address_id' => $address->id,
            'user_id' => Auth::id(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating address'
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Something went wrong. Please try again.')
            ->withInput();
    }
}

    /**
     * Remove the specified address
     */
    public function destroy(UserAddress $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        // Soft delete by setting is_active to false
        $address->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully!'
        ]);
    }

    /**
     * Set an address as default
     */
    public function setDefault(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->firstOrFail();

        $type = $request->input('type', 'default'); // default, billing, shipping

        switch ($type) {
            case 'billing':
                $this->clearDefaultBilling($id);
                $address->update(['is_default_billing' => true]);
                $message = 'Set as default billing address';
                break;
                
            case 'shipping':
                $this->clearDefaultShipping($id);
                $address->update(['is_default_shipping' => true]);
                $message = 'Set as default shipping address';
                break;
                
            default:
                $this->clearDefaultAddresses($id);
                $address->update(['is_default' => true]);
                $message = 'Set as default address';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get postal code information (API endpoint)
     */
    public function getPostalCodeInfo($code)
    {
        $postalCode = PostalCode::with(['country', 'state', 'city'])
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$postalCode) {
            return response()->json([
                'success' => false,
                'message' => 'Postal code not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'postal_code' => $postalCode->code,
                'area' => $postalCode->area,
                'city_id' => $postalCode->city_id,
                'city_name' => $postalCode->city->name,
                'state_id' => $postalCode->state_id,
                'state_name' => $postalCode->state->name,
                'country_id' => $postalCode->country_id,
                'country_name' => $postalCode->country->name,
            ]
        ]);
    }

    /**
     * Get states by country (API endpoint)
     */
    public function getStates($countryId)
    {
        $states = State::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        return response()->json($states);
    }

    /**
     * Get cities by state (API endpoint)
     */
    public function getCities($stateId)
    {
        $cities = City::where('state_id', $stateId)
            ->where('is_active', true)
            ->orderBy('is_major', 'desc') // Major cities first
            ->orderBy('sort_order')
            ->orderBy('name')
            ->select('id', 'name', 'is_major')
            ->get();

        return response()->json($cities);
    }

    /**
     * Get user's default addresses for checkout
     */
    public function getDefaultAddresses()
    {
        $user = Auth::user();
        
        $defaultBilling = UserAddress::with(['country', 'state', 'city'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_default_billing', true)
            ->first();

        $defaultShipping = UserAddress::with(['country', 'state', 'city'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_default_shipping', true)
            ->first();

        // If no specific defaults, use general default
        if (!$defaultBilling || !$defaultShipping) {
            $generalDefault = UserAddress::with(['country', 'state', 'city'])
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();

            $defaultBilling = $defaultBilling ?: $generalDefault;
            $defaultShipping = $defaultShipping ?: $generalDefault;
        }

        return response()->json([
            'billing' => $defaultBilling,
            'shipping' => $defaultShipping
        ]);
    }

    /**
     * Validate postal code format and existence
     */
    public function validatePostalCode(Request $request)
    {
        $postalCode = $request->input('postal_code');
        $countryId = $request->input('country_id');

        // Basic format validation (Indian PIN code example)
        if ($countryId == 1) { // Assuming 1 is India
            if (!preg_match('/^[0-9]{6}$/', $postalCode)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Indian PIN code must be 6 digits'
                ]);
            }
        }

        // Check if postal code exists in database
        $exists = PostalCode::where('code', $postalCode)
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->exists();

        return response()->json([
            'valid' => $exists,
            'message' => $exists ? 'Valid postal code' : 'Invalid postal code for selected country'
        ]);
    }

    /**
     * Validate address data
     */
    private function validateAddress(Request $request, $addressId = null)
    {
        $rules = [
            'type' => 'required|in:home,work,other',
            'label' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:100',
            'phone_number' => 'required|string|size:10|regex:/^[0-9]{10}$/',
            'alternate_phone' => 'nullable|string|size:10|regex:/^[0-9]{10}$/',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'postal_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'gst_number' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'delivery_instructions' => 'nullable|string|max:500',
            'is_default' => 'boolean',
            'is_default_billing' => 'boolean',
            'is_default_shipping' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];

        $messages = [
            'phone_number.regex' => 'Phone number must be exactly 10 digits',
            'alternate_phone.regex' => 'Alternate phone must be exactly 10 digits',
            'postal_code.regex' => 'PIN code must be exactly 6 digits',
            'gst_number.regex' => 'Invalid GST number format',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Clear default addresses for user
     */
    private function clearDefaultAddresses($exceptId = null)
    {
        $query = UserAddress::where('user_id', Auth::id());
        
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        
        $query->update(['is_default' => false]);
    }

    /**
     * Clear default billing addresses for user
     */
    private function clearDefaultBilling($exceptId = null)
    {
        $query = UserAddress::where('user_id', Auth::id());
        
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        
        $query->update(['is_default_billing' => false]);
    }

    /**
     * Clear default shipping addresses for user
     */
    private function clearDefaultShipping($exceptId = null)
    {
        $query = UserAddress::where('user_id', Auth::id());
        
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        
        $query->update(['is_default_shipping' => false]);
    }

    /**
     * Get address suggestions based on partial input
     */
    public function getAddressSuggestions(Request $request)
    {
        $query = $request->input('query');
        $limit = $request->input('limit', 5);

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Search in cities first
        $cities = City::with(['state', 'country'])
            ->where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->orderBy('is_major', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($city) {
                return [
                    'type' => 'city',
                    'text' => $city->name . ', ' . $city->state->name . ', ' . $city->country->name,
                    'city_id' => $city->id,
                    'state_id' => $city->state_id,
                    'country_id' => $city->country_id,
                ];
            });

        // Search in postal codes
        $postalCodes = PostalCode::with(['city.state', 'country'])
            ->where('code', 'LIKE', "%{$query}%")
            ->orWhere('area', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->limit($limit)
            ->get()
            ->map(function ($postal) {
                return [
                    'type' => 'postal',
                    'text' => $postal->code . ' - ' . $postal->area . ', ' . $postal->city->name,
                    'postal_code' => $postal->code,
                    'city_id' => $postal->city_id,
                    'state_id' => $postal->state_id,
                    'country_id' => $postal->country_id,
                ];
            });

        $suggestions = $cities->concat($postalCodes)->take($limit);

        return response()->json($suggestions);
    }

    public function getPostalCodesForCity($cityId)
    {
        $postalCodes = PostalCode::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('code')
            ->select('id', 'code', 'area')
            ->get();

        if ($postalCodes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No postal codes found for this city'
            ]);
        }

        return response()->json([
            'success' => true,
            'postal_codes' => $postalCodes,
            'count' => $postalCodes->count()
        ]);
    }


/**
 * Get postal codes for a specific state (API endpoint)
 */
    public function getPostalCodesForState($stateId)
    {
        $postalCodes = PostalCode::with('city')
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->orderBy('code')
            ->limit(20) // Limit to prevent too many results
            ->select('id', 'code', 'area', 'city_id')
            ->get();

        if ($postalCodes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No postal codes found for this state'
            ]);
        }

        return response()->json([
            'success' => true,
            'postal_codes' => $postalCodes->map(function($postal) {
                return [
                    'id' => $postal->id,
                    'code' => $postal->code,
                    'area' => $postal->area,
                    'city_id' => $postal->city_id,
                    'city_name' => $postal->city->name,
                    'display_text' => $postal->code . ' - ' . $postal->area . ' (' . $postal->city->name . ')'
                ];
            }),
            'count' => $postalCodes->count()
        ]);
    }


public function apiShow($id)
{
    try {
        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid address ID'
            ], 400);
        }

        $address = UserAddress::with(['country', 'state', 'city'])
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        // Check if address exists and belongs to user
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or you do not have permission to access it'
            ], 404);
        }

        // Double security check
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to view this address.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'address' => $address
        ]);

    } catch (\Exception $e) {
        \Log::error('Address API Show Error:', [
            'address_id' => $id,
            'user_id' => Auth::id(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while fetching address'
        ], 500);
    }
}

/**
 * Regular show method for web views
 */
public function show(UserAddress $address)
{
    // Security check
    if ($address->user_id !== Auth::id()) {
        abort(403);
    }

    // If it's an AJAX request, return JSON
    if (request()->expectsJson()) {
        return response()->json([
            'success' => true,
            'address' => $address->load(['country', 'state', 'city'])
        ]);
    }

    // Return view for regular requests
    return view('address.show', compact('address'));
}
}    