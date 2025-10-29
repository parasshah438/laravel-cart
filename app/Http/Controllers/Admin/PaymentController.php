<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display payment analytics dashboard
     */
    public function dashboard(Request $request)
    {
        $dateRange = $request->input('date_range', '7');
        $startDate = Carbon::now()->subDays($dateRange)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Custom date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        // Get payment analytics
        $analytics = $this->paymentService->getPaymentAnalytics(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        // Get recent payments
        $recentPayments = Payment::with(['order', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get failed payments for review
        $failedPayments = Payment::with(['order', 'user'])
            ->where('payment_status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Daily payment trend for chart
        $dailyTrend = Payment::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.payments.dashboard', compact(
            'analytics',
            'recentPayments',
            'failedPayments',
            'dailyTrend',
            'startDate',
            'endDate',
            'dateRange'
        ));
    }

    /**
     * Display all payments with filters
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order', 'user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_id', 'LIKE', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'LIKE', "%{$search}%")
                               ->orWhere('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show payment details
     */
    public function show(Payment $payment)
    {
        $payment->load(['order', 'user']);
        
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Export payments to CSV
     */
    public function export(Request $request)
    {
        $query = Payment::with(['order', 'user']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'payments_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Payment ID',
                'Order Number',
                'Customer',
                'Email',
                'Gateway',
                'Method',
                'Amount',
                'Currency',
                'Status',
                'Payment Status',
                'Gateway Payment ID',
                'Transaction ID',
                'Created At',
                'Paid At',
                'Failed At',
            ]);

            // CSV data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_id,
                    $payment->order->order_number ?? '',
                    $payment->user->name ?? '',
                    $payment->user->email ?? '',
                    $payment->gateway,
                    $payment->method ?? '',
                    $payment->amount,
                    $payment->currency,
                    $payment->status,
                    $payment->payment_status,
                    $payment->gateway_payment_id ?? '',
                    $payment->transaction_id ?? '',
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : '',
                    $payment->failed_at ? $payment->failed_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get payment analytics API
     */
    public function analyticsApi(Request $request)
    {
        $dateRange = $request->input('date_range', '7');
        $startDate = Carbon::now()->subDays($dateRange)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        $analytics = $this->paymentService->getPaymentAnalytics(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return response()->json($analytics);
    }
}