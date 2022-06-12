<?php

namespace App\Nova\Actions;

use App\Services\VendorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ImportVendorProducts extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $vendor = $models->first();
        try {
            $vendorInstance = (new VendorService())->getVendorAdapterInstance($vendor->name);
            $vendorInstance->loadProducts();
            $vendorInstance->exportVendorProductsToFile();

            $this->markAsFinished($vendor);
        } catch (\Exception $e){
            $this->markAsFailed($vendor, $e);
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
    public $name = 'Importar de este Proveedor';
}
