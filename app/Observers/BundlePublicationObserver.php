<?php

namespace App\Observers;

use App\BundlePublication;
use App\Jobs\EvaluateBundleJob;
use App\Services\ProductService;

class BundlePublicationObserver
{
    /**
     * Handle the bundle publication "created" event.
     *
     * @param \App\BundlePublication $bundlePublication
     * @return void
     */
    public function created(BundlePublication $bundlePublication)
    {
        ProductService::UpdateBundlePublishName($bundlePublication);
        EvaluateBundleJob::dispatch($bundlePublication->bundle_id);
    }

    /**
     * Handle the bundle publication "updated" event.
     *
     * @param \App\BundlePublication $bundlePublication
     * @return void
     */
    public function updated(BundlePublication $bundlePublication)
    {
        ProductService::UpdateBundlePublishName($bundlePublication);
        EvaluateBundleJob::dispatch($bundlePublication->bundle_id);
    }

    /**
     * Handle the bundle publication "deleted" event.
     *
     * @param \App\BundlePublication $bundlePublication
     * @return void
     */
    public function deleted(BundlePublication $bundlePublication)
    {
        //
    }

    /**
     * Handle the bundle publication "restored" event.
     *
     * @param \App\BundlePublication $bundlePublication
     * @return void
     */
    public function restored(BundlePublication $bundlePublication)
    {
        //
    }

    /**
     * Handle the bundle publication "force deleted" event.
     *
     * @param \App\BundlePublication $bundlePublication
     * @return void
     */
    public function forceDeleted(BundlePublication $bundlePublication)
    {
        //
    }
}
