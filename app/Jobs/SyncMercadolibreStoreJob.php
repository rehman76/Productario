<?php

namespace App\Jobs;

use App\Connectors\MercadolibreConnector;
use App\Services\Constants;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMercadolibreStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @param $product
     * @return void
     */

    protected $product, $mercadolibreConnector, $isPremiumProduct, $price, $mlaId;

    public function __construct($product, $isPremiumProduct= false)
    {
        $this->onQueue('connector');
        $this->product = $product;
        $this->isPremiumProduct = $isPremiumProduct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->mercadolibreConnector = new MercadolibreConnector();

        if ($this->product)
        {
            $this->price = $this->isPremiumProduct ? $this->product->premiumProduct->price : $this->product->price;
            $this->mlaId =  $productUpdateData['mla_id'] =$this->isPremiumProduct ? $this->product->premiumProduct->mla_id : $this->product->mla;
            $mercadolibreProduct = $this->get($this->product);
            $mercadolibreProduct->successful() ?
                        $this->update($this->product, $mercadolibreProduct) :
                    HelperService::logSync($this->product, $productUpdateData, $mercadolibreProduct, 'Error on getting the MLA Product',
                                        $this->isPremiumProduct, Constants::ConnectorMercadolibre );;
        }
    }

    public function get($product)
    {
        // Get product details from mercadolibre
        $mercadolibreProduct =$this->mercadolibreConnector->getProduct($this->mlaId);
        if ($mercadolibreProduct->successful() && !$this->isPremiumProduct)
        {
            $product->attributes = isset($mercadolibreProduct['attributes']) ? HelperService::createAttributesColumnFormat($mercadolibreProduct['attributes']) : null;
            $product->save();
        }

        return $mercadolibreProduct;
    }

    public function update($product, $mercadolibreProduct)
    {
        // Update Publication on the store
        $productUpdateData = [];
        $response = null;
        $message = 'No sync data Or product status under review';
        $productUpdateData = HelperService::setStorePriceAndQuantity($product->is_bundle,$this->price, $product->quantity , [
            'storeSettingField' => 'mercadolibre_sync_update_fields',
            'storeModifierFieldForPrice'  => 'mla_price_modifier',
            'storeModifierFieldForQuantity' => 'mla_stock_modifier',
            'productUpdateData' => $productUpdateData,
            'storeQuantityField' =>  'available_quantity',
            'publication_minimum_price' => $product->minimum_price
        ], $this->isPremiumProduct);

        // make execption BatePrecios vendor product, it stock will remain same and will not apply the stock modifier part
        $productUpdateData = $this->sendStockQuantityUnChangedInCaseBatepreciosProduct($product, $productUpdateData);


        $productUpdateData['status'] = $product->mla_status && $product->active && $product->status && (isset($productUpdateData['available_quantity']) &&
            $productUpdateData['available_quantity']) ? 'active' : 'paused';

        // mercadolibre not allows to update the if product status under review
        if ($productUpdateData && $mercadolibreProduct['status']!='under_review')
        {
            if (isset($mercadolibreProduct['variations']) && !$mercadolibreProduct['variations'])
            {
                //todo refactor this part
                //only update when we have active status
                $response =  $this->mercadolibreConnector->updateProduct($this->mlaId, $productUpdateData);
                $productUpdateData =  $this->skuUpdate($product, $mercadolibreProduct, $productUpdateData);

                $message = 'Publication updated';
            } elseif (isset($mercadolibreProduct['variations'])) {
                /// update the variation for the product
                $response =  $this->mercadolibreConnector->updateProduct($this->mlaId, [
                    'status' => $productUpdateData['status']
                ]);
                $this->mercadolibreConnector->updateProductVariation($this->mlaId, $mercadolibreProduct['variations'][0]['id'], $productUpdateData);

                $message = 'Publication updated with variation';
            }
        }

        $productUpdateData['mla_id'] = $this->mlaId;

        HelperService::logSync($product, $productUpdateData, $response, $message . ' (MLA status: '. $mercadolibreProduct['status'] .')',
                                        $this->isPremiumProduct, Constants::ConnectorMercadolibre);
    }

    public function skuUpdate($product, $mercadolibreProduct, $productUpdateData)
    {
        if (in_array('sku' ,nova_get_setting('mercadolibre_sync_update_fields')))
        {
            $this->mercadolibreConnector->updateProductSku($product, $product->sku, $mercadolibreProduct);
            $productUpdateData['sku'] = $product->sku;
        }

        return $productUpdateData;
    }

    public function sendStockQuantityUnChangedInCaseBatepreciosProduct($product, $productUpdateData)
    {
        if($product->winner_vendor_product_id && $product->vendorproductwinner()->first()->vendor_id == Constants::BatepreciosVendorId)
        {
            $productUpdateData['available_quantity'] = $product->quantity;
        }

        return $productUpdateData;
    }
}
