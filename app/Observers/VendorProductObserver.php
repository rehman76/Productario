<?php

namespace App\Observers;

use App\Services\HelperService;
use App\VendorProduct;

class VendorProductObserver
{
    /**
     * Handle the vendor product "created" event.
     *
     * @param \App\VendorProduct $VendorProduct
     * @return void
     */
    public function created(VendorProduct $VendorProduct)
    {
        //
    }

    /**
     * Handle the vendor product "updated" event.
     *
     * @param \App\VendorProduct $VendorProduct
     * @return void
     */
    public function updated(VendorProduct $VendorProduct)
    {
        if (!$VendorProduct->wasRecentlyCreated && ($VendorProduct->wasChanged('price') ||
                $VendorProduct->wasChanged('quantity'))) {

            if($VendorProduct->wasChanged('quantity')) {

                $originalQuantity = $VendorProduct->getOriginal()['quantity'] ?? 0;
                $newQuantity = $VendorProduct->quantity ?? 0;

                    VendorProduct::withoutEvents(function () use ($VendorProduct, $originalQuantity, $newQuantity) {
                        $VendorProduct->quantity_variation = HelperService::calculateValueVariationPercentage($originalQuantity, $newQuantity);
                        $VendorProduct->save();
                    });
                }

            if($VendorProduct->wasChanged('price')) {
                $originalPrice = $VendorProduct->getOriginal()['price'] ?? 0;
                $newPrice = $VendorProduct->price ?? 0;

                    VendorProduct::withoutEvents(function () use ($VendorProduct, $originalPrice, $newPrice) {
                        $VendorProduct->price_variation = HelperService::calculateValueVariationPercentage($originalPrice, $newPrice);
                        $VendorProduct->save();
                    });

                }
        }

    }

    /**
     * Handle the vendor product "deleted" event.
     *
     * @param \App\VendorProduct $VendorProduct
     * @return void
     */
    public function deleted(VendorProduct $VendorProduct)
    {
        //
    }

    /**
     * Handle the vendor product "restored" event.
     *
     * @param \App\VendorProduct $VendorProduct
     * @return void
     */
    public function restored(VendorProduct $VendorProduct)
    {
        //
    }

    /**
     * Handle the vendor product "force deleted" event.
     *
     * @param \App\VendorProduct $VendorProduct
     * @return void
     */
    public function forceDeleted(VendorProduct $VendorProduct)
    {
        //
    }
}
