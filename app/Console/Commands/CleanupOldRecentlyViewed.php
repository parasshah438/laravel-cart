<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecentlyViewedProduct;
use Carbon\Carbon;

class CleanupOldRecentlyViewed extends Command
{
    protected $signature = 'recently-viewed:cleanup';
    protected $description = 'Delete old recently viewed guest entries older than 30 days';

    public function handle(): void
    {
        $cutoff = Carbon::now()->subDays(30);

        $deleted = RecentlyViewedProduct::whereNull('user_id')
            ->where('created_at', '<', $cutoff)
            ->delete();

            
        $this->info("Deleted $deleted old guest recently viewed records.");
    }
}
