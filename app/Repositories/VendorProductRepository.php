<?php
namespace App\Repositories;

use App\VendorProduct;

/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 8:37 PM
 */
class VendorProductRepository
{
    protected $vendorProduct;

    public function __construct()
    {
        $this->vendorProduct = new VendorProduct();
    }

    public static function updateOrCreate($product)
    {
       return VendorProduct::updateOrCreate([
            'sku' => $product['sku'],
            'vendor_id' => $product['vendor_id'],
        ], $product);
    }

    public function findProduct($vendorId, $sku)
    {
        return $this->vendorProduct->where('vendor_id', $vendorId)->where('sku', $sku)->first();
    }

    public function getActiveProductsSKUAgainstVendor($vendorId)
    {
        return $this->vendorProduct->where('vendor_id', $vendorId)->where('status', 1)->pluck('sku')->toArray();
    }

    public function getActiveProductsSkuAgainstVendorWhereSkuNotMatch($vendorId, $skus)
    {
        return $this->vendorProduct->where('vendor_id', $vendorId)
                        ->where('status', 1)->whereNotIn('sku', $skus)->pluck('sku')->toArray();
    }

    public function updateMissingProductStatus($productNotFoundSkuNumbers, $vendorProductStatus)
    {
        $this->vendorProduct->whereIn('sku', $productNotFoundSkuNumbers)->update(['status' => $vendorProductStatus]);
    }

    public function updateProduct($vendorId, $sku, $product)
    {
        $updateRecordCount = $this->vendorProduct
            ->where('sku',$sku)
            ->where('vendor_id',$vendorId)
            ->update($product);

        if($updateRecordCount > 0)
        {
            return $this->findProduct($vendorId, $sku);
        }

    }

}
