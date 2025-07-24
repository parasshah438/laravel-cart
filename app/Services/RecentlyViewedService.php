<?php
namespace App\Services;

use App\Models\RecentlyViewedProduct;
use Illuminate\Support\Facades\Session;

class RecentlyViewedService
{
    public function mergeGuestToUser(): void
    {
        if (!auth()->check()) {
            return;
        }

        $userId = auth()->id();
        $sessionId = Session::getId();

        $guestViews = RecentlyViewedProduct::where('session_id', $sessionId)->get();
        foreach ($guestViews as $view) {
            //Prevent duplication
            RecentlyViewedProduct::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $view->product_id,
                ],
                [
                    'updated_at' => now()
                ]
            );

            //Delete the guest record
            $view->delete();
        }
    }
}
