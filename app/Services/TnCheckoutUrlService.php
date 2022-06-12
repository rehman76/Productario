<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 25/10/2021
 * Time: 2:41 PM
 */

namespace App\Services;


use App\Connectors\TiendanubeConnector;
use App\Publication;
use App\TnCheckoutUrl;
use App\TnCheckoutUrlPublication;

class TnCheckoutUrlService
{

    public static function UpdatePublicationName($tnCheckoutUrlPublication)
    {
        $publicationName = Publication::where('id', $tnCheckoutUrlPublication->publication_id)->pluck('name')->first();
        TnCheckoutUrlPublication::where('id', $tnCheckoutUrlPublication->id)->update(['publication_name' => $publicationName]);
    }

    public static function getTnPublicationVariantId($tnCheckoutUrlPublication)
    {
        if (($tnCheckoutUrlPublication->wasRecentlyCreated || $tnCheckoutUrlPublication->wasChanged('publication_id'))
                                && $tnCheckoutUrlPublication->publication->tiendanube_id)
        {
            $response = (new TiendanubeConnector())->getProduct($tnCheckoutUrlPublication->publication->tiendanube_id);
            if ($response->successful())
            {
                TnCheckoutUrlPublication::where('id', $tnCheckoutUrlPublication->id)
                                    ->update(['tiendanube_variant_id' => $response['variants'][0]['id']]);
            }

        }
    }

    public static function generateCheckoutUrl($tnCheckoutUrl)
    {
        TnCheckoutUrl::withoutEvents(function () use ($tnCheckoutUrl) {
            $tnCheckoutUrl->url = 'https://go.bateprecios.com/checkout/'. $tnCheckoutUrl->id;
            $tnCheckoutUrl->save();
        });

    }
}