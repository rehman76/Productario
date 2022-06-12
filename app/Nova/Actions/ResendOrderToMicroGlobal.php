<?php

namespace App\Nova\Actions;

use App\Connectors\MercadolibreConnector;
use App\Services\SaleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ResendOrderToMicroGlobal extends Action  implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct()
    {
        $this->queue = 'sale';
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
        $mercadolibreConnector = new MercadolibreConnector();
        $sale = $models->first();
        $resource = '/orders/'.$sale->order_id;
        SaleService::saleOrder($mercadolibreConnector->getOrder($resource), true);
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

    public $name = 'Refresh Sale Data';
}
