<?php
namespace App\Connectors;

use App\Connector;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Created by PhpStorm.
 * User=> aliraza
 * Date=> 08/04/2021
 * Time=> 3:42 PM
 */
class TiendanubeConnector
{
    protected $apiBaseURL = 'https://api.nuvemshop.com.br/v1';

    public function __construct()
    {
        $this->apiConfig = Connector::where('connector', 'Tiendanube')->first();
        $this->http = Http::withHeaders([
            'Authentication' => 'bearer ' . $this->apiConfig->access_token,
            'Content-Type' => 'application/json',
        ]);

        $this->apiBaseURLWithStore = $this->apiBaseURL . '/' . $this->apiConfig->api_id;
    }

    /**
     * @param $accessCode
     * @return string
     */
    public static function getAccessToken($accessCode)
    {
        $response = Http::asForm()->post('https://www.tiendanube.com/apps/authorize/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('Tiendanube_APP_CLIENT_ID'),
            'client_secret' => env('Tiendanube_APP_CLIENT_SECRET'),
            'code' => $accessCode,
        ]);

        if ($response->successful()) {
            Connector::updateOrCreate(
                ['connector' => 'Tiendanube'],
                [
                    'access_token' => $response['access_token'],
                    'scope' => $response['scope'],
                    'api_id' => $response['user_id'],
                ]
            );
        }

        return $response->successful() ? 'Auth Successfully Done' : 'Auth Faild';
    }

    // products
    /**
     * @param $productId
     * @return \Illuminate\Http\Client\Response
     */
    public function getProduct($productId)
    {
        $response =  $this->http->get($this->apiBaseURLWithStore . '/products/' . $productId);
        $this->setResponseParametersInCacheForApiLimit($response);
        return $response;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function createProduct($product)
    {
        $description =  HelperService::convertStingToNewLineHtmlTag($product->description);

        $productData = [
            "name" => [
                'en' => $product->name,
                'es' => $product->name,
            ],
            "description" => [
                'en' => $description,
                'es' => $description,
            ],
            "variants" => [
                [
                    'price' => $product->price,
                    "stock_management" => true,
                    "stock" => $product->quantity,
                    "sku" => $product->sku
                ]
            ],
            "published" => true,
            "seo_title" => $product->name,
            "seo_description" => $product->description
        ];
        $productData['images'] = [];
        $imagePosition = 1;

        /* for feature image */
        if (!$product->getMedia('avatar')->isEmpty()) {
            $productData['images'][] = [
                'position' => $imagePosition,
                'src' => $product->getMedia('avatar')[0]->getFullUrl()
            ];
        }

        /* for gallery images */
        if (!$product->getMedia('product_images')->isEmpty()) {
            $productMediaItems = $product->getMedia('product_images');
            foreach ($productMediaItems as $mediaItem) {
                $imagePosition++;
                $productData['images'][] = [
                    'position' => $imagePosition,
                    'src' => $mediaItem->getFullUrl()
                ];
            }
        }

        if ($categories = $product->categories()->get()) {
            $productData['categories'] = [];
            foreach ($categories as $category) {
                if (!$category->tiendanube_category_id) {
                    $category->tiendanube_category_id = $this->createCategory($category->name);
                    $category->save();
                }
                array_push($productData['categories'], $category->tiendanube_category_id);
            }
        }

        $response = $this->http->post($this->apiBaseURLWithStore . '/products', $productData);
        $this->setResponseParametersInCacheForApiLimit($response);
        return [
            'response' => $response,
            'productData' => $productData
        ];

    }

    /**
     * @param $productId
     * @param $variantId
     * @param $data
     * @return \Illuminate\Http\Client\Response
     */
    public function updateProductVariation($productId, $variantId, $data)
    {
        $response =  $this->http->put($this->apiBaseURLWithStore . '/products/' . $productId . '/variants/' . $variantId, $data);
        $this->setResponseParametersInCacheForApiLimit($response);

        return $response;
    }


    /**
     * @param $productId
     * @return \Illuminate\Http\Client\Response
     */
    public function deleteProduct($productId)
    {
        return $this->http->delete($this->apiBaseURLWithStore . '/products/' . $productId);
    }


    public function search($text)
    {
        return $this->http->get($this->apiBaseURLWithStore . '/products?q=' . $text);
    }

    public function get($filters)
    {
        return $this->http->get($this->apiBaseURLWithStore . '/products' . $filters);
    }


    public function update($id, $data)
    {
        $response =  $this->http->put($this->apiBaseURLWithStore . '/products/'.$id, $data);
        $this->setResponseParametersInCacheForApiLimit($response);

        return $response;
    }
    /// Categories

    /**
     * @param $categoryName
     * @return mixed
     */
    public function createCategory($categoryName)
    {
        $response = $this->http->post($this->apiBaseURLWithStore . '/categories', [
            "name" => [
                'en' => $categoryName,
                'es' => $categoryName,
            ],
        ]);
        $this->setResponseParametersInCacheForApiLimit($response);

        return $response['id'];
    }

    public function updateCategory($id, $data)
    {
        return $this->http->put($this->apiBaseURLWithStore . '/categories/'. $id, $data);
    }

    public function getProductImages($id)
    {
        return $this->http->get($this->apiBaseURLWithStore . '/products/' . $id. '/images');
    }

    public function deleteProductImage($productId, $imageId)
    {
        return $this->http->delete($this->apiBaseURLWithStore . '/products/' . $productId. '/images/'. $imageId);
    }

    public function createProductImage($productId, $data)
    {
        return $this->http->post($this->apiBaseURLWithStore . '/products/' . $productId . '/images', $data );
    }

    public function generateTnCouponCode($randomCouponCode, $couponRequest)
    {
        $response = $this->http->post($this->apiBaseURLWithStore . '/coupons',[
                'code'=>  $randomCouponCode,
                 'type' => "percentage",
                 'value' => $couponRequest->discount_percentage,
                 'max_uses' => $couponRequest->max_usage
        ]);

        $this->setResponseParametersInCacheForApiLimit($response);

        return $response;
    }


    public function setResponseParametersInCacheForApiLimit($response)
    {
        if ($response->header('X-Rate-Limit-Remaining') <= 10)
        {
            Cache::put(
                'tn-remaining-time-retry',
                now()->addMilliseconds($response->header('X-Rate-Limit-Reset'))->timestamp,
                (int) $response->header('X-Rate-Limit-Reset')/1000
            );
        }
    }

}
