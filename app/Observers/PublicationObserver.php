<?php

namespace App\Observers;

use App\Jobs\CloneClassicMlaProductToPremiumJob;
use App\Jobs\SyncMercadolibreStoreJob;
use App\Jobs\SyncProductsToTiendanubeJob;
use App\Jobs\SyncWooCommerceStoreJob;
use App\Publication;
use App\Services\HelperService;
use App\Services\ProductService;

class PublicationObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function created(Publication $publication)
    {

    }

    /**
     * Handle the product "updated" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function updated(Publication $publication)
    {
        if (!$publication->wasRecentlyCreated && ($publication->wasChanged('price') ||
                $publication->wasChanged('quantity') || $publication->wasChanged('winner_vendor_product_id'))
        ) {

            /***** Stock & Price Variation Feature  ****/
            /// stock variation percentage calculation
            if ($publication->wasChanged('quantity'))
            {
                $originalQuantity = $publication->getOriginal()['quantity'] ?? 0;
                $newQuantity = $publication->quantity ?? 0;

                Publication::withoutEvents(function () use ($publication, $originalQuantity, $newQuantity) {
                    $publication->quantity_variation = HelperService::calculateValueVariationPercentage($originalQuantity, $newQuantity);
                    $publication->save();
                });
            }

            /// price variation percentage calculation
            if ($publication->wasChanged('price'))
            {
                $originalPrice = $publication->getOriginal()['price'] ?? 0;
                $newPrice = $publication->price ?? 0;

                Publication::withoutEvents(function () use ($publication, $originalPrice, $newPrice) {
                    $publication->price_variation = HelperService::calculateValueVariationPercentage($originalPrice, $newPrice);
                    $publication->save();
                });
            }

            /***** End: Stock & Price Variation Feature  ****/

            /***** Premium Product Feature  ****/
            if ($publication->premiumProduct()->exists())
            {
                HelperService::syncPremiumPublicationToML($publication);
            }

            /***** End: Premium Product End  ****/

            /***** Sync to Stores Feature  ****/
            if ($publication->mla)
            {
                SyncMercadolibreStoreJob::dispatch($publication);
            }

            /***** Sync to Woo Commerce Store Feature  ****/
            if ($publication->winner_vendor_product_id != null || $publication->woo_id != null)
            {
                SyncWooCommerceStoreJob::dispatch($publication);
            }

            if ($publication->is_bundle || $publication->winner_vendor_product_id != null || $publication->tiendanube_id != null)
            {
                SyncProductsToTiendanubeJob::dispatch($publication);
            }

            /***** End: Sync to Stores Feature ****/

            /***** Re-evaluate linked bundle of publication   *****/
            if (!$publication->is_bundle && $linkedBundles = ProductService::isLinkWithBundle($publication))
            {
                foreach ($linkedBundles as $linkedBundle) {
                    ProductService::BundleStockCheck($linkedBundle->bundle_id, false);
                }
            }
            /***** End: Re-evaluate linked bundle of publication *****/

        }

        /***** Re-calculate the vendor product retail price  *****/
        if (!$publication->wasRecentlyCreated && $publication->wasChanged('markup')
            && $winnerVendorProduct = $publication->vendorproductwinner()->first())
        {
            $priceAfterNewMarkup = round($winnerVendorProduct->price * (1 + ($publication->markup ? $publication->markup : nova_get_setting('markup')) / 100));
            $winnerVendorProduct->calculated_retail_price = $priceAfterNewMarkup;
            $winnerVendorProduct->save();
        }
        /***** End: Re-calculate the vendor product retail price  *****/

        /*** If MLA Id added/Updated and create the premium product if not exists ***/
        if (!$publication->wasRecentlyCreated &&
                  $publication->mla &&
                    !$publication->premiumProduct()->exists() &&
                        $publication->vendorproductwinner()->exists() && $this->isPublicationFullyActiveForML($publication))
        {
            CloneClassicMlaProductToPremiumJob::dispatch($publication)->onQueue('bulk_classic_mla_clone_to_premium');
        }
    }

    /**
     * Handle the product "deleted" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function deleted(Publication $publication)
    {
        //
    }

    /**
     * Handle the product "restored" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function restored(Publication $publication)
    {
        //
    }

    /**
     * Handle the product "force deleted" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function forceDeleted(Publication $publication)
    {
        //
    }

    public function isPublicationFullyActiveForML($publication)
    {
        if (!$publication->status || !$publication->mla_status ||
                !$publication->active || !$publication->price || $publication->quantity <= 3)
        {
            return false;
        }

        return true;
    }
}
