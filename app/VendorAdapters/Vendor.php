<?php
namespace App\VendorAdapters;
use App\Exports\VendorProductsExport;
use App\Services\VendorProductService;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Valuestore\Valuestore;

/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 26/01/2021
 * Time: 11:53 AM
 */
abstract class Vendor
{
    protected $vendorInstance;
    protected $vendorProductService;

    public function __construct()
    {
        $this->vendorInstance = \App\Vendor::where('name', $this->getName())->first();
        $this->vendorProductService = new VendorProductService();
    }
    /**
     *
     */
    abstract function loadProducts();

    /**
     * @return string
     */
    abstract function getName();

    /**
     * To import products to vendor_products table
     * @param $products
     * @param $vendorId
     */
    abstract function importToDataBase($vendorId, $products);

    /**
     * @param array $vendorProductFields
     * @return array $calculatedFields
     * To get calculated values according to define formula for vendor
     *  product price & retail price
     */
    public function getCalculatedPriceAndRetailPrice($vendorProductFields)
    {
        $calculatedFields['price'] = $this->calculateProductPrice($vendorProductFields);
        $calculatedFields['retail_price'] = $this->calculateProductRetailPrice($vendorProductFields);

        return $calculatedFields;
     }
    /**
     * @param array $vendorProductFields
     * @return double
     * The formula is to calculate retail price of vendor product in pesos
     *  Formula: round(price * dollar_rate * (1 + iva/100) * iibb * markup);
     */
    public function calculateProductRetailPrice($vendorProductFields)
    {
        return round(
            $vendorProductFields['price'] *
            (1+  $vendorProductFields['internal_taxes']/ 100 +
               $this->getIvaPercentage($vendorProductFields['iva_percentage'])/ 100 +
                nova_get_setting('IIBB')/ 100) *
            $this->getDollarRate($vendorProductFields['dollar_rate']) *
            (1+ $this->getMarkupPercentage($vendorProductFields['sku'])/ 100)
        );
    }

    /**
     * @param array $vendorProductFields
     * @return double
     * The formula is to calculate price of vendor product in pesos
     *  Formula: round(price * dollar_rate * (1 + iva/100) * iibb);
     */
    public function calculateProductPrice($vendorProductFields)
    {
        return round(
            $vendorProductFields['price'] *
            (1+  $vendorProductFields['internal_taxes']/ 100 +
                $this->getIvaPercentage($vendorProductFields['iva_percentage'])/ 100 +
                nova_get_setting('IIBB')/ 100) *
             $this->getDollarRate($vendorProductFields['dollar_rate'])
        );
    }

    /**
     * @param $importedProductDollarRate
     * @return int|double
     */
    public function getDollarRate($importedProductDollarRate)
    {
        /**** Return the rate if its mentioned in imported product ***/
        if ($importedProductDollarRate)
        {
           return $importedProductDollarRate;
        }

        /*** Return if the rate set in vendor config ***/
        if ($this->vendorInstance->dollar_rate)
        {
            return $this->vendorInstance->dollar_rate;
        }

        return nova_get_setting('dollar_rate');
    }

    /**
     * @param $importedProductIva
     * @return int|double
     */
    public function getIvaPercentage($importedProductIva)
    {
        /**** Return the rate if its mentioned in imported product ***/
        if ($importedProductIva)
        {
            return $importedProductIva;
        }
        /*** Return if the rate set in vendor config ***/
        if ($this->vendorInstance->other_taxes)
        {
            return $this->vendorInstance->other_taxes;
        }

        return nova_get_setting('IVA');
    }

    /**
     * @param $vendorProductSku
     * @return double
     */
    public function getMarkupPercentage($vendorProductSku)
    {
        $product = $this->vendorProductService->getLinkedPublication($this->vendorInstance->id, $vendorProductSku);

        /** Return if the linked publication have the markup value */
        if ($product && $product->markup)
        {
            return $product->markup;
        }

        /** Return if the vendor config have the mark up value */
        if ($this->vendorInstance->mark_up)
        {
            return $this->vendorInstance->mark_up;
        }

        return nova_get_setting('markup');
    }

    public function exportVendorProductsToFile()
    {
        Excel::store( new VendorProductsExport($this->vendorInstance->id),
                    strtolower(str_replace(' ', '_', $this->vendorInstance->name)).'.csv', 'export' );
    }
}