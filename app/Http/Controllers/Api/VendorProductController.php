<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\BulkCreateOrUpdateRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\SkuValidationRequest;
use App\Http\Resources\CreateProductResource;
use App\Http\Resources\UpdateProductResource;
use App\Jobs\BulkProductsImportToDatabaseJob;
use App\Services\VendorService;
use Illuminate\Support\Facades\Auth;
use App\Services\VendorProductService;

class VendorProductController extends Controller
{
    protected $vendorProductService;

    public function __construct(VendorProductService $vendorProductService)
    {
        $this->vendorProductService = $vendorProductService;
    }


    public function getVendorAdapterInstance($vendorName)
    {
        return (new VendorService())->getVendorAdapterInstance($vendorName);
    }


    public function bulkUpdateOrCreateProduct(BulkCreateOrUpdateRequest $request)
    {
            $vendorInstance = $this->getVendorAdapterInstance(Auth::user()->vendor['name']);
            BulkProductsImportToDatabaseJob::dispatch($vendorInstance, Auth::user()->vendor['id'], $request->all());

            return response()->json(['message' => "Your products are being processed"], 200);
    }


    public function createOrUpdateProduct($data)
    {
            $vendorInstance = $this->getVendorAdapterInstance(Auth::user()->vendor['name']);
            return $vendorInstance->createOrUpdateProduct(Auth::user()->vendor['id'], $data, null, null);
    }

    public function updateProduct($data, $sku)
    {
        $vendorInstance = $this->getVendorAdapterInstance(Auth::user()->vendor['name']);
        return $vendorInstance->createOrUpdateProduct(Auth::user()->vendor['id'] ,$data, $updateProductBySku = 1, $sku);
    }


    public function deleteProductBySku($sku)
    {
        $vendorInstance = $this->getVendorAdapterInstance(Auth::user()->vendor['name']);
        return $vendorInstance->deleteProductBySku($sku, Auth::user()->vendor['id'], null, null);
    }


    public function deleteBulkProductsBySku(SkuValidationRequest $request)
    {
        $vendorInstance = $this->getVendorAdapterInstance(Auth::user()->vendor['name']);
        $status = $vendorInstance->deleteProductBySku(null, Auth::user()->vendor['id'],1 ,$request->all());

        if($status)
        {
            return response()->json(['message'=> "Products have been deleted successfully"],200);
        }

    }


    public function store(ProductRequest $request)
    {
        $response = $this->createOrUpdateProduct($request->all());

        if(isset($response))
        {
            return new CreateProductResource($response);
        }
    }


    public function destroy($sku)
    {
        $status = $this->deleteProductBySku($sku);
        return $status == 1? response()->json(['message'=> "Product have been deleted successfully"],200): response()->json(['message'=> $status],200);
    }


    public function update(ProductRequest $request,$sku)
    {
        $productResponse = $this->updateProduct($request->validated(), $sku);
        return new UpdateProductResource($productResponse);

    }


}
