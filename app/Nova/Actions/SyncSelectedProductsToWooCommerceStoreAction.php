<?php

namespace App\Nova\Actions;

use App\Jobs\SyncWooCommerceStoreJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncSelectedProductsToWooCommerceStoreAction extends Action
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
        if ($models->count() > 5 )
        {
            return Action::danger('Selected products should less or equal to 5');
        }

        $publications = $models->filter(function ($product, $key) {
            return $product->winner_vendor_product_id != null || $product->woo_id != null;
        })->all();

        foreach ($publications as $publication)
        {
            SyncWooCommerceStoreJob::dispatch($publication);
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
    public $name = 'Sync selected Products to WooCommerce';
}
