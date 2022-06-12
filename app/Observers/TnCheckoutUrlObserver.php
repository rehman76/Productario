<?php

namespace App\Observers;

use App\Services\TnCheckoutUrlService;
use App\TnCheckoutUrl;
use Illuminate\Support\Facades\Log;

class TnCheckoutUrlObserver
{
    /**
     * Handle the tn checkout url "created" event.
     *
     * @param  \App\TnCheckoutUrl  $tnCheckoutUrl
     * @return void
     */
    public function created(TnCheckoutUrl $tnCheckoutUrl)
    {
        TnCheckoutUrlService::generateCheckoutUrl($tnCheckoutUrl);
    }

    /**
     * Handle the tn checkout url "updated" event.
     *
     * @param  \App\TnCheckoutUrl  $tnCheckoutUrl
     * @return void
     */
    public function updated(TnCheckoutUrl $tnCheckoutUrl)
    {
        TnCheckoutUrlService::generateCheckoutUrl($tnCheckoutUrl);
    }

    /**
     * Handle the tn checkout url "deleted" event.
     *
     * @param  \App\TnCheckoutUrl  $tnCheckoutUrl
     * @return void
     */
    public function deleted(TnCheckoutUrl $tnCheckoutUrl)
    {
        //
    }

    /**
     * Handle the tn checkout url "restored" event.
     *
     * @param  \App\TnCheckoutUrl  $tnCheckoutUrl
     * @return void
     */
    public function restored(TnCheckoutUrl $tnCheckoutUrl)
    {
        //
    }

    /**
     * Handle the tn checkout url "force deleted" event.
     *
     * @param  \App\TnCheckoutUrl  $tnCheckoutUrl
     * @return void
     */
    public function forceDeleted(TnCheckoutUrl $tnCheckoutUrl)
    {
        //
    }
}
