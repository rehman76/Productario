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
class StylusAdapter extends Vendor
{
    /**
     *
     */
    public function loadProducts()
    {
        // Get stylus products
        $products = $this->getDataFromFTP();
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'IdProducto');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Stylus';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                'price' => $product['PrecioDeLista'],
                'dollar_rate' => (int)$product['Moneda'] ? null : 1,
                'iva_percentage' => $product['Iva'] == 'M' ? 10.5 : 21,
                'mark_up' => null,
                'internal_taxes' => $product['IInternos'] == 'S' ? 20.48 : 0,
                'sku' => $product['IdProducto'],
            ]);

            VendorProductService::updateOrInsert([
                'sku' => $product['IdProducto'],
                'vendor_id' => $vendorId,
                'name' => $product['Nombre'],
                'description' => $product['Descripcion'],
                'vendor_price' => $product['PrecioDeLista'],
                'price' => $calculatedFields['price'],
                'calculated_retail_price' => $calculatedFields['retail_price'],
                'sale_price' => $product['Oferta'],
                'quantity' => $product['Stock'],
                'other_taxes' => ($product['IInternos'] == 'S' ? 20.48 : 0),
                'iva' => ($product['Iva'] == 'M' ? 10.5 : 21),
                'currency' => ((int)$product['Moneda'] ? 'USD' : 'ARS'),
                'min_quantity' => $product['StkMinimo'],
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
            'TEMP_CSV_STYLUS.CSV',
            Storage::disk('ftp-bateprecios')->get('vendors/stylus/artweb1.txt')
        );

        // Import CSV into array
        $array = Excel::toArray(new StylusProductsImport(), 'TEMP_CSV_STYLUS.CSV')[0];

        // Check last newline error
        $lastrow = $array[array_key_last($array)];
        if ("\x1A" == $lastrow[array_key_first($lastrow)]) {
            array_pop($array);
        }

        // Return array
        return $array;
    }


}
