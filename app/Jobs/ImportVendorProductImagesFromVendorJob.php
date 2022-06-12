<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportVendorProductImagesFromVendorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $vendorProduct, $imageLink;

    public function __construct($vendorProduct, $imageLink)
    {
        $this->vendorProduct = $vendorProduct;
        $this->imageLink = $imageLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($imageContents = @file_get_contents($this->imageLink, 'r'))
        {
            $media = $this->vendorProduct->addMediaFromUrl($this->imageLink);
            $media->toMediaCollection('vendor_product_images');
        }
    }
}
