<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class MinimalShipmentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $order;
    
    public $tries = 3;
    public $timeout = 60;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        // Just update the order status - no shipment creation
        $this->order->update([
            'status' => 'processing',
            'notes' => array_merge($this->order->notes ?? [], [
                'minimal_shipment_processing' => [
                    'processed_at' => now(),
                    'job_type' => 'minimal_localhost_test'
                ]
            ])
        ]);
    }
}