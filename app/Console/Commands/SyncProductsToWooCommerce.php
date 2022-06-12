<?php

namespace App\Console\Commands;

use App\Jobs\PushingProductToWoCommerceStoreJob;
use App\Publication;
use App\Services\ProductService;
use App\Services\WooCommerceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProductsToWooCommerce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export_products_to_woo_commerce';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductService $productService,
                                    WooCommerceService $wooCommerceService)
    {
        $this->productService = $productService;
        $this->wooCommerceService = $wooCommerceService;

        parent::__construct();
    }

    /**,
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//       $lastSyncTime =  nova_get_setting('woo_commerce_last_sync');

        // Check if the nova sync config set
        // On sync data after each frequency interval
//        if (nova_get_setting('woo_commerce_frequency') && (!$lastSyncTime ||
//            Carbon::now()->greaterThanOrEqualTo(Carbon::parse($lastSyncTime)->addMinutes(nova_get_setting('woo_commerce_frequency')))))
//        {
        $products = $this->productService->all();

        $data = [];
        foreach ($products as $product) {
            if (!$product->woo_id) {
//                     create product in woo commerce

                PushingProductToWoCommerceStoreJob::dispatch($product);

            }
        }
//                elseif($product->price && $product->quantity) {
//                    // update product in woo commerce
//                    $data['update'][] = $this->wooCommerceService->updateProductData($product);
//                }
//            }

//            if (isset($data['update']) && $data['update'])
//            {
//                for ($offset =0 ; $offset < count($data['update']) ; $offset = $offset+90 )
//                {
//                    $dataArray['update'] = array_slice($data['update'], $offset, 90);
//                    $this->wooCommerceService->bulkProductUpdate($dataArray);
//                }
//            }

//            nova_set_setting_value('woo_commerce_last_sync', Carbon::now());
//
//        }

    }
}
