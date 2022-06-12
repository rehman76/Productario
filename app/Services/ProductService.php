<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 01/02/2021
 * Time: 6:56 PM
 */

namespace App\Services;

use App\BundlePublication;
use App\Publication;
use App\PublicationLog;
use App\Repositories\PublicationRepository;
use Illuminate\Support\Facades\Log;

class ProductService
{

    protected $publicationService;

    protected $helperService;

    public function __construct(PublicationRepository $productRepository,
                                CategoryService $categoryService, HelperService $helperService)
    {
        $this->productRepository = $productRepository;
        $this->categoryService = $categoryService;
        $this->helperService= $helperService;
    }

    /**
     * Get all products
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->productRepository->all();
    }


    /**
     *  Get all products paginated
     * @param $productIds
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function allPaginated($filters)
    {
        return $this->productRepository->allPaginated($filters);
    }


    /**
     *  Get single product by any column filter
     * @param $column
     * @param $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function first($column, $value)
    {
        return $this->productRepository->first($column, $value);
    }

    /**
     *  Declare the winner vendor product for the publication
     *  If
     * @param $publication
     * @return  void
     */
    public static function winnerVendorProductEvaluation(Publication $publication)
    {
        //todo uncomment the code if the needs arises  for this feature on vendor product side
//        if ($vendorProduct->ean && $product = PublicationRepository::getByEAN($vendorProduct->ean)) {
//            static::mapWinnerVendorProductDetailsToProduct($vendorProduct, $product);
//        } else
        if ($publication->is_bundle) {
            return;
        }

        $vendorProducts = $publication->enabledVendorProducts;
        if ($vendorProducts->isEmpty()) {
            // no connected and enabled products found
            static::makePublicationPaused($publication, 'Paused, No active Vendor Product');
            return;
        }
        $winnerVendorProduct = $vendorProducts->where('quantity', '>', 0)->where('calculated_retail_price', '>' , 0)->sortBy('calculated_retail_price')->first();
        $winnerVendorProduct = $winnerVendorProduct ?? $vendorProducts->where('id', $publication->winner_vendor_product_id)->first();

        if ($winnerVendorProduct) {
            static::mapWinnerVendorProductDetailsToProduct($winnerVendorProduct, $publication);
            return;
        }

        static::makePublicationPaused($publication);
    }

    public static function BundleStockCheck($bundleId, $whereRequest)
    {
        $BundlePublicationObject = static::getBundlePublication($bundleId);

        if (!$BundlePublicationObject->isEmpty()) {
            $BundleStock = array();
            foreach ($BundlePublicationObject as $ob) {
                $Publication = $ob->publication;
                if (isset($Publication)) {
                    $BundleStock['quantity'][] = $Publication->quantity ? $Publication->quantity / $ob->qty : 0;
                } else {
                    $BundleStock['quantity'][] = 0;
                }
                $BundleStock['price'] [] = $Publication->price ? $Publication->price * $ob->qty : 0;
            }

            if (count($BundleStock) > 0) {
                $QtyPrice = static::GetBundlePriceAndQty($BundleStock);
                if ($QtyPrice['quantity'] == 0) {
                    $QtyPrice = ['quantity' => 0, 'price' => null,
                        'status' => 0,  'sale_price' => null
                    ];
                }
                static::updateBundleStock($QtyPrice, $BundlePublicationObject->first()->bundle);
            }
        } else {
            Publication::where('id', $bundleId)->update(['quantity' => 0, 'price' => null,
                'status' => 0,  'sale_price' => null
            ]);
        }
        return;
    }

    public static function getBundlePublication($bundleId)
    {
        return BundlePublication::with('publication', 'bundle')
            ->where('bundle_id', $bundleId)->get();
    }


    public static function GetBundlePriceAndQty($BundleStock)
    {
        return ['quantity' => min($BundleStock['quantity']),
            'price' => array_sum($BundleStock['price']),
            'status' => 1
        ];
    }

    public static function getBundleProduct($publication_id)
    {
        return Publication::where('id', $publication_id)->select('quantity', 'price')->first();
    }

    public static function updateBundleStock($obj, $publication)
    {
        if ($obj['price'])
        {
            $obj['sale_price'] = $publication->discount ? round($obj['price'] - ($obj['price'] * $publication->discount / 100)) : null;
        }

        $publication->update($obj);

        self::addProductLog($publication);

        return;
    }


    public static function mapWinnerVendorProductDetailsToProduct($winnerVendorProduct, $publication)
    {
        $oldQuantityValue = $publication->quantity;
        $oldPriceValue = $publication->price;
        $publication->update([
            "price" => self::getPublicationPrice($winnerVendorProduct, $publication),
            "quantity" => $winnerVendorProduct->quantity,
            "status" => 1,
            'sale_price' => $publication->discount ?
                round($winnerVendorProduct->calculated_retail_price - ($winnerVendorProduct->calculated_retail_price * $publication->discount / 100)) :
                null
        ]);
        $publication->winner_vendor_product_id = $winnerVendorProduct->id; // as foreign key not directly update by model instance
        $publication->save();
        self::addProductLog($publication, null, $oldQuantityValue, $oldPriceValue);
    }

