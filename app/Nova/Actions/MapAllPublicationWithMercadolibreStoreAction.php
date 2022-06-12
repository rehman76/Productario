<?php

namespace App\Nova\Actions;

use App\Jobs\MapProductsToMercadolibreStoreJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class MapAllPublicationWithMercadolibreStoreAction extends Action
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
         MapProductsToMercadolibreStoreJob::dispatch();
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
//
        ];
    }

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Map all products with Mercadolibre';
}
