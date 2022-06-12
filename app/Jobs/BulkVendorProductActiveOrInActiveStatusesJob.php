<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkVendorProductActiveOrInActiveStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendorProduct;

    protected $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendorProduct, $status)
    {
        $this->vendorProduct = $vendorProduct;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $publicationAgainstVendorProducts = $this->vendorProduct->publications;
        $this->vendorProduct->active = $this->status;
        $this->vendorProduct->save();
        if(count($publicationAgainstVendorProducts) != 0){
            foreach ($publicationAgainstVendorProducts as $publicationAgainstVendorProduct)
            {
                EvaluateProductJob::dispatch($publicationAgainstVendorProduct);
            }
        }
    }
}
