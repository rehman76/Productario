<?php

namespace App\Nova\Actions;

use App\Jobs\SyncMercadolibreStoreJob;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncSelectedProductsMercadolibreStoreAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() > 10 )
        {
            return Action::danger('Selected products should less or equal to 10');
        }
        $publications = $models->whereNotNull('mla');

        foreach ($publications as $publication)
        {
            SyncMercadolibreStoreJob::dispatch($publication);

            /***** Premium Product Feature  ****/
            if ($publication->premiumProduct()->exists())
            {
                HelperService::syncPremiumPublicationToML($publication);
            }

        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Sync selected Products to Mercadolibre';
}
