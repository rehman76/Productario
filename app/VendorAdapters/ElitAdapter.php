<?php
namespace App\VendorAdapters;

use App\Jobs\ImportVendorProductImagesFromVendorJob;
use App\Services\VendorProductService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 12:18 PM
 */
class ElitAdapter extends Vendor
{
    /**
     */
    public function loadProducts()
    {
        $products = [];
        $offset = 0;
            do {
                $result = $this->getDataFromAPI($offset);
                $products = isset($result['resultado']) ? array_merge($products, $result['resultado']) : $products;
                $offset += 100;
            } while (isset($result['paginador']) && $offset < $result['paginador']['total']);
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'cod_alfa');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ELIT';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
        foreach ($products as $product) {
            $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                'price' => $product['precio'],
                'dollar_rate' => $product['cotizacion'],
                'iva_percentage' => $product['iva'],
                'mark_up' => null,
                'internal_taxes' => $product['i_internos'] ? (float) $product['i_internos'] : 0,
                'sku' => $product['cod_alfa'],
            ]);

            $vendorProduct = VendorProductService::updateOrInsert([
                'sku' => $product['cod_alfa'],
                'vendor_id' => $vendorId,
                'name' => $product['detalle'],
                'vendor_price' => $product['precio'],
                'price' => $calculatedFields['price'],
                'calculated_retail_price' => $calculatedFields['retail_price'],
                'other_taxes' => $product['i_internos'],
                'iva' => $product['iva'],
                'currency' => $product['moneda'] === 1 ? 'ARS' : 'USD',
                'ean' => $product['ean'],
                'quantity' => $product['stock'],
                'min_quantity' => $product['nivelstock'],
                'link' => $product['link'],
                'sale_price' => $product['porcentaje'] > 0
                    ? ($product['precio'] - ($product['precio'] * ($product['porcentaje'] / 100)))
                    : null,
            ]);

            if(isset($vendorProduct) && isset($product['link']) && $vendorProduct->getMedia('vendor_product_images')->isEmpty())
            {
                ImportVendorProductImagesFromVendorJob::dispatch($vendorProduct, $product['link']);
            }

        }
    }


    /**
     * @param int $offset
     * @return mixed
     */
    protected function getDataFromAPI($offset = 0)
    {
        return Http::withoutVerifying()->post("https://" . config('vendors.ELIT.api.host') . "/productos?offset={$offset}",
            [
                'user_id' => config('vendors.ELIT.api.user_id'),
                'token' => config('vendors.ELIT.api.token'),
            ]
        )->json();
    }


}
