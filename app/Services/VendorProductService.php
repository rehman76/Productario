<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 1:39 PM
 */

namespace App\Services;


use App\Jobs\EvaluateProductJob;
use App\Publication;
use App\Repositories\VendorProductRepository;
use App\VendorProductLog;
use Illuminate\Support\Facades\DB;


class VendorProductService
{
    protected $vendorProductRepository;

    /* Don't add any dependency injection here */
    public function __construct()
    {
        $this->vendorProductRepository = new VendorProductRepository();
    }

    /**
     * @param $product
     */
    public static function updateOrInsert($product)
    {
        if(!isset($product['status']))
        {
            $product['status'] = 1; // Make vendor product enabled if its coming in the feed
        }

       $vendorProduct = VendorProductRepository::updateOrCreate($product);
       self::evaluateProduct($vendorProduct);

       return $vendorProduct;
    }

    public static function updateAndEvaluate($vendorId, $sku, $product)
    {
        $vendorProductRepository = new VendorProductRepository();
        $vendorProduct = $vendorProductRepository->updateProduct($vendorId, $sku, $product);
        if($vendorProduct){
            self::evaluateProduct($vendorProduct);
        }
        return $vendorProduct;
    }

    public static function evaluateProduct($vendorProduct)
    {
        self::evaluateWinnerForConnectedPublication($vendorProduct);
        self::addVendorProductLog($vendorProduct);
        return;
    }

    /**
     * @param $vendorId
     * @param $sku
     * @return null|Publication
     */
    public function getLinkedPublication($vendorId, $sku)
    {
       $vendorProduct =  $this->vendorProductRepository->findProduct($vendorId, $sku);

        if (!$vendorProduct)
        {
            return null;
        }

        return  $vendorProduct->publications()->first();
    }

    public static function addVendorProductLog($modelInstance, $message= null)
    {
       $data = [
           'vendor_id' => $modelInstance->vendor_id,
           'vendor_product_id' => $modelInstance->id,
           'price' => $modelInstance->price,
           'stock' => $modelInstance->quantity ?? 0,
           'message' => $message,
       ];

        if($message && !$modelInstance->wasRecentlyCreated){
            VendorProductLog::create($data);
        }else{
            if (!$modelInstance->wasRecentlyCreated && ($modelInstance->wasChanged('price') ||
                    $modelInstance->wasChanged('quantity'))) {
                VendorProductLog::create($data);
            }
        }
    }

    public static function evaluateWinnerForConnectedPublication($vendorProduct)
    {
        // Is vendor product connected with any publication then evaluate winner for that publication
        if ($publication = $vendorProduct->publications()->first())
        {
            ProductService::winnerVendorProductEvaluation($publication);
        }
    }

    public function removeZombieProduct($products, $vendorInstanceId, $keyName)
    {
        $vendorProductSKUs = $this->vendorProductRepository->getActiveProductsSKUAgainstVendor($vendorInstanceId);
        $productNotFoundSkuNumbers = array_diff($vendorProductSKUs, $products->pluck($keyName)->toArray());
        if(isset($productNotFoundSkuNumbers)) {
            $this->updateMissingProductStatus($productNotFoundSkuNumbers, $vendorInstanceId);
        }

    }

    public function updateMissingProductStatus($productNotFoundSkuNumbers, $vendorId)
    {
        $vendorProductStatus = 0;
        $this->vendorProductRepository->updateMissingProductStatus($productNotFoundSkuNumbers, $vendorProductStatus);

        foreach($productNotFoundSkuNumbers as $key => $value){
            $vendorProduct = $this->vendorProductRepository->findProduct($vendorId,$value);
            self::addVendorProductLog($vendorProduct, 'Product not found in the feed, disabled');
            if ($publication = $vendorProduct->publications()->first())
            {
                EvaluateProductJob::dispatch($publication);
            }
        }

    }

    public static function updateProduct($vendorId, $sku, $product)
    {
        $vendorProductRepository = new VendorProductRepository();
        return $vendorProductRepository->updateProduct($vendorId, $sku, $product);
    }

    public static function deleteProductBySku($sku, $vendorId, $bulkDelete, $skus)
    {
        $vendorProductRepository = new VendorProductRepository();

        try {
            if ($bulkDelete) {
                foreach ($skus as $skuId) {
                    $product = $vendorProductRepository->findProduct($vendorId, $skuId['sku']);
                    if (isset($product)) {
                        $product->customSoftDelete();
                    }
                }
                return true;
            }else {
                $product = $vendorProductRepository->findProduct($vendorId, $sku);
                return isset($product) ? $product->customSoftDelete() : 'Product with this sku not exist';
            }
            DB::commit();
        }catch (\Throwable $th){
            DB::rollback();
            report($th);
        }
    }

    public function getActiveProductsSkuAgainstVendorWhereSkuNotMatch($vendorId, $skus)
    {
        return $this->vendorProductRepository->getActiveProductsSkuAgainstVendorWhereSkuNotMatch($vendorId, $skus);
    }


}
