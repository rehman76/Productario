<?php

namespace App\Nova\Actions;

use App\Jobs\MapProductsToMercadolibreStoreJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class MapSelectedProductsWithMercadolibreStoreAction extends Action
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
        if ($models->count() > 20 )
        {
            return Action::danger('Selected products should less or equal to 20');
        }
        if ($models->isNotEmpty() )
        {
            MapProductsToMercadolibreStoreJob::dispatch($models);
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
    public $name = 'Map selected products with Mercadolibre';
}
