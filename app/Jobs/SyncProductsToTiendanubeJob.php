<?php

namespace App\Jobs;

use App\Connectors\TiendanubeConnector;
use App\Services\Constants;
use App\Services\HelperService;
use App\SyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncProductsToTiendanubeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 0;

    public $maxExceptions = 3;

    public function retryUntil()
    {
        return now()->addHours(6);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $product, $tiendanubeConnector;

    public function __construct($product)
    {
        $this->onQueue('connector');
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     *
     */
    public function handle()
    {
        if ($timestamp = Cache::get('tn-remaining-time-retry')) {
            return $this->release(
                $timestamp - time()
            );
        }

        if ($this->product)
        {
            $this->tiendanubeConnector = new TiendanubeConnector();

            $this->updateProduct($this->product);
            $this->createInStore($this->product);
        }
    }

    public function createInStore($product)
    {
        if (!$product->tiendanube_id)
        {
            $productMutatedFields = HelperService::setStorePriceAndQuantity($product->is_bundle,$product->price, $product->quantity, [
                'storeSettingField' => 'tiendanube_sync_update_fields',
                'storeModifierFieldForPrice'  => 'tiendanube_price_modifier',
                'storeModifierFieldForQuantity' => 'tiendanube_stock_modifier',
                'productUpdateData' => [],
                'storeQuantityField' =>  'stock'
            ]);
            $product['price'] = $productMutatedFields['price'];
            $product['quantity'] = $productMutatedFields['stock'];

           $response = $this->tiendanubeConnector->createProduct($product);
           if (isset($response['response']['id']))
           {
               $product->tiendanube_id = $response['response']['id'];
               $product->tiendanube_product_url = $response['response']['canonical_url'];
               $product->save();

               SyncLog::create([
                   'connector_id' => Constants::ConnectorTiendanube,
                   'publication_id' => $product->id,
                   'attributes' => $response['productData'],
                   'message' => 'Publication Created'
               ]);
           } else {
               Log::error($response['response']->body());
           }

        }
    }

    public function updateProduct($product)
    {
        if ($product->tiendanube_id)
        {
            $tiendanubeProduct = $this->tiendanubeConnector->getProduct($product->tiendanube_id);

            if (isset($tiendanubeProduct['variants']))
            {
                $variantUpdatedFields = $this->productVariantUpdate($product, $tiendanubeProduct);
                $productUpdatedData =  $this->productUpdateFields($product);
                $this->setProductURL($product, $tiendanubeProduct);

                $response =  $this->tiendanubeConnector->update($product->tiendanube_id, $productUpdatedData);

                SyncLog::create([
                    'connector_id' => Constants::ConnectorTiendanube,
                    'publication_id' => $product->id,
                    'attributes' => array_merge($variantUpdatedFields, $productUpdatedData),
                    'message' => $response->successful() ?  'Publication Updated' : $response->body()
                ]);
            }else {
                Log::info('Variant not found here ->' . $product->tiendanube_id . ' '.$tiendanubeProduct->body());
            }
        }
    }

    public function productVariantUpdate($product,$tiendanubeProduct)
    {
        $productVariationUpdateData = [];
        $productVariationUpdateData['promotional_price'] = $product->sale_price ? $product->sale_price : 0;
        $productVariationUpdateData = HelperService::setStorePriceAndQuantity($product->is_bundle,$product->price, $product->quantity, [
            'storeSettingField' => 'tiendanube_sync_update_fields',
            'storeModifierFieldForPrice'  => 'tiendanube_price_modifier',
            'storeModifierFieldForQuantity' => 'tiendanube_stock_modifier',
            'productUpdateData' => $productVariationUpdateData,
            'storeQuantityField' =>  'stock'
        ]);

        if (in_array('sku' ,nova_get_setting('tiendanube_sync_update_fields')) && isset($product['sku']))
        {
            $productVariationUpdateData['sku'] = $product['sku'];
        }


        if ($productVariationUpdateData)
        {
            $this->tiendanubeConnector->updateProductVariation($product->tiendanube_id, $tiendanubeProduct['variants'][0]['id'] , $productVariationUpdateData);
        }

        return $productVariationUpdateData;
    }

    public function productUpdateFields($product)
    {
        $this->addDiscountedCategory($product);
        $productUpdatedData['published'] = $product->active && $product->status && $product->tiendanube_status ? true : false;
        $productUpdatedData = $this->addProductDescription($product->description, $productUpdatedData);
        if ($categories = $product->categories()->get()) {
            $productUpdatedData['categories'] = [];
            foreach ($categories as $category) {
                if (!$category->tiendanube_category_id) {
                    $category->tiendanube_category_id =  $this->tiendanubeConnector->createCategory($category->name);
                    $category->save();
                }
                array_push($productUpdatedData['categories'], $category->tiendanube_category_id);
            }
        }

        return $productUpdatedData;
    }

    public function addDiscountedCategory($product)
    {
        if ($product->discount && !$product->categories()->where('id', Constants::OfertaCategoryId)->exists())
        {
            $product->categories()->attach(Constants::OfertaCategoryId);
        } elseif(!$product->discount && $product->categories()->where('id', Constants::OfertaCategoryId )->exists())
        {
            $product->categories()->detach(Constants::OfertaCategoryId);
        }
    }

    public function setProductURL($product, $tiendanubeProduct)
    {
        if (!$product->tiendanube_product_url)
        {
            $product->tiendanube_product_url = $tiendanubeProduct['canonical_url'];
            $product->save();
        }
    }

    public function addProductDescription($description, $productUpdatedData)
    {
        if (in_array('description' ,nova_get_setting('tiendanube_sync_update_fields')) && $description)
        {
            $description =  HelperService::convertStingToNewLineHtmlTag($description);
            $productUpdatedData['description'] = [
                'en' => $description,
                'es' => $description
            ];
        }

        return $productUpdatedData;
    }


}
