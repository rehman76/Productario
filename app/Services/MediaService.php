<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 01/02/2021
 * Time: 6:56 PM
 */

namespace App\Services;

use App\Repositories\MediaRepository;
use App\Repositories\PublicationRepository;

class MediaService
{

    protected $media;

    public function __construct(PublicationRepository $productRepository, MediaRepository $media)
    {
        $this->productRepository = $productRepository;
        $this->media = $media;
    }


    public function attachedOrRemovePublicationImages($bundleId)
    {
        //first remove all then add new images
        self::RemoveBundleImages($bundleId);
        if ($publications = ProductService::getBundlePublication($bundleId)) {
            foreach ($publications as $ob) {
                if (self::isExist($ob->publication_id)) {
                    $mediaItem = $ob->publication->getMedia('avatar')->first();
                    self::create($mediaItem, $bundleId);
                }
            }
        }
    }

    public function create($mediaItem, $bundleId)
    {
        return $this->media->store(["model_type" => $mediaItem->model_type, "model_id" => $bundleId, "collection_name" => "product_images", "name" => $mediaItem->name, "file_name" => $mediaItem->file_name, "mime_type" => "image\/png", "disk" => $mediaItem->disk, "conversions_disk" => $mediaItem->conversions_disk, "size" => $mediaItem->size, "manipulations" => [], "custom_properties" => $mediaItem->custom_properties, "responsive_images" => $mediaItem->responsive_images, "order_column" => $mediaItem->order_column]);
    }

    public function RemoveBundleImages($bundleId)
    {
        return $this->media->deleteAll([['model_id', $bundleId], ['collection_name', 'product_images']]);
    }

    public function isExist($publicationId)
    {
        return (bool)$this->media->first([['model_id', $publicationId]]);
    }


    /****** ENd Import Products from Woo Commerce ****/
}
