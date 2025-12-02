<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateShippingTrackingJob;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\Log;

class UpdateShippingTracking extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shipping:update-tracking {--limit=50 : Maximum number of shipments to process}';

    /**
     * The console command description.
     */
    protected $description = 'Update shipping tracking information for active shipments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("Starting shipping tracking update (limit: {$limit})...");
        
        try {
            // Get active shipments that need tracking updates
            $activeShipments = OrderShipment::whereIn('status', [
                'pending', 'picked_up', 'in_transit', 'out_for_delivery'
            ])
            ->where('updated_at', '<=', now()->subMinutes(30)) // Not updated in last 30 minutes
            ->limit($limit)
            ->get();
            
            if ($activeShipments->isEmpty()) {
                $this->info('No shipments require tracking updates.');
                return 0;
            }
            
            $this->info("Found {$activeShipments->count()} shipments to update.");
            
            // Dispatch update job for each shipment
            foreach ($activeShipments as $shipment) {
                UpdateShippingTrackingJob::dispatch($shipment);
                $this->line("Queued tracking update for: {$shipment->tracking_number}");
            }
            
            $this->info('All tracking update jobs have been queued successfully!');
            
            Log::info('Shipping tracking update command completed', [
                'shipments_processed' => $activeShipments->count(),
                'limit' => $limit
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to update shipping tracking: ' . $e->getMessage());
            
            Log::error('Shipping tracking update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}