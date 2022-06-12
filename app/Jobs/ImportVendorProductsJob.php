<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportVendorProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $vendorName;

    public function __construct($vendorName)
    {
        $this->vendorName = $vendorName;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vendorService = app('App\Services\VendorService');

        if ($vendorInstance = $vendorService->getVendorAdapterInstance($this->vendorName))
        {
            $vendorInstance->loadProducts();
            $vendorInstance->exportVendorProductsToFile();
        }
    }
}
