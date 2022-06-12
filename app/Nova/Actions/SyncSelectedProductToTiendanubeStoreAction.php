<?php

namespace App\Nova\Actions;

use App\Connectors\TiendanubeConnector;
use App\Jobs\SyncProductsToTiendanubeJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use OwenMelbz\RadioField\RadioButton;

class SyncSelectedProductToTiendanubeStoreAction extends Action
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
            return $product->winner_vendor_product_id != null || $product->tiendanube_id != null;
        })->all();


        foreach ($publications as $publication)
        {
            if ($fields->action==1) // check if require action is to create product
            {
                /// if product TN id already exists delete that
                if ($publication->tiendanube_id)
                {
                    (new TiendanubeConnector())->deleteProduct($publication->tiendanube_id);
                    $publication->tiendanube_id = null;
                    $publication->save();
                }
            }
            SyncProductsToTiendanubeJob::dispatch($publication);
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            RadioButton::make('Choose sync action with store', 'action')
                ->options([
                    1 => 'Create',
                    0 => 'Update',
                ])
                ->default(0) // optional
                ->marginBetween() // optional
                ->skipTransformation() // optional

        ];
    }
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Sync selected Products to Tiendanube';

}
