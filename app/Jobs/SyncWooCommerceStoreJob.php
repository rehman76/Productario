<?php

namespace App\Jobs;

use App\Connectors\WooCommerceConnector;
use App\Services\Constants;
use App\Services\HelperService;
use App\Services\WooCommerceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWooCommerceStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $publication;

    public function __construct($publication)
    {
        $this->onQueue('connector');
        $this->publication = $publication;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WooCommerceService $wooCommerceService, WooCommerceConnector $wooCommerceConnector)
    {
        $productUpdateData = [];

        if (!isset($this->publication->woo_id)) {

            $response = $wooCommerceConnector->createProduct($this->publication);

            if (isset($response->id)) {
                $this->publication->update([
                    'woo_id' => $response->id,
                    'woo_product_url' => $response->permalink
                ]);

                $message = 'Publication Successfully Created';
            } else {
                $message = $response;
            }

        } else {
            $productUpdateData = $wooCommerceService->updateProductData($this->publication);
            $response = $wooCommerceConnector->updateProduct($this->publication->woo_id, $productUpdateData);

            if (isset($response->id)) {
                $this->publication->update([
                    'woo_product_url' => $response->permalink
                ]);
                $message = 'Publication Successfully Updated';

            } else {
                $message = $response;
            }
        }

        HelperService::logSync($this->publication, $productUpdateData, $response, $message, false, Constants::ConnectorWooCommerce);

    }

}
