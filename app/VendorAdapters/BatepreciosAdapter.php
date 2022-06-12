<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 11/05/2021
 * Time: 8:16 PM
 */

namespace App\VendorAdapters;


use App\Imports\BatepreciosVendorImport;
use App\Services\VendorProductService;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class BatepreciosAdapter extends Vendor
{
    /**
     */
    public function loadProducts()
    {
        $products = $this->getDataFromFile();
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct($products, $this->vendorInstance->id, 'SKU');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'BatePrecios';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            if ($product['SKU']) {
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => $product['COSTO'],
                    'dollar_rate' => null,
                    'iva_percentage' => (float)$product['IVA'],
                    'mark_up' => null,
                    'internal_taxes' => 0,
                    'sku' => $product['SKU'],
                ]);
                VendorProductService::updateOrInsert([
                    'sku' => $product['SKU'],
                    'vendor_id' => $vendorId,
                    'name' => $product['PRODUCTO'],
                    'vendor_price' => $product['COSTO'],
                    'price' => $calculatedFields['price'],
                    'calculated_retail_price' => $calculatedFields['retail_price'],
                    'iva' => (float)$product['IVA'],
                    'currency' => 'USD',
                    'ean' => $product['EAN'],
                    'quantity' => $product['STOCK'],
                ]);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getDataFromFile()
    {
        return Storage::exists('vendor_files/Bateprecios.xlsx') ? Excel::toCollection(new BatepreciosVendorImport(), 'vendor_files/Bateprecios.xlsx')->first()
            : [];

    }


}
