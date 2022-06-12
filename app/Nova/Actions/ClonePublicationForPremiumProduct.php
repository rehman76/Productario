<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Services\ClonePremiumProductService;

class ClonePublicationForPremiumProduct extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     * @param $mercadolibreConnector
     * @return void
     */

    protected $mercadolibreConnector, $clonePremiumProductService;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $publication
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        $publication = $models->first();

        if (!$publication->mla){
            return Action::danger('The classic product not being linked as MLA id not exists');
        }


        if (!$publication->winner_vendor_product_id)
        {
            return Action::danger('The winner vendor product not exists');
        }

        if ($publication->price && $publication->quantity <= 3)
        {
            return Action::danger('The publication does not have price or stock to be cloned');
        }

        if (!$publication->mla_status || !$publication->status || !$publication->active)
        {
            return Action::danger('The publication statuses are not active');
        }

        if ($publication->premiumProduct()->exists()){
            return Action::danger('Premium publication already exists in MLA for this publication');
        }


        $this->clonePremiumProductService = new ClonePremiumProductService();
        $response = $this->clonePremiumProductService->clonePremiumProduct($publication);

        if($response['error']) {
            return Action::danger($response['message']);
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

    public $name = 'Clone publication to MLA premium publication';
}
