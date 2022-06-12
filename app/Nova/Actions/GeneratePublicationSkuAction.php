<?php

namespace App\Nova\Actions;

use App\Connectors\MercadolibreConnector;
use App\Jobs\SyncProductsToTiendanubeJob;
use App\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class GeneratePublicationSkuAction extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct()
    {
        $this->queue = 'connector';
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($publication = $models->first())
        {
            $newSku = Str::random(15);

            if (!Publication::where('sku', $newSku)->exists())
            {
                $publication->sku = $newSku;
                $publication->save();

                // dispatch store jobs to sync new sku Disable TN JOB
                if ($publication->tiendanube_id)
                {
                    dispatch(new SyncProductsToTiendanubeJob($models));
                }

                if ($publication->mla)
                {
                   $this->mercadolibreUpdate($publication, $newSku);
                }
            }
        }
    }

    public function mercadolibreUpdate($publication, $newSku)
    {
        $mercadolibreConnector = new MercadolibreConnector();
        $mercadolibreConnector->updateProductSku($publication, $newSku);

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
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name() {
        return  'Regenerate SKU';
    }
}
