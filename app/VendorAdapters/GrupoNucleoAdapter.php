<?php
namespace App\VendorAdapters;

use App\Services\VendorProductService;
use Illuminate\Support\Facades\Storage;


/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 12:18 PM
 */
class GrupoNucleoAdapter extends Vendor
{
    /**
     *
     */
    public function loadProducts()
    {
        $products = json_decode(Storage::disk('ftp-bateprecios')
                ->get('vendors/grupo-nucleo/precios_stock_GN.JSON')
                , TRUE);
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'codigo');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Grupo Nucleo';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                'price' => $product['neto_pesos'],
                'dollar_rate' => 1,
                'iva_percentage' => $product['porcentaje_imp'],
                'mark_up' => null,
                'internal_taxes' => 0,
                'sku' => $product['codigo'],
            ]);

            VendorProductService::updateOrInsert([
                'sku' => $product['codigo'],
                'vendor_id' => $vendorId,
                'name' => $product['titulo'],
                'description' => $product['desc_1'] . "\n" . $product['desc_2'],
                'vendor_price' => $product['neto_pesos'],
                'price' => $calculatedFields['price'],
                'calculated_retail_price' => $calculatedFields['retail_price'],
                'currency' =>'ARS',
                //'other_taxes' => $product['i_internos'],
                'iva' => $product['porcentaje_imp'],
                //'ean' => $product['ean'],
                'quantity' => $product['stock_caba'],
                'weight' => $product['peso'],
            ]);
        }
    }

}
