<?php


namespace App\VendorAdapters;


use App\Imports\MasNetImport;
use App\Imports\MasnetProductsImport;
use App\Imports\SecondSheetImport;
use App\Services\VendorProductService;
use App\VendorProduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MasNetAdapter extends Vendor
{
    /**
     */
    public function loadProducts()
    {
        $products = $this->getDataFromFile();
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'Codigo');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'MasNet';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {

        foreach ($products as $vendorProduct) {

            if ($vendorProduct['Precio'])
            {
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => (float) $vendorProduct['Precio'],
                    'dollar_rate' => null,
                    'iva_percentage' => (float) $vendorProduct['% IVA'] * 100,
                    'mark_up' => null,
                    'internal_taxes' => 0,
                    'sku' => $vendorProduct['Codigo'],
                ]);
                $vendorProduct['price'] =  $calculatedFields['price'];
                $vendorProduct['calculated_retail_price'] = $calculatedFields['retail_price'];
            }

            VendorProductService::updateOrInsert([
                'sku' => $vendorProduct['Codigo'],
                'vendor_id' => $vendorId,
                'name' => $vendorProduct['Descripcion'],
                'description' => $vendorProduct['Descripcion'],
                'vendor_price' => (float) $vendorProduct['Precio'],
                'price' => isset($vendorProduct['price']) ? $vendorProduct['price'] : 0,
                'calculated_retail_price' => isset($vendorProduct['calculated_retail_price']) ? $vendorProduct['calculated_retail_price'] : 0,
                'sale_price' => null,
                'quantity' =>  $vendorProduct['Stock B.A.'],
                'currency' => 'USD',
                'iva' => (float) $vendorProduct['% IVA'] * 100,
            ]);
        }
    }

    /**
     * @return mixed
     */
    protected function getDataFromFile()
    {
        // Download csv from ftp server
        Storage::put(
            'TEMP_CSV_MASNET.CSV',
            Storage::disk('ftp-bateprecios')->get('vendors/masnet/IN_Lista_Precios.csv')
        );

        // Import CSV into array
        $array = Excel::toArray(new MasnetProductsImport(), 'TEMP_CSV_MASNET.CSV')[0];

        // Check last newline error
        $lastrow = $array[array_key_last($array)];
        if ("\x1A" == $lastrow[array_key_first($lastrow)]) {
            array_pop($array);
        }

         return $array;
    }



}
