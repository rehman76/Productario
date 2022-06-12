<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 13/04/2021
 * Time: 10:34 AM
 */

namespace App\Connectors;


use App\Connector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class MercadolibreConnector
{
    protected $apiBaseURL = 'https://api.mercadolibre.com';

    protected $apiBaseUrlForShipment = 'https://api.mercadopago.com/v1';

    public function __construct()
    {
        $this->apiConfig = Connector::where('connector', 'Mercadolibre')->first();
        $this->isValidToken();
        $this->http = Http::withToken($this->apiConfig->access_token);
    }

    /**
     * Get access token for API authorization
     * @param $accessCode
     * @return string
     */
    public function getAccessToken($accessCode)
    {
        $response = Http::asForm()->post($this->apiBaseURL . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('Mercadolibre_APP_CLIENT_ID'),
            'client_secret' => env('Mercadolibre_APP_CLIENT_SECRET'),
            'code' => $accessCode,
            'redirect_uri' => env('Mercadolibre_APP_REDIRECT_URL'),
        ]);

        return $response->successful() ?
            $this->createOrUpdateAccessToken($response->json()) :
            'Request has been failed';
    }

    /**
     * Is Token Valid as per Token validity
     */
    public function isValidToken()
    {
        if ($this->apiConfig &&
            Carbon::now()->greaterThanOrEqualTo(
                Carbon::parse($this->apiConfig->updated_at)->addSeconds($this->apiConfig->expires_in))
        ) {
            $this->refreshAccessToken($this->apiConfig->refresh_token);
        }
    }

    /**
     * Get access token for API authorization
     * @param $refreshToken
     * @return string
     */
    public function refreshAccessToken($refreshToken)
    {
        $response = Http::asForm()->post($this->apiBaseURL . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => env('Mercadolibre_APP_CLIENT_ID'),
            'client_secret' => env('Mercadolibre_APP_CLIENT_SECRET'),
            'refresh_token' => $refreshToken,
        ]);

        $this->createOrUpdateAccessToken($response->json());
    }

    /**
     *  Create or update generated token in DB
     * @param $response
     * @return string
     */
    public function createOrUpdateAccessToken($response)
    {
        Connector::updateOrCreate(
            ['connector' => 'Mercadolibre'],
            [
                'access_token' => $response['access_token'],
                'expires_in' => $response['expires_in'],
                'scope' => $response['scope'],
                'refresh_token' => $response['refresh_token'],
                'api_id' => $response['user_id'],
            ]
        );

        $this->apiConfig = Connector::where('connector', 'Mercadolibre')->first();

        return 'Access token generated & saved.';
    }


    public function searchInUserStore($filters = '')
    {
        return $this->http->get($this->apiBaseURL . '/users/' . $this->apiConfig->api_id . '/items/search' . $filters);
    }

    public function getProduct($productId)
    {
        return $this->http->get($this->apiBaseURL . '/items/' . $productId);
    }
    public function getOrder($resource)
    {
        return $this->http->get($this->apiBaseURL . $resource);
    }
    public function updateProduct($productId, $data)
    {
        return $this->http->put($this->apiBaseURL . '/items/' . $productId, $data);
    }

    public function getProductVariation($productId, $variationId)
    {
        return $this->http->get($this->apiBaseURL . '/items/' . $productId . '/variations/' . $variationId);
    }

    public function updateProductVariation($productId, $variationId, $data)
    {
        return $this->http->put($this->apiBaseURL . '/items/' . $productId . '/variations/' . $variationId, $data);
    }

    public function getShipmentLabel($shipmentId)
    {
        return $this->http->get($this->apiBaseURL . '/shipment_labels',[
            'shipment_ids'=>$shipmentId,
            'response_type'=>'pdf'
        ]);
    }

    /**
     *  Update product sku if its regenerated or update manually in app
     *
     * @param $product
     * @param $sku
     * @param null $mercadolibreProduct
     */
    public function updateProductSku($product, $sku, $mercadolibreProduct = null)
    {
        $mercadolibreProduct = $mercadolibreProduct ? $mercadolibreProduct :  $this->getProduct($product->mla);

        // update new sku in product attributes
        $skuAttribute = $this->getSKUAttribute($mercadolibreProduct['attributes']);
        if ($skuAttribute)
        {
            $this->updateProduct($product->mla, [
                "attributes" => [
                    [
                        'id' => "SELLER_SKU",
                        "value_name" => $sku
                    ]
                ]
            ]);
        }

        // update sku in product variation
        if (isset($mercadolibreProduct['variations']))
        {
            $variation = $this->getProductVariation($product->mla, $mercadolibreProduct['variations'][0]['id']);
            $variationSkuAttribute = $this->getSKUAttribute($variation['attributes']);

            if ($variationSkuAttribute)
            {
                $this->updateProductVariation($product->mla,   $mercadolibreProduct['variations'][0]['id'], [
                    "attributes" => [
                        [
                            'id' => "SELLER_SKU",
                            "value_name" => $sku
                        ]
                    ]
                ]);
            }
        }
    }


    public function getProductDescription($productId)
    {
        return $this->http->get($this->apiBaseURL . '/items/' . $productId. '/description');
    }

    public function setProductDescription($productId, $description)
    {
        return $this->http->post($this->apiBaseURL . '/items/' . $productId. '/description', [
            'plain_text' => $description
        ]);
    }


    public function createProduct($data)
    {
        return $this->http->post($this->apiBaseURL . '/items', $data);
    }

    public function getCategoryAttributes($categoryId)
    {
        return $this->http->get($this->apiBaseURL . '/categories/'.$categoryId.'/attributes');
    }


    public function getSKUAttribute($attributes)
    {
        return collect($attributes)->where('id', 'SELLER_SKU')->first();
    }

    public function getPaymentAgainstOrder($paymentId)
    {
        return $this->http
            ->get($this->apiBaseUrlForShipment . '/payments/'.$paymentId)
            ->json();
    }

    public function getShipmentCostAgainstOrder($shipmentId)
    {
        return $this->http
            ->get($this->apiBaseURL . '/shipments/'.$shipmentId.'/costs')
            ->json();
    }

    public function getShipmentAgainstOrder($shipmentId)
    {
        return $this->http
            ->get($this->apiBaseURL . '/shipments/'.$shipmentId)
            ->json();
    }

    public function getMlaParentCategories()
    {
        return $this->http->get($this->apiBaseURL . '/sites/MLA/categories')->json();
    }

    public function getCategory($categoryId)
    {
        return $this->http->get($this->apiBaseURL . '/categories/'. $categoryId)->json();
    }


}
