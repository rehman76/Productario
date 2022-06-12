<?php

namespace App\Nova\Actions\Vendor;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;

class UploadBatepreciosVendorFileAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Upload Bateprecios Vendor File';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        Storage::putFileAs(
            'vendor_files',
            $fields->file,
            'Bateprecios.xlsx'
        );
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('File', 'file')->rules('required', 'mimes:xlsx'),
        ];
    }
}
