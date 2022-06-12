<?php

namespace App\Nova\Actions;

use App\Jobs\CloneClassicMlaProductToPremiumJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Publication;

class BulkClonePublicationForPremiumProduct extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct()
    {
        $this->queue = 'bulk_classic_mla_clone_to_premium';
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
        $publications = Publication::doesntHave('premiumProduct')->whereNotNull('mla')
                                    ->whereNotNull('winner_vendor_product_id')
                                        ->whereNotNull('price')->where('quantity','>',3)->where('status','=', 1)
                                        ->where('mla_status','=', 1)->where('active','=', 1)->get();
        if($publications) {
            foreach ($publications as $publication) {
                CloneClassicMlaProductToPremiumJob::dispatch($publication)->onQueue($this->queue);
            }
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

    public $name = 'Bulk Clone publication to MLA premium publications';
}
