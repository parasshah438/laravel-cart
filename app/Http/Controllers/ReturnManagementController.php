<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class ReturnManagementController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display return requests
     */
    public function index(Request $request)
    {
        $query = Order::whereNotNull('notes')
            ->where('notes', 'like', '%return_request%')
            ->with(['user', 'items.product']);

        // Filter by return status
        if ($request->has('status') && $request->status !== 'all') {
            $query->whereRaw('JSON_EXTRACT(notes, "$.return_request.status") = ?', [$request->status]);
        }

        $orders = $query->orderBy('updated_at', 'desc')->paginate(20);
        $returnStatuses = ['all', 'pending', 'approved', 'picked_up', 'completed', 'rejected', 'cancelled'];

        return view('admin.returns.index', compact('orders', 'returnStatuses'));
    }

    /**
     * Update return status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,picked_up,completed,rejected,cancelled',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $currentNotes = $order->notes ?? [];
        if (!is_array($currentNotes)) {
            $currentNotes = [];
        }

        if (!isset($currentNotes['return_request'])) {
            return back()->with('error', 'No return request found for this order.');
        }

        // Update return status
        $currentNotes['return_request']['status'] = $request->status;
        $currentNotes['return_request']['admin_updated_at'] = now()->format('Y-m-d H:i:s');
        $currentNotes['return_request']['admin_updated_by'] = auth()->id();
        
        if ($request->admin_notes) {
            $currentNotes['return_request']['admin_notes'] = $request->admin_notes;
        }

        // Add status history
        if (!isset($currentNotes['return_request']['status_history'])) {
            $currentNotes['return_request']['status_history'] = [];
        }

        $currentNotes['return_request']['status_history'][] = [
            'status' => $request->status,
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'updated_by' => auth()->id(),
            'notes' => $request->admin_notes
        ];

        $order->update(['notes' => $currentNotes]);

        // 📦 RESTORE STOCK WHEN RETURN IS COMPLETED
        if ($request->status === 'completed') {
            $this->stockService->restoreOrderStock($order);
        }

        Log::info('Return status updated by admin', [
            'order_id' => $order->id,
            'new_status' => $request->status,
            'admin_id' => auth()->id()
        ]);

        return back()->with('success', "Return status updated to {$request->status} successfully.");
    }

    /**
     * Show return details
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'address']);
        
        $returnRequest = $order->notes['return_request'] ?? null;
        if (!$returnRequest) {
            abort(404, 'No return request found for this order.');
        }

        return view('admin.returns.show', compact('order', 'returnRequest'));
    }

    /**
     * Process refund for completed return
     */
    public function processRefund(Order $order)
    {
        $currentNotes = $order->notes ?? [];
        if (!is_array($currentNotes)) {
            $currentNotes = [];
        }

        $returnRequest = $currentNotes['return_request'] ?? null;
        if (!$returnRequest || $returnRequest['status'] !== 'completed') {
            return back()->with('error', 'Refund can only be processed for completed returns.');
        }

        // Check if refund already processed
        if (isset($currentNotes['refund_status']) && $currentNotes['refund_status']['status'] === 'completed') {
            return back()->with('info', 'Refund has already been processed for this return.');
        }

        try {
            // Use RefundProcessingService
            $refundService = app(\App\Services\RefundProcessingService::class);
            
            $result = $refundService->processRefund($order);

            if ($result['success']) {
                return back()->with([
                    'success' => $result['message'],
                    'refund_details' => [
                        'method' => $result['refund_method'] ?? 'Unknown',
                        'amount' => $result['refund_amount'] ?? 0,
                        'reference' => $result['transaction_id'] ?? null,
                        'timeline' => $result['expected_timeline'] ?? 'Processing'
                    ]
                ]);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to process refund. Please try again or contact technical support.');
        }
    }

    /**
     * Update refund status (processing/completed)
     */
    public function updateRefundStatus(Request $request, Order $order)
    {
        $request->validate([
            'refund_status' => 'required|in:processing,completed',
            'transaction_id' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $currentNotes = $order->notes ?? [];
        if (!is_array($currentNotes)) {
            $currentNotes = [];
        }

        // Check if refund details exist
        if (!isset($currentNotes['refund_status'])) {
            return back()->with('error', 'No refund details found for this order.');
        }

        try {
            // Update refund status
            $currentNotes['refund_status']['status'] = $request->refund_status;
            $currentNotes['refund_status']['updated_at'] = now()->toISOString();
            $currentNotes['refund_status']['updated_by'] = auth()->id();

            if ($request->transaction_id) {
                $currentNotes['refund_status']['transaction_id'] = $request->transaction_id;
            }

            if ($request->admin_notes) {
                $currentNotes['refund_status']['admin_notes'] = $request->admin_notes;
            }

            // Set completion timestamp if completed
            if ($request->refund_status === 'completed') {
                $currentNotes['refund_status']['completed_at'] = now()->toISOString();
                
                // Set expected timeline based on method
                $method = $currentNotes['refund_status']['method'] ?? 'unknown';
                $timelines = [
                    'upi_transfer' => 'Instant to 24 hours',
                    'bank_transfer' => '1-3 business days',
                    'store_credit' => 'Instant',
                    'cheque' => '7-14 business days'
                ];
                $currentNotes['refund_status']['expected_timeline'] = $timelines[$method] ?? '1-3 business days';
            }

            // Update order
            $order->update(['notes' => $currentNotes]);

            \Log::info('Refund status updated', [
                'order_id' => $order->id,
                'status' => $request->refund_status,
                'transaction_id' => $request->transaction_id,
                'updated_by' => auth()->id()
            ]);

            $messages = [
                'processing' => 'Refund marked as processing. Customer will be notified.',
                'completed' => 'Refund marked as completed successfully!'
            ];

            return back()->with('success', $messages[$request->refund_status]);

        } catch (\Exception $e) {
            \Log::error('Refund status update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update refund status. Please try again.');
        }
    }
}
