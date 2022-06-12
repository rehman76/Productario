<?php

namespace App\Nova\Actions;

use App\Jobs\GenerateBulkTnCouponCodeJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class GenerateBulkTnCouponCodes extends Action
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
        if ($models->first()->id==2)
        {
            GenerateBulkTnCouponCodeJob::dispatch($fields ,$models->first());
        } else {
            return Action::danger('The feature is only for TN');
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
            Text::make('Prefix' , 'prefix')->rules('required'),
            Number::make('Number of coupon codes' , 'no_of_coupon_codes')->rules('required'),
            Number::make('Discount %' , 'discount_percentage')->rules('required')->min(1)->max(100)->step(0.01),
            Number::make('Max usage' , 'max_usage')->rules('required'),
        ];
    }
}
