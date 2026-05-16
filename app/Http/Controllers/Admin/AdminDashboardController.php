<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $pendingCodCount = Order::where('payment_method', 'cod')
                               ->where('status', 'pending')
                               ->count();

        return view('admin.dashboard', compact('pendingOrdersCount', 'pendingCodCount'));
    }
}
