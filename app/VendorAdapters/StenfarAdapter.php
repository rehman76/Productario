<?php
namespace App\VendorAdapters;

use App\Imports\StylusProductsImport;
use App\Services\VendorProductService;
use Illuminate\Support\Facades\Storage;
use Excel;


/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 12:18 PM
 */
class StenfarAdapter extends Vendor
{
    /**
     *
     */
    public function loadProducts()
    {
        // Get stylus products
        $products = $this->getDataFromFTP();
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'codigo');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Stenfar';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                'price' => $product['Importe'],
                'dollar_rate' => 1,
                'iva_percentage' => $product['COEFICIENTEDEFAULT'],
                'mark_up' => null,
                'internal_taxes' => 0,
                'sku' => $product['codigo'],
            ]);

            VendorProductService::updateOrInsert([
                'sku' => $product['codigo'],
                'vendor_id' => $vendorId,
                'name' => $product['descripcion'],
                'description' => $product['descripcion'],
                'vendor_price' => $product['Importe'],
                'price' => $calculatedFields['price'],
                'calculated_retail_price' => $calculatedFields['retail_price'],
                'quantity' => $product['disponible'],
                'iva' =>$product['COEFICIENTEDEFAULT'],
                'currency' => 'ARS',
            ]);
        }
    }

    /**
     * @return mixed
     */
    protected function getDataFromFTP()
    {
        // Download csv from ftp server
        Storage::put(
            'TEMP_CSV_STENFAR.CSV',
            Storage::disk('ftp-bateprecios')->get('vendors/stenfar/Listado-17384.txt')
        );

        // Import CSV into array
        $array = Excel::toArray(new StylusProductsImport(), 'TEMP_CSV_STENFAR.CSV')[0];

        // Check last newline error
        $lastrow = $array[array_key_last($array)];
        if ("\x1A" == $lastrow[array_key_first($lastrow)]) {
            array_pop($array);
        }

        // Return array
        return $array;
    }


}
