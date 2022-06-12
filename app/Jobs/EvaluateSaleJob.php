<?php

namespace App\Jobs;

use App\Connectors\MercadolibreConnector;
use App\Services\SaleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateSaleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $order, $topic;

    public function __construct($order, $topic)
    {
        $this->topic = $topic;
        $this->order = $order;
        $this->onQueue('sale');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order && $this->topic == 'orders_v2') {
            $mercadolibreConnector = new MercadolibreConnector();
            SaleService::saleOrder($mercadolibreConnector->getOrder($this->order));
        }
    }
}
