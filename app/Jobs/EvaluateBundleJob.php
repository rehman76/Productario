<?php

namespace App\Jobs;

use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateBundleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $bundleID;

    public function __construct($bundleID)
    {
        $this->onQueue('publication_evaluation');
        $this->bundleID = $bundleID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->bundleID)
        {
            ProductService::BundleStockCheck($this->bundleID,true);
            //todo need to un comment when its require to update bundle images on evaluation
//            app('App\Services\MediaService')->attachedOrRemovePublicationImages($this->bundleID);

        }
    }
}
