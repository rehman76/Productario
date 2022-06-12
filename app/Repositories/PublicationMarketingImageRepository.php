<?php


namespace App\Repositories;


use App\PublicationMarketingImage;

class PublicationMarketingImageRepository
{
    protected $publicationMarketingImage;

    public function __construct(PublicationMarketingImage $publicationMarketingImage)
    {
         $this->publicationMarketingImage= $publicationMarketingImage;
    }

    public function getPublicationMarketingImages()
    {
        return $this->publicationMarketingImage->get();
    }

}
