<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use Carbon\Carbon;

class CleanOldCarts extends Command
{
    protected $signature = 'carts:clean';

    protected $description = 'Delete guest carts (no user) that are older than 7 days';

    public function handle()
    {
        $threshold = Carbon::now()->subDays(7);

        $oldCarts = Cart::whereNull('user_id')
            ->where('updated_at', '<', $threshold)
            ->get();

        $count = 0;

        foreach ($oldCarts as $cart) {
            $cart->items()->delete();
            $cart->delete();
            $count++;
        }

        $this->info("Cleaned $count guest carts older than 7 days.");
    }
}
