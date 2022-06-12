<?php

namespace App\Jobs;

use App\Connectors\MercadolibreConnector;
use App\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MapProductsToMercadolibreStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $products, $mercadolibreConnector;

    public function __construct($products = null)
    {
        $this->onQueue('connector');
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->mercadolibreConnector = new MercadolibreConnector();

        if ($this->products)
        {
            $this->mapSpecificProducts();
        }else {
            $this->mapAllProducts();
        }
    }

    public function mapSpecificProducts()
    {
        foreach ($this->products as $product)
        {
            if ($product->sku)
            {
                $response = $this->mercadolibreConnector->searchInUserStore('?seller_sku='.$product->sku);
                /// Do we have any results on search
                if ($response['results'])
                {
                    $product->mla = $response['results'][0];
                    $product->save();
                }
            }
        }
    }

    public function mapAllProducts()
    {
        $productCount = Publication::whereNull('mla')->count();
        for ($offset =0 ; $offset < $productCount ; $offset = $offset+20 )
        {
            $selectedProducts = Publication::whereNull('mla')->offset($offset)->limit(20)->get();
            dispatch(new MapProductsToMercadolibreStoreJob($selectedProducts));
        }

    }

}