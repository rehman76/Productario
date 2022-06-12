<?php

namespace App\Nova\Actions;

use App\Jobs\BulkVendorProductActiveOrInActiveStatusesJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use OwenMelbz\RadioField\RadioButton;

class ActiveOrInActiveVendorProducts extends Action implements ShouldQueue
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
        foreach($models as $vendorProduct)
        {
            BulkVendorProductActiveOrInActiveStatusesJob::dispatch($vendorProduct, $fields->active);
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
            RadioButton::make('Active', 'active')
                ->options([
                    1 => 'Yes',
                    0 => 'No',
                ])
                ->default(1) // optional
                ->marginBetween() // optional
                ->skipTransformation() // optional

        ];
    }
}
