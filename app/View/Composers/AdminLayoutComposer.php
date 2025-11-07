<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Order;

class AdminLayoutComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Get order counts for navigation badges
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $pendingCodCount = Order::where('payment_method', 'cod')
                               ->where('status', 'pending')
                               ->count();
        
        $view->with([
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingCodCount' => $pendingCodCount
        ]);
    }
}