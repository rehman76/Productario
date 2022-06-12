<?php

namespace App\Nova\Actions;

use App\Jobs\BulkSyncToStoreDispatcherJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class BulkStoreSyncAction extends Action
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
        BulkSyncToStoreDispatcherJob::dispatch($fields->connector);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Choose Store', 'connector')->options([
                'TN' => 'Tienda',
                'ML' => 'MLA',
            ])->rules('required'),
        ];
    }

    public $name = 'Bulk Sync to Store';
}
