<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderShipment;
use App\Models\Order;
use App\Models\ShippingCarrier;
use App\Services\ShipRocketService;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    protected $shipRocketService;

    public function __construct(ShipRocketService $shipRocketService)
    {
        $this->shipRocketService = $shipRocketService;
    }

    /**
     * Display a listing of shipments
     */
    public function index(Request $request)
    {
        $query = OrderShipment::with(['order.user', 'carrier', 'shippingMethod'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by carrier
        if ($request->has('carrier') && $request->carrier) {
            $query->where('carrier_id', $request->carrier);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $shipments = $query->paginate(20);

        // Get statistics for dashboard
        $stats = [
            'pending' => OrderShipment::where('status', 'pending')->count(),
            'in_transit' => OrderShipment::whereIn('status', ['picked_up', 'in_transit'])->count(),
            'out_for_delivery' => OrderShipment::where('status', 'out_for_delivery')->count(),
            'delivered' => OrderShipment::where('status', 'delivered')->count(),
            'exceptions' => OrderShipment::where('status', 'exception')->count(),
        ];

        $carriers = ShippingCarrier::active()->get();

        return view('admin.shipments.index', compact('shipments', 'stats', 'carriers'));
    }

    /**
     * Display the specified shipment
     */
    public function show(OrderShipment $shipment)
    {
        $shipment->load(['order.user', 'order.items.product', 'carrier', 'trackingEvents']);
        
        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Create a new shipment for an order
     */
    public function create(Request $request)
    {
        $order = null;
        if ($request->has('order_id')) {
            $order = Order::with(['user', 'address', 'items.product'])->findOrFail($request->order_id);
            
            // Check if order can create shipment
            if (!$order->canCreateShipment()) {
                return redirect()->back()->with('error', 'Order cannot create shipment. Check order status and payment.');
            }
        }

        $carriers = ShippingCarrier::active()->get();
        
        return view('admin.shipments.create', compact('order', 'carriers'));
    }

    /**
     * Store a new shipment
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'carrier_id' => 'required|exists:shipping_carriers,id',
            'package_weight' => 'required|numeric|min:0.1',
            'package_dimensions' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $order = Order::findOrFail($request->order_id);

        // Check if order already has a shipment
        if ($order->shipments()->exists()) {
            return redirect()->back()->with('error', 'Order already has a shipment.');
        }

        try {
            // Create shipment via ShipRocket if carrier is ShipRocket
            $carrier = ShippingCarrier::find($request->carrier_id);
            
            if ($carrier->code === 'shiprocket') {
                $shipment = $this->shipRocketService->createOrder($order);
                
                if ($request->package_weight) {
                    $shipment->update(['package_weight' => $request->package_weight]);
                }
                
                if ($request->package_dimensions) {
                    $shipment->update(['package_dimensions' => $request->package_dimensions]);
                }
                
                if ($request->notes) {
                    $shipment->update(['notes' => $request->notes]);
                }
                
                return redirect()->route('admin.shipments.show', $shipment)
                    ->with('success', 'Shipment created successfully via ShipRocket.');
            } else {
                // Create manual shipment for other carriers
                $shipment = OrderShipment::create([
                    'order_id' => $order->id,
                    'carrier_id' => $request->carrier_id,
                    'shipment_number' => 'SHP' . date('Ymd') . rand(100000, 999999),
                    'status' => 'pending',
                    'package_weight' => $request->package_weight,
                    'package_dimensions' => $request->package_dimensions,
                    'notes' => $request->notes,
                    'shipped_to_address' => [
                        'name' => $order->user->name,
                        'address_line_1' => $order->address->address_line_1,
                        'address_line_2' => $order->address->address_line_2,
                        'city' => $order->address->city->name ?? 'Mumbai',
                        'state' => $order->address->state->name ?? 'Maharashtra',
                        'postal_code' => $order->address->postal_code,
                        'country' => $order->address->country->name ?? 'India',
                        'phone' => $order->address->phone
                    ]
                ]);

                return redirect()->route('admin.shipments.show', $shipment)
                    ->with('success', 'Shipment created successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Shipment creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create shipment: ' . $e->getMessage());
        }
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Request $request, OrderShipment $shipment)
    {
        $request->validate([
            'status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered,exception,returned',
            'notes' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'location' => 'nullable|string'
        ]);

        try {
            // Update tracking number if provided
            if ($request->tracking_number && $request->tracking_number !== $shipment->tracking_number) {
                $shipment->update(['tracking_number' => $request->tracking_number]);
            }

            // Update status with tracking event
            $shipment->updateStatus(
                $request->status,
                $request->notes,
                $request->location
            );

            return redirect()->back()->with('success', 'Shipment status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Shipment status update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(OrderShipment $shipment)
    {
        try {
            if ($shipment->carrier->code === 'shiprocket' && $shipment->shiprocket_shipment_id) {
                $result = $this->shipRocketService->generateLabel($shipment->shiprocket_shipment_id);
                
                if ($result) {
                    return redirect()->back()->with('success', 'Shipping label generated successfully.');
                } else {
                    return redirect()->back()->with('error', 'Failed to generate shipping label.');
                }
            } else {
                return redirect()->back()->with('error', 'Label generation not supported for this carrier.');
            }
        } catch (\Exception $e) {
            Log::error('Label generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate label: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update shipment status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:order_shipments,id',
            'status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered,exception,returned'
        ]);

        try {
            $shipments = OrderShipment::whereIn('id', $request->shipment_ids)->get();
            $updated = 0;

            foreach ($shipments as $shipment) {
                $shipment->updateStatus($request->status, 'Bulk status update by admin');
                $updated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} shipments."
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk generate labels
     */
    public function bulkGenerateLabels(Request $request)
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:order_shipments,id'
        ]);

        try {
            $shipments = OrderShipment::with('carrier')
                ->whereIn('id', $request->shipment_ids)
                ->get();
            
            $generated = 0;
            $errors = [];

            foreach ($shipments as $shipment) {
                if ($shipment->carrier->code === 'shiprocket' && $shipment->shiprocket_shipment_id) {
                    $result = $this->shipRocketService->generateLabel($shipment->shiprocket_shipment_id);
                    if ($result) {
                        $generated++;
                    } else {
                        $errors[] = "Failed to generate label for shipment {$shipment->shipment_number}";
                    }
                } else {
                    $errors[] = "Label generation not supported for shipment {$shipment->shipment_number}";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$generated} labels.",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk label generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate labels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipment tracking data
     */
    public function tracking(OrderShipment $shipment)
    {
        try {
            if ($shipment->tracking_number && $shipment->carrier->code === 'shiprocket') {
                $trackingData = $this->shipRocketService->trackShipment($shipment->tracking_number);
                
                return response()->json([
                    'success' => true,
                    'data' => $trackingData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tracking data available'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Tracking data fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking data'
            ], 500);
        }
    }

    /**
     * Get orders ready for shipment
     */
    public function readyOrders()
    {
        $orders = Order::with(['user', 'address'])
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->whereDoesntHave('shipments')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.shipments.ready-orders', compact('orders'));
    }
}
