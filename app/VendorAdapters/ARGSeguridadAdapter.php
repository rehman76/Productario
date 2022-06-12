<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 11/05/2021
 * Time: 8:16 PM
 */

namespace App\VendorAdapters;


use App\Imports\ARGSeguridadVendorImport;
use App\Jobs\ImportVendorProductImagesFromVendorJob;
use App\Jobs\ScrapDataFromArgseguridadWebsite;
use App\Services\VendorProductService;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ARGSeguridadAdapter extends Vendor
{
    /**
     */
    public function loadProducts()
    {
         $products = $this->getDataFromFile();
         $this->importToDataBase($this->vendorInstance->id, $products);
         $this->vendorProductService->removeZombieProduct($products, $this->vendorInstance->id,'ID');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ARG Seguridad';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            if ($product['ID'])
            {
                $vendorPrice = (float) str_replace(',', '.',$product['PRECIO-USD (iva inc)']);
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => $vendorPrice,
                    'dollar_rate' =>  null,
                    'iva_percentage' => 1,
                    'mark_up' => null,
                    'internal_taxes' => 0,
                    'sku' => $product['ID'],
                ]);

                $vendorProduct = VendorProductService::updateOrInsert([
                    'sku' => $product['ID'],
                    'vendor_id' => $vendorId,
                    'name' => $product['TITLE'],
                    'description' => $product['DESCRIPTION'],
                    'vendor_price' => $vendorPrice,
                    'price' => $calculatedFields['price'],
                    'calculated_retail_price' => $calculatedFields['retail_price'],
                    'iva' => 1,
                    'currency' =>  'USD'
                ]);


                if(isset($vendorProduct) && isset($product['IMAGE_URL']) && $vendorProduct->getMedia('vendor_product_images')->isEmpty())
                {
                    ImportVendorProductImagesFromVendorJob::dispatch($vendorProduct, $product['IMAGE_URL']);
                }

            }
        }
        ScrapDataFromArgseguridadWebsite::dispatch();
    }

    /**
     * @return mixed
     */
    protected function getDataFromFile()
    {
        Storage::put('vendor_files/ARGSeguridad.xls', file_get_contents('https://www.argseguridad.com/admin/excel-productos.php'));

        return Storage::exists('vendor_files/ARGSeguridad.xls') ?  Excel::toCollection(new ARGSeguridadVendorImport(), 'vendor_files/ARGSeguridad.xls')->first()
                    : [];

    }

}
