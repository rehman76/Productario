<?php

namespace App\Jobs;

use App\Publication;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class DeleteCommonImagesFromOurAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @param $publication
     * @return void
     */


    protected $publication, $publications;
    public function __construct($publication = null)

    {
        $this->onQueue('connectors_bulk_sync');
        $this->publication = $publication;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->publication)
        {
            $this->checkAndRemoveMarketingImages($this->publication);
        } else {
            $publications= Publication::whereNotNull('mla')->get();

            if(isset($publications))
            {
                $publications->each(function ($publication) {
                    DeleteCommonImagesFromOurAppJob::dispatch($publication);
                });
            }
        }
    }

    public function checkAndRemoveMarketingImages($publication)
    {
        $hashedMarketingImages= HelperService::hashedMarketingImages(false, null, null, null);

        $isAnyDeletedPublicationImage = !!$publication->getMedia('product_images')->filter(function ($mediaImage) use($hashedMarketingImages) {

            $hasher= HelperService::hasherInstance();
            $hashedPublicationImage= $hasher->hash(file_get_contents($mediaImage->getUrl()));

            $isPublicationImageMatched = false;
            foreach($hashedMarketingImages as $hashedMarketingImage) {
                if ($hasher->distance($hashedPublicationImage, $hashedMarketingImage) < 10) {
                    $isPublicationImageMatched = true;
                    break;
                }
            }

            return $isPublicationImageMatched;
        })->values()->each(function ($imageToBeDeleted) use($publication) {
            $imageToBeDeleted->delete();
        })->count();

        if ($isAnyDeletedPublicationImage)
        {
            HelperService::updateTnPublicationImage($publication);
        }
    }
}
