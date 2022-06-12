<?php


namespace App\Connectors;

use App\Services\WooCommerceService;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


class WooCommerceConnector
{
    public function wooCommerceAuthCheck()
    {
        return new Client(
            env('WOOCOMMERCE_STORE_URL'),
            env('WOOCOMMERCE_CONSUMER_KEY'),
            env('WOOCOMMERCE_CONSUMER_SECRET'),
            [
                'timeout' => 150, // SET TIMOUT HERE
                'wp_api' => true,
                'version' => 'wc/v3',
                'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
            ]
        );

    }

    public function createProduct($publication)
    {
        $woocommerce = $this->wooCommerceAuthCheck();
        $data = WooCommerceService::wooCommerceDataStructure($publication, $woocommerce);

        try
        {
            return $woocommerce->post('products', $data);
        }catch (HttpClientException $e)
        {
            return $e->getMessage();
        }

    }

    public function updateProduct($wooId, $publication)
    {
        $woocommerce = $this->wooCommerceAuthCheck();

        try
        {
            return $woocommerce->put('products/' . $wooId, $publication);
        }catch (HttpClientException $e)
        {
            return $e->getMessage();
        }
    }

}
