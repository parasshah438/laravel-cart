<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService; // Assuming you have a CartService to handle cart operations
use App\Services\RazorpayService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Country; // Assuming you have a Country model to fetch countries
use App\Models\Order;
use App\Models\UserAddress;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;


class CheckoutController extends Controller
{
    protected $cart;
    protected $razorpayService;
    protected $paymentService;

    public function __construct(CartService $cart, RazorpayService $razorpayService, PaymentService $paymentService)
    {
        $this->cart = $cart;
        $this->razorpayService = $razorpayService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        // Logic to display the checkout page
        $user = auth()->user();
        $cartItems = $this->cart->getCartItems(); // Get all cart items
        $savedItems = $this->cart->getCartItems(true); // true = saved items
        $countries = Country::all(); // Assuming you have a Country model to fetch countries
        
        // ✅ GET CART TOTALS AND APPLIED COUPON FOR CHECKOUT
        $cart = $this->cart->getCurrentCart();
        $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price_at_time);
        $appliedCoupon = $cart->appliedCoupon;
        $discount = $appliedCoupon ? $appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;
        
        // Check if cart is empty
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty. Add items to proceed to checkout.');
        }

        // Get user addresses for delivery
        $addresses = UserAddress::with(['country', 'state', 'city'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        // Get delivery dates (today, tomorrow, and future dates)
        $deliveryDates = $this->getAvailableDeliveryDates();

        // Get shipping methods with time slots
        $shippingMethods = $this->getShippingMethods();

        return view('checkout.index', compact(
            'cartItems', 
            'savedItems', 
            'countries', 
            'user',
            'cart',
            'subtotal',
            'discount',
            'total',
            'appliedCoupon',
            'addresses',
            'deliveryDates',
            'shippingMethods'
        ));
    }

    public function processPayment(Request $request)
    {
        // Logic to process payment
        // Validate request, handle payment gateway integration, etc.
        
        return redirect()->route('front.index')->with('success', 'Payment processed successfully!');
    }

    public function orderSummary()
    {
        // Logic to display order summary
        return view('checkout.summary');
    }

        /**
         * Place order (COD and Razorpay)
         */
    public function placeOrder(Request $request)
    {
        // Debug: Log all request data
        \Log::info('Checkout form data:', $request->all());
        
        // Debug: Log all request data
        Log::info('Checkout form submission - All request data:', $request->all());
        Log::info('Checkout form submission - Specific fields:', [
            'delivery_date' => $request->input('delivery_date'),
            'custom_delivery_date' => $request->input('custom_delivery_date'),
            'shipping_method' => $request->input('shipping_method'),
            'time_slot' => $request->input('time_slot'),
            'address_id' => $request->input('address_id'),
            'payment_method' => $request->input('payment_method'),
            'debug_delivery_date' => $request->input('debug_delivery_date'),
            'debug_shipping_method' => $request->input('debug_shipping_method'),
            'debug_time_slot' => $request->input('debug_time_slot'),
            'debug_address_id' => $request->input('debug_address_id'),
        ]);

        // Check if payment_method is missing
        if (!$request->has('payment_method') || $request->input('payment_method') === null) {
            Log::warning('Payment method is missing from request', [
                'has_payment_method' => $request->has('payment_method'),
                'payment_method_value' => $request->input('payment_method'),
                'all_request_keys' => array_keys($request->all())
            ]);
            
            return back()->withErrors([
                'payment_method' => 'Please select a payment method (COD or Online Payment).'
            ])->withInput();
        }

        // Handle custom date: if custom date is selected, use the custom_delivery_date value
        $actualDeliveryDate = $request->delivery_date;
        if ($request->delivery_date === '' && $request->custom_delivery_date) {
            $actualDeliveryDate = $request->custom_delivery_date;
        }

        // Validate delivery options
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'shipping_method' => 'required|in:morning,standard,express,midnight',
            'time_slot' => 'required|string',
            'payment_method' => 'required|in:cod,razorpay',
            'delivery_instructions' => 'nullable|string|max:500',
        ]);
        
        // Validate delivery date separately since it can come from two sources
        if (!$actualDeliveryDate) {
            return back()->withErrors(['delivery_date' => 'Please select a delivery date.'])->withInput();
        }

        $user = auth()->user();
        $cartItems = $this->cart->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
        }

        // Validate delivery date is not in the past
        $deliveryDate = Carbon::parse($actualDeliveryDate);
        if ($deliveryDate->isPast()) {
            return back()->withErrors(['delivery_date' => 'Please select a future delivery date']);
        }

        // Validate time slot for selected shipping method
        $timeSlots = $this->getTimeSlots($request->shipping_method);
        if (!in_array($request->time_slot, $timeSlots)) {
            return back()->withErrors(['time_slot' => 'Invalid time slot for selected shipping method']);
        }

        // Get selected address
        $address = $user->addresses()->findOrFail($request->address_id);

        // ✅ GET CART WITH APPLIED COUPON
        $cart = $this->cart->getCurrentCart();
        $appliedCoupon = $cart->appliedCoupon;
        
        // Calculate totals with proper coupon logic
        $subtotal = $cartItems->sum(function($item) { 
            return $item->price_at_time * $item->quantity; 
        });
        
        $discount = 0;
        $couponCode = null;
        $couponTitle = null;
        
        if ($appliedCoupon) {
            $discount = $appliedCoupon->calculateDiscount($subtotal);
            $couponCode = $appliedCoupon->code;
            $couponTitle = $appliedCoupon->title;
            
            // Update coupon usage count
            $appliedCoupon->increment('used_count');
        }
        
        // Add shipping cost
        $shippingCost = $this->getShippingCost($request->shipping_method);
        $grandTotal = $subtotal - $discount + $shippingCost;

        // Handle payment method
        if ($request->payment_method === 'razorpay') {
            return $this->initiateRazorpayPayment($request, $user, $address, $cartItems, $grandTotal, [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'coupon_code' => $couponCode,
                'coupon_title' => $couponTitle,
                'delivery_date' => $deliveryDate,
                'shipping_method' => $request->shipping_method,
                'time_slot' => $request->time_slot,
                'delivery_instructions' => $request->delivery_instructions,
            ]);
        }

        // ✅ CREATE ORDER WITH COUPON AND DELIVERY DATA (COD)
        $order = $user->orders()->create([
            'address_id' => $address->id,
            'order_number' => 'ORD' . strtoupper(uniqid()),
            'total' => $subtotal,
            'discount' => $discount,
            'shipping_cost' => $shippingCost,
            'grand_total' => $grandTotal,
            'delivery_date' => $deliveryDate,
            'shipping_method' => $request->shipping_method,
            'time_slot' => $request->time_slot,
            'delivery_instructions' => $request->delivery_instructions,
            'coupon_code' => $couponCode,
            'coupon_title' => $couponTitle,
            'coupon_discount' => $discount,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'notes' => null,
        ]);

        // Create order items with cart prices (preserving cart-time pricing)
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'price' => $item->price_at_time, // Use cart-time price
                'quantity' => $item->quantity,
                'total' => $item->price_at_time * $item->quantity,
            ]);
        }

        // ✅ CREATE PAYMENT RECORD FOR COD
        $this->paymentService->createCODPayment($order);

        // ✅ SEND ORDER CONFIRMATION EMAIL (Professional Amazon/Flipkart Style)
        try {
            Mail::to($user->email)->queue(new OrderConfirmation($order));
            Log::info('Order confirmation email queued successfully', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            // Log email error but don't fail the order
            Log::error('Order confirmation email failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Clear cart and remove applied coupon
        $this->cart->clear();

        return redirect()->route('checkout.thankyou', ['order' => $order->id])
            ->with('success', 'Order placed successfully! Check your email for order confirmation.');
    }

        /**
     * Show the details for a specific order (only for the order owner)
     */
    public function orderDetails($orderId)
    {
        $order = auth()->user()->orders()->with(['items.product', 'address'])->findOrFail($orderId);
        return view('checkout.order-details', compact('order'));
    }

    /**
     * Thank you page after order placement
     */
    public function thankYou($orderId = null)
    {
        $order = null;
        
        if ($orderId && auth()->check()) {
            $order = auth()->user()->orders()->with(['items.product', 'address'])->find($orderId);
        }
        
        return view('checkout.thankyou', compact('order'));
    }

    /**
     * Show order history/listing page
     */
    public function orderHistory(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->orders()->with(['items.product', 'address'])
                     ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Search by order number
        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }
        
        $orders = $query->paginate(10);
        $orderStatuses = ['all', 'pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        
        return view('orders.index', compact('orders', 'orderStatuses'));
    }

    /**
     * Track specific order with detailed timeline
     */
    public function trackOrder($orderId)
    {
        $order = auth()->user()->orders()->with(['items.product', 'address'])->findOrFail($orderId);
        
        // Get tracking timeline from the model
        $timeline = $order->getTrackingSteps();
        
        return view('orders.track', compact('order', 'timeline'));
    }

    /**
     * Cancel an order (if cancellable)
     */
    public function cancelOrder($orderId)
    {
        $order = auth()->user()->orders()->findOrFail($orderId);
        
        // Check if order can be cancelled
        if (in_array($order->status, ['delivered', 'cancelled', 'shipped'])) {
            return back()->with('error', 'Order cannot be cancelled at this stage.');
        }
        
        $order->update(['status' => 'cancelled']);
        
        return back()->with('success', 'Order has been cancelled successfully.');
    }

    /**
     * Reorder - add all items from previous order to cart
     */
    public function reorder($orderId)
    {
        $order = auth()->user()->orders()->with('items.product')->findOrFail($orderId);
        
        $addedItems = 0;
        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock > 0) {
                $this->cart->add($item->product, $item->quantity);
                $addedItems++;
            }
        }
        
        if ($addedItems > 0) {
            return redirect()->route('cart.view')->with('success', "{$addedItems} items added to cart from your previous order.");
        } else {
            return back()->with('error', 'No items could be added to cart. Products may be out of stock.');
        }
    }

    /**
     * Update order status (admin functionality for testing)
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($orderId);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully to ' . ucfirst($request->status));
    }

    /**
     * Process order return request
     */
    public function returnOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'items' => 'array',
            'items.*' => 'exists:order_items,id'
        ]);

        $order = auth()->user()->orders()->findOrFail($orderId);
        
        // Check if order can be returned (delivered within return period)
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Only delivered orders can be returned.');
        }
        
        // Check return period (30 days)
        if ($order->updated_at->diffInDays(now()) > 30) {
            return back()->with('error', 'Return period has expired. Orders can only be returned within 30 days of delivery.');
        }

        // Create return request - properly handle notes array
        $currentNotes = $order->notes ?? [];
        $currentNotes['return_request'] = [
            'requested_at' => now()->format('Y-m-d H:i:s'),
            'reason' => $request->reason,
            'status' => 'pending',
            'requested_by' => auth()->id()
        ];
        
        $order->update([
            'notes' => $currentNotes
        ]);

        return back()->with('success', 'Return request submitted successfully. We will contact you within 2-3 business days.');
    }

    /**
     * Process order exchange request
     */
    public function exchangeOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'items' => 'required|array',
            'items.*' => 'exists:order_items,id',
            'exchange_reason' => 'required|string|max:500'
        ]);

        $order = auth()->user()->orders()->findOrFail($orderId);
        
        // Check if order can be exchanged
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Only delivered orders can be exchanged.');
        }
        
        // Check exchange period (15 days)
        if ($order->updated_at->diffInDays(now()) > 15) {
            return back()->with('error', 'Exchange period has expired. Orders can only be exchanged within 15 days of delivery.');
        }

        // Create exchange request - properly handle notes array
        $currentNotes = $order->notes ?? [];
        $currentNotes['exchange_request'] = [
            'requested_at' => now()->format('Y-m-d H:i:s'),
            'reason' => $request->reason,
            'exchange_reason' => $request->exchange_reason,
            'status' => 'pending',
            'requested_by' => auth()->id()
        ];
        
        $order->update([
            'notes' => $currentNotes
        ]);

        return back()->with('success', 'Exchange request submitted successfully. We will contact you within 2-3 business days.');
    }

    /**
     * Download order invoice
     */
    public function downloadInvoice($orderId)
    {
        $order = auth()->user()->orders()->with(['items.product', 'address'])->findOrFail($orderId);
        
        // Generate PDF invoice (you would use a PDF library like DomPDF or wkhtmltopdf)
        // For now, return a view that can be printed
        return view('orders.invoice', compact('order'));
    }

    /**
     * Download order receipt
     */
    public function downloadReceipt($orderId)
    {
        $order = auth()->user()->orders()->with(['items.product', 'address'])->findOrFail($orderId);
        
        // Generate PDF receipt
        return view('orders.receipt', compact('order'));
    }

    /**
     * Get available delivery time slots for shipping method
     */
    public function getTimeSlots($shippingMethod)
    {
        $timeSlots = [
            'morning' => ['06:00-09:00', '09:00-12:00'],
            'standard' => ['12:00-17:00', '17:00-21:00'],
            'express' => ['09:00-12:00', '12:00-17:00', '17:00-21:00'],
            'midnight' => ['21:00-23:59', '00:00-06:00'],
        ];

        return $timeSlots[$shippingMethod] ?? [];
    }

    /**
     * Get available delivery dates
     */
    private function getAvailableDeliveryDates()
    {
        $dates = [];
        $today = Carbon::today();
        
        // Add today if it's before 6 PM
        if (Carbon::now()->hour < 18) {
            $dates[] = [
                'date' => $today->format('Y-m-d'),
                'label' => 'Today',
                'formatted' => $today->format('M d, Y')
            ];
        }

        // Add tomorrow
        $tomorrow = $today->copy()->addDay();
        $dates[] = [
            'date' => $tomorrow->format('Y-m-d'),
            'label' => 'Tomorrow',
            'formatted' => $tomorrow->format('M d, Y')
        ];

        // Add next 7 days
        for ($i = 2; $i <= 8; $i++) {
            $date = $today->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('l'),
                'formatted' => $date->format('M d, Y')
            ];
        }

        return $dates;
    }

    /**
     * Get shipping methods with details
     */
    private function getShippingMethods()
    {
        return [
            'morning' => [
                'name' => 'Morning Delivery',
                'description' => 'Delivered between 6 AM - 12 PM',
                'cost' => 50,
                'icon' => 'fas fa-sun'
            ],
            'standard' => [
                'name' => 'Standard Delivery',
                'description' => 'Delivered between 12 PM - 9 PM',
                'cost' => 25,
                'icon' => 'fas fa-clock'
            ],
            'express' => [
                'name' => 'Express Delivery',
                'description' => 'Delivered between 9 AM - 9 PM (Same day)',
                'cost' => 100,
                'icon' => 'fas fa-bolt'
            ],
            'midnight' => [
                'name' => 'Midnight Delivery',
                'description' => 'Delivered between 9 PM - 6 AM',
                'cost' => 75,
                'icon' => 'fas fa-moon'
            ]
        ];
    }

    /**
     * Get shipping cost for method
     */
    private function getShippingCost($shippingMethod)
    {
        $costs = [
            'morning' => 50,
            'standard' => 25,
            'express' => 100,
            'midnight' => 75,
        ];

        return $costs[$shippingMethod] ?? 25;
    }

    // ================================================================================================
    // 💳 RAZORPAY PAYMENT METHODS
    // ================================================================================================

    /**
     * Initiate Razorpay payment
     */
    private function initiateRazorpayPayment($request, $user, $address, $cartItems, $grandTotal, $orderData)
    {
        try {
            Log::info('Initiating Razorpay payment', [
                'user_id' => $user->id,
                'total' => $grandTotal,
                'items_count' => $cartItems->count()
            ]);

            // Create order in database first (with pending payment status)
            $order = $user->orders()->create([
                'address_id' => $address->id,
                'order_number' => 'ORD' . strtoupper(uniqid()),
                'total' => $orderData['subtotal'],
                'discount' => $orderData['discount'],
                'shipping_cost' => $orderData['shipping_cost'],
                'grand_total' => $grandTotal,
                'delivery_date' => $orderData['delivery_date'],
                'shipping_method' => $orderData['shipping_method'],
                'time_slot' => $orderData['time_slot'],
                'delivery_instructions' => $orderData['delivery_instructions'],
                'coupon_code' => $orderData['coupon_code'],
                'coupon_title' => $orderData['coupon_title'],
                'coupon_discount' => $orderData['discount'],
                'status' => 'pending',
                'payment_method' => 'razorpay',
                'payment_status' => 'pending',
                'notes' => null,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'price' => $item->price_at_time,
                    'quantity' => $item->quantity,
                    'total' => $item->price_at_time * $item->quantity,
                ]);
            }

            // Create Razorpay order
            $razorpayOrder = $this->razorpayService->createOrder(
                $grandTotal, // Amount in INR
                'INR',
                $order->order_number,
                [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_phone' => $user->phone ?? $address->phone_number,
                ]
            );

            // Store Razorpay order ID in our order
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
                'notes' => json_encode([
                    'razorpay_order_data' => $razorpayOrder,
                    'payment_initiated_at' => now()->toISOString()
                ])
            ]);

            // ✅ CREATE PAYMENT RECORD FOR RAZORPAY
            $this->paymentService->createRazorpayPayment($order, $razorpayOrder['id']);

            Log::info('Razorpay order created successfully', [
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id']
            ]);

            // Get Razorpay configuration for frontend
            $razorpayConfig = $this->razorpayService->getConfig();
            $razorpayConfig['order_id'] = $razorpayOrder['id'];
            $razorpayConfig['amount'] = $razorpayOrder['amount']; // Amount in paise
            $razorpayConfig['prefill'] = [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->phone ?? $address->phone_number,
            ];
            $razorpayConfig['notes'] = $razorpayOrder['notes'];

            // Store order data in session for payment completion
            Session::put('pending_order_id', $order->id);
            Session::put('razorpay_order_id', $razorpayOrder['id']);

            // Return payment view with Razorpay configuration
            return view('checkout.payment', [
                'order' => $order,
                'razorpayConfig' => $razorpayConfig,
                'user' => $user,
                'address' => $address
            ]);

        } catch (Exception $e) {
            Log::error('Razorpay payment initiation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Payment initialization failed. Please try again or use COD.');
        }
    }

    /**
     * Handle Razorpay payment success
     */
    public function razorpaySuccess(Request $request)
    {
        try {
            Log::info('Razorpay payment success callback', $request->all());

            $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Get order from session
            $orderId = Session::get('pending_order_id');
            $sessionRazorpayOrderId = Session::get('razorpay_order_id');

            if (!$orderId || $sessionRazorpayOrderId !== $request->razorpay_order_id) {
                throw new Exception('Invalid order session data');
            }

            $order = Order::findOrFail($orderId);

            // Verify payment signature
            $isSignatureValid = $this->razorpayService->verifyPaymentSignature(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature
            );

            if (!$isSignatureValid) {
                throw new Exception('Invalid payment signature');
            }

            // Fetch payment details from Razorpay
            $paymentDetails = $this->razorpayService->fetchPayment($request->razorpay_payment_id);

            Log::info('Payment verification successful', [
                'order_id' => $order->id,
                'payment_id' => $request->razorpay_payment_id,
                'payment_details' => $paymentDetails
            ]);

            // ✅ UPDATE PAYMENT RECORD WITH SUCCESS DATA
            $payment = $this->paymentService->findByGatewayOrderId($request->razorpay_order_id);
            if ($payment) {
                $this->paymentService->markPaymentAsSuccessful($payment, [
                    'gateway_payment_id' => $request->razorpay_payment_id,
                    'transaction_id' => $paymentDetails['id'] ?? null,
                    'method' => $paymentDetails['method'] ?? null,
                    'payment_method' => $paymentDetails['method'] ?? null,
                    'gateway_response' => $paymentDetails,
                ]);

                // Update payment method details from gateway response
                $this->paymentService->updatePaymentMethodFromGateway($payment, $paymentDetails);
            }

            // Update order with payment success
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'notes' => json_encode([
                    'razorpay_payment_data' => $paymentDetails,
                    'payment_completed_at' => now()->toISOString(),
                    'payment_method_details' => $paymentDetails['method'] ?? 'unknown'
                ])
            ]);

            // Send order confirmation email
            try {
                Mail::to(auth()->user()->email)->queue(new OrderConfirmation($order));
                Log::info('Order confirmation email queued for Razorpay payment', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::error('Order confirmation email failed for Razorpay payment', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Clear cart and session
            $this->cart->clear();
            Session::forget(['pending_order_id', 'razorpay_order_id']);

            Log::info('Razorpay payment completed successfully', [
                'order_id' => $order->id,
                'payment_id' => $request->razorpay_payment_id
            ]);

            return redirect()->route('checkout.thankyou', ['order' => $order->id])
                ->with('success', 'Payment successful! Your order has been confirmed.');

        } catch (Exception $e) {
            Log::error('Razorpay payment success handling failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Try to get order for failure handling
            $orderId = Session::get('pending_order_id');
            $sessionRazorpayOrderId = Session::get('razorpay_order_id');
            
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    // ✅ UPDATE PAYMENT RECORD WITH FAILURE DATA
                    $payment = $this->paymentService->findByGatewayOrderId($sessionRazorpayOrderId);
                    if ($payment) {
                        $this->paymentService->markPaymentAsFailed($payment, [
                            'reason' => 'Payment verification failed: ' . $e->getMessage(),
                            'gateway_response' => $request->all(),
                        ]);
                    }

                    $order->update([
                        'payment_status' => 'failed',
                        'notes' => json_encode([
                            'payment_failure_reason' => $e->getMessage(),
                            'payment_failed_at' => now()->toISOString()
                        ])
                    ]);
                }
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Razorpay payment failure
     */
    public function razorpayFailure(Request $request)
    {
        try {
            Log::warning('Razorpay payment failure callback', $request->all());

            // Get order from session
            $orderId = Session::get('pending_order_id');
            $sessionRazorpayOrderId = Session::get('razorpay_order_id');
            
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    // ✅ UPDATE PAYMENT RECORD WITH FAILURE DATA
                    $payment = $this->paymentService->findByGatewayOrderId($sessionRazorpayOrderId);
                    if ($payment) {
                        $this->paymentService->markPaymentAsFailed($payment, [
                            'reason' => $request->input('error.description', 'Payment failed'),
                            'gateway_response' => $request->all(),
                        ]);
                    }

                    $order->update([
                        'payment_status' => 'failed',
                        'notes' => json_encode([
                            'payment_failure_data' => $request->all(),
                            'payment_failed_at' => now()->toISOString()
                        ])
                    ]);
                }
            }

            // Clear session data
            Session::forget(['pending_order_id', 'razorpay_order_id']);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment was unsuccessful. Please try again or choose a different payment method.');

        } catch (Exception $e) {
            Log::error('Razorpay payment failure handling error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * Handle Razorpay webhook
     */
    public function razorpayWebhook(Request $request)
    {
        try {
            Log::info('Razorpay webhook received', [
                'event' => $request->input('event'),
                'payment_id' => $request->input('payload.payment.entity.id'),
                'order_id' => $request->input('payload.payment.entity.order_id')
            ]);

            // Verify webhook signature
            $signature = $request->header('X-Razorpay-Signature');
            $payload = $request->getContent();
            
            if (!$this->razorpayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Razorpay webhook signature verification failed');
                return response('Unauthorized', 401);
            }

            $event = $request->input('event');
            $paymentEntity = $request->input('payload.payment.entity');

            // Handle different webhook events
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($paymentEntity);
                    break;
                    
                case 'payment.failed':
                    $this->handlePaymentFailed($paymentEntity);
                    break;
                    
                case 'order.paid':
                    $this->handleOrderPaid($paymentEntity);
                    break;

                default:
                    Log::info('Unhandled Razorpay webhook event', ['event' => $event]);
            }

            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('Razorpay webhook handling failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle payment captured webhook
     */
    private function handlePaymentCaptured($paymentEntity)
    {
        $orderId = $paymentEntity['order_id'] ?? null;
        $paymentId = $paymentEntity['id'] ?? null;

        if (!$orderId || !$paymentId) {
            Log::warning('Missing order_id or payment_id in webhook', $paymentEntity);
            return;
        }

        $order = Order::where('razorpay_order_id', $orderId)->first();
        if (!$order) {
            Log::warning('Order not found for webhook', ['razorpay_order_id' => $orderId]);
            return;
        }

        // Update order if not already updated
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'razorpay_payment_id' => $paymentId,
                'notes' => json_encode([
                    'webhook_payment_captured' => $paymentEntity,
                    'payment_captured_at' => now()->toISOString()
                ])
            ]);

            Log::info('Order updated via webhook', ['order_id' => $order->id, 'payment_id' => $paymentId]);
        }
    }

    /**
     * Handle payment failed webhook
     */
    private function handlePaymentFailed($paymentEntity)
    {
        $orderId = $paymentEntity['order_id'] ?? null;
        $paymentId = $paymentEntity['id'] ?? null;

        if (!$orderId) {
            Log::warning('Missing order_id in failed payment webhook', $paymentEntity);
            return;
        }

        $order = Order::where('razorpay_order_id', $orderId)->first();
        if (!$order) {
            Log::warning('Order not found for failed payment webhook', ['razorpay_order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => 'failed',
            'notes' => json_encode([
                'webhook_payment_failed' => $paymentEntity,
                'payment_failed_at' => now()->toISOString()
            ])
        ]);

        Log::info('Order marked as failed via webhook', ['order_id' => $order->id, 'payment_id' => $paymentId]);
    }

    /**
     * Handle order paid webhook
     */
    private function handleOrderPaid($paymentEntity)
    {
        // Similar to payment captured but for order-level events
        $this->handlePaymentCaptured($paymentEntity);
    }
}    