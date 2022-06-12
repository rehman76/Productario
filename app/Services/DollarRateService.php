<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;


/**
 * Dollar Rate Service.
 *
 * @author Theodore Yaosin <theodoreyaosin@outlook.com>
 */
class DollarRateService {
    /**
     * Returns current dollar rate.
     *
     * @return float
     */
    static function get() {
        $dollar_rate = '';
        $goutteClient = new Client();
        $guzzleClient = new GuzzleClient(array(
            'timeout' => 60,
        ));
        $goutteClient->setClient($guzzleClient);
        $crawler = $goutteClient->request('GET', 'https://dolar-plus.com/');
        $nodes = $crawler->filterXPath('//*[@id="article"]/div[1]/div[2]/div/div[2]/span[2]');
        $nodes->each(function ($item) use(&$dollar_rate) {
            $dollar_rate = (float)str_replace('$', '', $item->text());
        });

        return $dollar_rate;
    }
}
