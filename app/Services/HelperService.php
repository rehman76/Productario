<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 10/05/2021
 * Time: 2:49 PM
 */

namespace App\Services;


use App\Connectors\TiendanubeConnector;
use App\ErrorLog;
use App\Jobs\SyncMercadolibreStoreJob;
use App\Notifications\ErrorNotification;
use App\PublicationMarketingImage;
use App\SyncLog;
use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class HelperService
{


    public static function createAttributesColumnFormat($attributes)
    {
        $productAttributes = [];
        foreach ($attributes as $attribute) {
            $productAttributes[$attribute['name']] = $attribute['value_name'];
        }

        return $productAttributes;
    }

    public static function setStorePriceAndQuantity($isBundle, $price, $quantity, $fields, $isPremiumProduct = false)
    {
        if (in_array('price', nova_get_setting($fields['storeSettingField'])) && isset($price)) {
            if (nova_get_setting($fields['storeModifierFieldForPrice']) && !$isBundle & !$isPremiumProduct)
            {
                $modifiedPrice = round($price + (floatval(nova_get_setting($fields['storeModifierFieldForPrice'])) / 100 * $price));
                if (isset($fields['publication_minimum_price'])
                                && $fields['publication_minimum_price'] > $modifiedPrice)
                {
                    $modifiedPrice = $fields['publication_minimum_price'];
                }

                $fields['productUpdateData']['price'] = $modifiedPrice;
            }  else {
                $fields['productUpdateData']['price'] = $price;
            }
        }

        if (in_array('quantity', nova_get_setting($fields['storeSettingField'])) && isset($quantity)) {
            if (nova_get_setting($fields['storeModifierFieldForQuantity']) && !$isBundle)
            {
                $updatedQuantity = $quantity + intval(nova_get_setting($fields['storeModifierFieldForQuantity']));
                $fields['productUpdateData'][$fields['storeQuantityField']] = $updatedQuantity && $updatedQuantity > 0 ? $updatedQuantity : 0;

            } else {
                $fields['productUpdateData'][$fields['storeQuantityField']] = $quantity;
            }
        }
        return $fields['productUpdateData'];
    }

    public static function convertStingToNewLineHtmlTag($string)
    {
        /// convert only if given string is in plain text
        if (preg_match("/<[^<]+>/", $string, $m) != 0)
        {
            return $string;
        }

        return nl2br($string);
    }


    public static function calculateValueVariationPercentage($originalValue, $newValue): string
    {
        if (!$originalValue)
        {
            return 0;
        }
        $diff = $newValue - $originalValue;
        $percentChange = ($diff / $originalValue) * 100;

        return round($percentChange, 2);
    }

    public static function sign($originalValue, $newValue): int
    {
        return  $newValue > $originalValue  ? 1 : -1;
    }

    public static function updateTnPublicationImage($product = null)
    {
        $teiendanubeConnector = new TiendanubeConnector();
        $images = $teiendanubeConnector->getProductImages($product->tiendanube_id)->json();

        // delete existing images
        foreach ($images as $image)
        {
            $teiendanubeConnector->deleteProductImage($product->tiendanube_id, $image['id']);
        }

        /// create product images
        $imagePosition = 1;

        /* for feature image */
        if (!$product->getMedia('avatar')->isEmpty()) {
            $teiendanubeConnector->createProductImage($product->tiendanube_id,  [
                'position' => $imagePosition,
                'src' => $product->getMedia('avatar')[0]->getFullUrl()
            ]);
        }

        /* for gallery images */
        if (!$product->getMedia('product_images')->isEmpty()) {
            $productMediaItems = $product->getMedia('product_images');
            foreach ($productMediaItems as $mediaItem) {
                $imagePosition++;
                $teiendanubeConnector->createProductImage($product->tiendanube_id,  [
                    'position' => $imagePosition,
                    'src' => $mediaItem->getFullUrl()
                ]);
            }
        }

    }

    public static function hasherInstance()
    {
        return new ImageHash(new DifferenceHash());
    }

    public static function hashedMarketingImages($isPullImage= false, $images= null, $urlKey= null, $product= null)
    {
        $hasher= HelperService::hasherInstance();

        $hashedMarketingImages= PublicationMarketingImage::get()->map(function ($image) use($hasher) {
            return $hasher->hash(file_get_contents($image->image_url));
        });


        if(!$isPullImage && !$images && !$urlKey)
        {
            return $hashedMarketingImages;

        }else{
            foreach ($images as $key => $image) {
                if ($imageContents = @file_get_contents($image[$urlKey], 'r')) {

                    $matched = true;

                    foreach ($hashedMarketingImages as $hashedMarketingImage)
                    {
                        if($hasher->distance($hasher->hash($imageContents), $hashedMarketingImage) < 10)
                        {
                            $matched = false;
                            break;
                        }
                    }

                    if($matched)
                    {
                        $media = $product->addMediaFromUrl($image[$urlKey]);
                        $key === 0 ? $media->toMediaCollection('avatar') : $media->toMediaCollection('product_images');
                    }
                }
            }
        }

    }

    public static function syncPremiumPublicationToML($publication, $queue = 'connector')
    {
        if ($winnerVendorProduct = $publication->vendorproductwinner()->first())
        {
            $premiumProductPrice = round((nova_get_setting('markup_percentage_premium_product') / 100) * $winnerVendorProduct->price
                + $winnerVendorProduct->price);
            $publication->premiumProduct()->update([
                'price' => $premiumProductPrice
            ]);
        }

        SyncMercadolibreStoreJob::dispatch($publication, true)->onQueue($queue);
    }

    public static function logSync($product, $productUpdateData = [], $response= null, $message=null, $premiumProduct = false, $connectorId = null)
    {
        $message = self::connectorSyncLogMessage($response, $connectorId, $message, $premiumProduct);

        SyncLog::create([
            'connector_id' => $connectorId,
            'publication_id' => $product->id,
            'attributes' => $productUpdateData,
            'message' => $message
        ]);

//        $connectorId==Constants::ConnectorMercadolibre ?
//                self::logErrorsInCaseRequestNotFullFilled($response, $product, $message, $productUpdateData['mla_id']) : '';
    }

    public static function connectorSyncLogMessage($response, $connectorId, $message, $premiumProduct)
    {
        if($connectorId == Constants::ConnectorMercadolibre)
        {
            if($response && $response->successful() || !$response)
            {
                return self::syncLogProductMessage($message, $premiumProduct);
            }else{
                return $response->body();
            }

        }else{
            return $message;
        }
    }

    public static function syncLogProductMessage($message = null, $isPremiumProduct = '')
    {
        if ($isPremiumProduct)
        {
            $message.=  '  (Premium) ';
        }

        return $message;
    }

    public static function logErrorsInCaseRequestNotFullFilled($response, $product, $message, $mlaId)
    {
        if(($response && !$response->successful()) || !$response)
        {
            $data = [
                'title' => $mlaId. ' has issue please have a look',
                'message' => $message,
                'link' => '/resources/'. self::getResourceName($product) .'/'. $product->id,
                'url' => url('/dash/resources/'. self::getResourceName($product) .'/'. $product->id),
                'mla_id' => $mlaId,
                'publication_id' => $product->id,
            ];

            if (!ErrorLog::where('is_resolved', 0)->where('errorable_id', $product->id)->where('body', $data['message'])->exists())
            {
                Notification::send(User::role('super-admin')->get(), new ErrorNotification($data));

                Http::post(env('WEBHOOK_URL'), ['data' => $data ]);

                $product->errorLogs()->create([
                    'body' => $data['message']
                ]);
            }
        }
    }

    public static function getResourceName($product)
    {
        if ($product->is_bundle)
        {
            return 'bundles';
        }

        return 'publications';
    }

}