    public static function makePublicationPaused($publication, $message = null)
    {
        $publication->update([
            "status" => 0,
            "quantity" => 0
        ]);
        $publication->winner_vendor_product_id = null; // as foreign key not directly update by model instance
        $publication->save();

        self::addProductLog($publication, $message);
    }

    public static function addProductLog($publication, $message = null, $oldQuantityValue = null, $oldPriceValue = null)
    {
        if (!$publication->wasRecentlyCreated && ($publication->wasChanged('price') ||
                $publication->wasChanged('quantity') || $publication->wasChanged('status'))) {
            PublicationLog::create([
                'publication_id' => $publication->id,
                'price' => $publication->price ?? 0,
                'stock' => $publication->quantity ?? 0,
                'message' => ProductService::publicationLogMessage($publication, $oldQuantityValue,$publication->quantity, $oldPriceValue, $publication->price). $message,
            ]);
        }
    }

    public static function publicationLogMessage($publication, $oldQuantityValue = null, $newQuantityValue = null ,$oldPriceValue = null, $newPriceValue = null): string
    {
        $message = '';

        if ($publication->wasChanged('price'))
        {
            $priceIncreaseMessage = 'Price up.';
            $priceDecreaseMessage = 'Price down.';
            if(HelperService::sign($oldPriceValue, $newPriceValue) == 1){
                $message = $message.$priceIncreaseMessage ;
            }else if( HelperService::sign($oldPriceValue, $newPriceValue) == -1){
                $message = $message.$priceDecreaseMessage;
            }
        }

        if ($publication->wasChanged('quantity'))
        {
            $stockIncreaseMessage = 'Stock up.';
            $stockDecreaseMessage = 'Stock down.';
            if(HelperService::sign($oldQuantityValue, $newQuantityValue) == 1){
                $message = $message. $stockIncreaseMessage;
            }else if( HelperService::sign($oldQuantityValue, $newQuantityValue) == -1){
                $message = $message. $stockDecreaseMessage;
            }
        }

        if($publication->wasChanged('status'))
        {
            $message = $message. $publication->status ? 'Status Activated .' : 'Status Paused .';
        }

        if ($publication->wasChanged('winner_vendor_product_id'))
        {
            $message = $message. 'New Winner Vendor Product'.' '.$publication->vendorproductwinner->name;
        }

        return $message;
    }


    /****** Import Products from Woo Commerce ****/

    /**
     *  Import Products Create/ Update Decision
     * @param $wooProducts
     * @return void
     */
    public function importProductFromWooCommerce($wooProducts)
    {
        foreach ($wooProducts as $wooProduct) {
            $product = $this->productRepository->first('woo_id', $wooProduct->id);
            if ($product) {
                $this->updateProductFromImport($wooProduct, $product);
            } else {
                $this->storeProductFromImport($wooProduct);

            }
        }
    }

    /**
     * Store woo product to Database
     * @param $wooProduct
     */
    public function storeProductFromImport($wooProduct)
    {
        $product = $this->productRepository->store([
            'name' => $wooProduct->name,
            'sku' => $wooProduct->sku,
            'woo_id' => $wooProduct->id,
            'description' => $wooProduct->description,
            'woo_product_url' => $wooProduct->permalink,
        ]);
        $this->syncProductCategoriesWithWoo($wooProduct, $product);
        $this->syncProductImages($wooProduct->images, $product, false);
    }

    /** Update woo product
     * @param $wooProduct
     * @param $product
     */
    public function updateProductFromImport($wooProduct, $product)
    {
        $this->productRepository->update(
            ['name' => 'woo_id', 'value' => $wooProduct->id], [
            'name' => $wooProduct->name,
            'sku' => $wooProduct->sku,
            'description' => $wooProduct->description,
            'woo_product_url' => $wooProduct->permalink
        ]);
        $this->syncProductCategoriesWithWoo($wooProduct, $product);
        $this->syncProductImages($wooProduct->images, $product, true);
    }

    /**
     * @param $wooProduct
     * @param $product
     */
    public function syncProductCategoriesWithWoo($wooProduct, $product)
    {
        $categories = $this->categoryService->getByWooCategoryIds(collect($wooProduct->categories)->pluck('id')->toArray());
        $product->categories()->sync($categories->pluck('id')->toArray());
    }

    /**
     * @param array $images
     * @param object $product
     * @param boolean $isProductUpdate
     * @param string $urlKey
     * @return void
     */
    public function syncProductImages($images, $product, $isProductUpdate, $urlKey = 'src')
    {
        $isProductUpdate ? $product->clearMediaCollection('product_images') : '';

        $this->helperService->hashedMarketingImages(true, $images, $urlKey, $product);
    }

    /****** ENd Import Products from Woo Commerce ****/

    public static function isLinkWithBundle($bundlePublication)
    {
        return BundlePublication::where('publication_id', $bundlePublication->id)->get();
    }

    public static function UpdateBundlePublishName($bundlePublication)
    {
        $publicationName = Publication::where('id', $bundlePublication->publication_id)->pluck('name')->first();
        BundlePublication::where('id', $bundlePublication->id)->update(['publication_name' => $publicationName]);

    }

    public static function getPublicationPrice($vendorProduct, $publication)
    {
        if ($publication->minimum_price && $publication->minimum_price > $vendorProduct->calculated_retail_price) {
            return $publication->minimum_price;
        }

        return $vendorProduct->calculated_retail_price;
    }
}
