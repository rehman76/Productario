<?php


namespace App\VendorAdapters;


use App\Services\VendorProductService;
use Illuminate\Support\Facades\DB;


class CevenAdapter extends Vendor
{
    /**
     * This adaptor is not been use by the import script it
     * uses some functions that allow vendor to populate the data
     * by the the API
     */
    public function loadProducts()
    {

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Ceven';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        DB::beginTransaction();

        try {
            foreach ($products as $product)
            {
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => $product['price'],
                    'dollar_rate' => $product['currency'] == 'USD'?$product['dollar_rate'] ?? null:null,
                    'iva_percentage' => $product['iva'] ?? null,
                    'mark_up' => null,
                    'internal_taxes' => $product['other_taxes'] ?? null,
                    'sku' => $product['sku'],
                ]);

                VendorProductService::updateOrInsert([
                    'sku' => $product['sku'],
                    'vendor_id' => $vendorId,
                    'name' => $product['name'],
                    'vendor_price' => $product['price'],
                    'price' => $calculatedFields['price'],
                    'calculated_retail_price' => $calculatedFields['retail_price'],
                    'other_taxes' => $product['other_taxes'] ?? null,
                    'iva' => $product['iva'] ?? null,
                    'description' =>$product['description'] ?? null,
                    'currency' => $product['currency'] ?? null,
                    'ean' => $product['ean'],
                    'quantity' => $product['quantity'],
                    'min_quantity' => $product['min_quantity'] ?? null,
                    'link' => $product['link'] ?? 0,
                    'sale_price' => isset($product['porcentaje'])?$product['porcentaje'] > 0
                        ? ($product['precio'] - ($product['precio'] * ($product['porcentaje'] / 100)))
                        : null:null,
                ]);
            }

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return false;
        }
    }


    /**
     * @param int $offset
     * @return mixed
     */
    protected function getDataFromAPI($offset = 0)
    {

    }

    public function createOrUpdateProduct($vendorId, $product, $updateProductBySku= null, $sku = null)
    {
        $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
            'price' => $product['price'],
            'dollar_rate' => $product['currency'] == 'USD'?$product['dollar_rate'] ?? null:null,
            'iva_percentage' => $product['iva'] ?? null,
            'mark_up' => null,
            'internal_taxes' => $product['other_taxes'] ?? null,
            'sku' => $product['sku'],
        ]);

        return !$updateProductBySku?VendorProductService::updateOrInsert($this->vendorProductProperties($product, $calculatedFields, $vendorId))
            :VendorProductService::updateProduct($vendorId,$sku,$this->vendorProductProperties($product, $calculatedFields, $vendorId));

    }

    public function vendorProductProperties($product, $calculatedFields, $vendorId): array
    {
            return [
                'sku' => $product['sku'],
                'vendor_id' => $vendorId,
                'name' => $product['name'],
                'vendor_price' => $product['price'],
                'price' => $calculatedFields['price'],
                'calculated_retail_price' => $calculatedFields['retail_price'],
                'other_taxes' => $product['other_taxes'] ?? null,
                'iva' => $product['iva'] ?? null,
                'description' =>$product['description'] ?? null,
                'currency' => $product['currency'] ?? null,
                'ean' => $product['ean'],
                'quantity' => $product['quantity'],
                'min_quantity' => $product['min_quantity'] ?? null,
                'link' => $product['link'] ?? 0,
                'sale_price' => isset($product['porcentaje']) ? $product['porcentaje'] > 0
                    ? ($product['precio'] - ($product['precio'] * ($product['porcentaje'] / 100)))
                    : null : null,
            ];
    }


    public function deleteProductBySku($sku = null, $vendorId, $bulkDelete = null, $skus)
    {
        return VendorProductService::deleteProductBySku($sku, $vendorId, $bulkDelete, $skus);
    }

}
