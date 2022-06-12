<?php

namespace App\Jobs;

use App\Publication;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkSyncToStoreDispatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $connector;

    public function __construct($connector)
    {
        $this->onQueue('connectors_bulk_sync');
        $this->connector = $connector;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->connector == 'TN')
        {
            $publications = Publication::whereNotNull('winner_vendor_product_id')->orWhereNotNull('tiendanube_id')
                ->orWhere('is_bundle', true)->get();

            foreach ($publications as $publication)
            {
                SyncProductsToTiendanubeJob::dispatch($publication)->onQueue('connectors_bulk_sync');
            }
        }

        if ($this->connector == 'ML')
        {
            $publications = Publication::whereNotNull('mla')->with('premiumProduct')->latest()->get();

            foreach ($publications as $publication)
            {
                SyncMercadolibreStoreJob::dispatch($publication)->onQueue('connectors_bulk_sync');

                /***** Premium Product Feature  ****/
                if ($publication->premiumProduct()->exists())
                {
                    HelperService::syncPremiumPublicationToML($publication, 'connectors_bulk_sync');
                }
            }
        }
    }
}
