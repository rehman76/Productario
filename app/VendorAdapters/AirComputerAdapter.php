<?php
namespace App\VendorAdapters;

use App\Services\VendorProductService;
use App\Services\AirComputerProductsService;
use Excel;
use Illuminate\Support\Facades\Storage;


/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 12:18 PM
 */
class AirComputerAdapter extends Vendor
{
    /**
     */
    public function loadProducts()
    {
            $airComputerVendorProductService = app(AirComputerProductsService::class);
            $airComputerVendorProductService->saveProductsFromAllGroups($this->vendorInstance->id);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Air Computers';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            Storage::append('air_computer_skus.txt', $product['codiart']);
            $vendorProduct = [
                'sku' => $product['codiart'],
                'vendor_id' => $vendorId,
                'name' => $product['descart'],
                'description' => $product['descart'],
                'vendor_price' => (preg_match('/\pL/', $product['precio_user']) ? 0.00 : $product['precio_user']),
                'price' => (preg_match('/\pL/', $product['precio_user'])? 0.00 : $product['precio_user']),
                'calculated_retail_price' => (preg_match('/\pL/', $product['precio_user']) ? 0.00 : $product['precio_user']),
                'sale_price' => (preg_match('/\pL/', $product['precio_user'])? 0.00 : $product['precio_user']),
                'quantity' =>  (ctype_alpha($product['stock_full']['lug']) ? 0 : $product['stock_full']['lug']),
                'ean' => $product['part_number'],
                'currency' => 'USD',
                'iva' => (preg_match('/\pL/', $product['alicuota_iva']) ? 0.00 : $product['alicuota_iva']),
                'status' => $product['estado_detalle'] == 'Producto sólo para venta en esquemas'? 0: null
            ];

            if ($vendorProduct['price'])
            {
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => $vendorProduct['price'],
                    'dollar_rate' => null,
                    'iva_percentage' => $vendorProduct['iva'],
                    'mark_up' => null,
                    'internal_taxes' => 0,
                    'sku' => $product['codiart'],
                ]);
                $vendorProduct['price'] =  $calculatedFields['price'];
                $vendorProduct['calculated_retail_price'] = $calculatedFields['retail_price'];
            }

            VendorProductService::updateOrInsert($vendorProduct);
        }
    }

}
