<?php

namespace App\Observers;

use App\Services\TnCheckoutUrlService;
use App\TnCheckoutUrlPublication;
use Illuminate\Support\Facades\Log;

class TnCheckoutUrlPublicationObserver
{
    /**
     * Handle the tn checkout url publication "created" event.
     *
     * @param  \App\TnCheckoutUrlPublication  $tnCheckoutUrlPublication
     * @return void
     */
    public function created(TnCheckoutUrlPublication $tnCheckoutUrlPublication)
    {
        TnCheckoutUrlService::UpdatePublicationName($tnCheckoutUrlPublication);
        TnCheckoutUrlService::getTnPublicationVariantId($tnCheckoutUrlPublication);
    }

    /**
     * Handle the tn checkout url publication "updated" event.
     *
     * @param  \App\TnCheckoutUrlPublication  $tnCheckoutUrlPublication
     * @return void
     */
    public function updated(TnCheckoutUrlPublication $tnCheckoutUrlPublication)
    {
        TnCheckoutUrlService::UpdatePublicationName($tnCheckoutUrlPublication);
        TnCheckoutUrlService::getTnPublicationVariantId($tnCheckoutUrlPublication);
    }

    /**
     * Handle the tn checkout url publication "deleted" event.
     *
     * @param  \App\TnCheckoutUrlPublication  $tnCheckoutUrlPublication
     * @return void
     */
    public function deleted(TnCheckoutUrlPublication $tnCheckoutUrlPublication)
    {
        //
    }

    /**
     * Handle the tn checkout url publication "restored" event.
     *
     * @param  \App\TnCheckoutUrlPublication  $tnCheckoutUrlPublication
     * @return void
     */
    public function restored(TnCheckoutUrlPublication $tnCheckoutUrlPublication)
    {
        //
    }

    /**
     * Handle the tn checkout url publication "force deleted" event.
     *
     * @param  \App\TnCheckoutUrlPublication  $tnCheckoutUrlPublication
     * @return void
     */
    public function forceDeleted(TnCheckoutUrlPublication $tnCheckoutUrlPublication)
    {
        //
    }
}
