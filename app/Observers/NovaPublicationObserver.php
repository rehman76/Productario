<?php

namespace App\Observers;

use App\Jobs\EvaluateBundleJob;
use App\Jobs\EvaluateProductJob;
use App\Publication;

class NovaPublicationObserver
{
    /**
     * Handle the publication "created" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function creating(Publication $publication)
    {
        $publication->created_by = auth()->user()->id;
    }

    /**
     * Handle the publication "updated" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function updated(Publication $publication)
    {

        if (!$publication->is_bundle) {
            EvaluateProductJob::dispatch($publication);
        }

        if ($publication->is_bundle)
        {
            EvaluateBundleJob::dispatch($publication->id);
        }


    }

    /**
     * Handle the publication "deleted" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function deleted(Publication $publication)
    {
        //
    }

    /**
     * Handle the publication "restored" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function restored(Publication $publication)
    {
        //
    }

    /**
     * Handle the publication "force deleted" event.
     *
     * @param \App\Publication $publication
     * @return void
     */
    public function forceDeleted(Publication $publication)
    {
        //
    }
}
