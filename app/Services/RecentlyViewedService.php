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
        $sessionId = session()->get('cart_session_id');
        $guestViews = RecentlyViewedProduct::where('session_id', $sessionId)->get();
        
        foreach ($guestViews as $view) {
            // Check if a record already exists for this user & product
            $existing = RecentlyViewedProduct::where('user_id', $userId)
                ->where('product_id', $view->product_id)
                ->first();

            if ($existing) {
                // Record already exists for user, so discard guest record
                $view->delete();
            } else {
                // Just update the guest record to now belong to the user
                $view->update([
                    'user_id'    => $userId,
                    'session_id' => null,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
