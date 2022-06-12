<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 10/02/2021
 * Time: 9:52 AM
 */

namespace App\Services;
use Illuminate\Support\Facades\DB;


class WooCommerceService
{

    public static function wooCommerceDataStructure($product, $woocommerce)
    {
        $data = [
            'regular_price' => (string) $product->price,
            'sale_price' => (string) $product->sale_price,
            'name' => $product->name,
            'sku' => $product->sku,
            'featured' => false,
            'manage_stock'=>true,
            'stock_quantity' => $product->quantity,
            'description' => $product->description
        ];
        $data['images'] = [];

        /* for feature image */
        if (!$product->getMedia('avatar')->isEmpty())
        {
            $data['images'][] = ['src' =>  $product->getMedia('avatar')[0]->getFullUrl() ];
        }
        /* for gallery images */
        if (!$product->getMedia('product_images')->isEmpty())
        {
            $productMediaItems = $product->getMedia('product_images');
            foreach ($productMediaItems as $mediaItem)
            {
                $data['images'][] = ['src' => $mediaItem->getFullUrl() ];
            }
        }


        $tagsData = [];

        if ($categories = $product->categories()->whereNull('woo_category_id')->get())
        {
            if(!$categories->isEmpty())
            {
                $categories->each(function ($category, $key) use($woocommerce, $product){
                    try
                    {

                        $rootCategoryAndPath = $category->getRootCategoryAndPath();

                        if ($rootCategoryAndPath && $rootCategoryAndPath['path'])
                        {
                            foreach (array_reverse($rootCategoryAndPath['path']) as $categoryId)
                            {
                                $category = \App\Category::with('parentCategory')->where('id', $categoryId)->first();

                                if (!$category->woo_category_id)
                                {
                                    $categoryData['name'] = $category->name;
                                    $categoryData['parent'] = $category->parentCategory ? $category->parentCategory->woo_category_id : 0;

                                    self::createCategoryOnWoo($woocommerce, $categoryData, $category);
                                }
                            }
                        }
                        DB::commit();
                    }catch (\Exception $e){
                        HelperService::logSync($product, null, $e->getMessage(), $message='', $premiumProduct = null, $connectorId = Constants::ConnectorWooCommerce);
                        DB::rollback();
                    }
                });

            }
        }

        if($tags = $product->tags)
        {
            if(!$tags->isEmpty())
            {
                $tags->each(function ($tag, $key) use($tagsData, $woocommerce){
                    try
                    {
                        $tagsData['name'] = $tag->name;

                        DB::transaction(function() use ($woocommerce, $tag, $tagsData) {
                            $response = $woocommerce->post('products/tags', $tagsData);
                            $tag->woo_tag_id = $response->id;
                            $tag->save();
                        });
                        DB::commit();
                    }catch (\Exception $e){
                        DB::rollback();
                    }
                });

            }

        }

        $categories = $product->categories()->get(['categories.woo_category_id as id'])->toArray();

        $tagsAgainstPublication = $product->tags()->get(['tags.woo_tag_id as id'])->toArray();

        foreach($tagsAgainstPublication as $key=>$value)
        {
            $data['tags'][] = array_diff_key($tagsAgainstPublication[$key],array_flip((array)['pivot']));
        }

        $data['categories'] = $categories;

        return $data;
    }

    /**
     * @param $product
     * @return array
     */
    public function updateProductData($product)
    {
        // columns names mapping
        $wooColumns = ['price' => 'regular_price', 'sale_price' => 'sale_price',
                'quantity' => 'stock_quantity' , 'name' => 'name', 'description' => 'description'];

        $data = [];

        // for data fields
        foreach ($wooColumns as $appColumn => $wooColumn )
        {
            if (in_array($appColumn ,nova_get_setting('woo_update_fields')))
            {
                $data[$wooColumn] = $appColumn=='quantity' ? $product[$appColumn] : (string) $product[$appColumn];
            }
        }

        /* for main images */
        if (in_array('images' ,nova_get_setting('woo_update_fields')) &&
                    !$product->getMedia('avatar')->isEmpty())
        {
            $data['images'][] = ['src' =>  $product->getMedia('avatar')[0]->getFullUrl() ];
        }

        /* for gallery images */
        if (in_array('images' ,nova_get_setting('woo_update_fields')) &&
            !$product->getMedia('product_images')->isEmpty())
        {
            $productMediaItems = $product->getMedia('product_images');
            foreach ($productMediaItems as $mediaItem)
            {
                $data['images'][] = ['src' =>  $mediaItem->getFullUrl() ];
            }
        }


        return $data;
    }

    public static function createCategoryOnWoo($woocommerce, $categoryData, $category)
    {
        DB::transaction(function() use ($woocommerce, $categoryData, $category) {
            $response = $woocommerce->post('products/categories', $categoryData);
            $category->woo_category_id = $response->id;
            $category->save();
        });
    }
}
