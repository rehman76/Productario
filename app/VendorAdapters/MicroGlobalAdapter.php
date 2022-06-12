<?php


namespace App\VendorAdapters;


use App\Services\VendorProductService;
use Throwable;

class MicroGlobalAdapter extends Vendor
{
    /**
     * @throws Throwable
     */
    public function loadProducts()
    {
        $products = $this->getDataFromSoapWebServices();
        $this->importToDataBase($this->vendorInstance->id, $products);
        $this->vendorProductService->removeZombieProduct(collect($products), $this->vendorInstance->id, 'partNumber');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Micro Global';
    }

    /**
     * @param $vendorId
     * @param $products
     */
    public function importToDataBase($vendorId, $products)
    {
            foreach ($products as $product) {
                $calculatedFields = $this->getCalculatedPriceAndRetailPrice([
                    'price' => $product->precio,
                    'dollar_rate' => 1,
                    'iva_percentage' => $product->iva_pct * 100,
                    'mark_up' => null,
                    'internal_taxes' => isset($product->i_internos) ? (float)$product->i_internos : 0,
                    'sku' => $product->partNumber,
                ]);

                VendorProductService::updateOrInsert([
                    'sku' => $product->partNumber,
                    'vendor_id' => $vendorId,
                    'name' => $product->descripcion,
                    'vendor_price' => $product->precio,
                    'price' => $calculatedFields['price'],
                    'calculated_retail_price' => $calculatedFields['retail_price'],
                    'other_taxes' => isset($product->i_internos) ?? null,
                    'iva' => $product->iva_pct * 100,
                    'currency' => 'ARS', //Give hardcoded currency
                    'ean' => isset($product->ean) ?? null,
                    'quantity' => $product->stock,
                    'min_quantity' => isset($product->nivelstock) ?? null,
                    'link' => isset($product->link) ?? null,
                    'sale_price' => null,
                ]);

            }
    }

    /**
     * @return mixed
     */
    protected function getDataFromSoapWebServices()
    {
        try{
            $url = "https://ecommerce.microglobal.com.ar/WSMG_back/WSMG.asmx?WSDL";
            $client = new \SoapClient($url);
            $getCatalogResponse = $client->GetCatalog(array('cliente'=>'928286',
                'usuario'=>'','password'=>'YFK665'));

            if($getCatalogResponse->GetCatalogResult->message != '')
            {
                throw new \Exception($getCatalogResponse->GetCatalogResult->message);
            }

            return $getCatalogResponse->GetCatalogResult->listProducts->Product;

        }catch (Throwable $th) {
            throw $th;
        }

    }
}
