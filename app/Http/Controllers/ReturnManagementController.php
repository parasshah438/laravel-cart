<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ReturnManagementController extends Controller
{
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
}