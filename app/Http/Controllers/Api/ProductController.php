<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(ProductService $productService,
                                CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * Get All Products by Pages
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        if ($request['category_id'])
        {
            $filters['productIds'] = $this->getProductIdsByCategories(explode(',', $request['category_id']));
        }

        return ProductResource::collection($this->productService->allPaginated($filters));
    }

    /**
     * Get All Products by Pages
     * @param $id
     * @return ProductResource
     */
    public function show($id)
    {
        return new ProductResource($this->productService->first('id', $id));
    }

    /**
     * @param $categoryIds
     * @return array
     */
    public function getProductIdsByCategories($categoryIds)
    {
        $categories = $this->categoryService->getWithProducts($categoryIds);

        $productIds = [];
        foreach ($categories as $category)
        {
             isset($category->products) ? $productIds = array_merge($productIds,$category->products->pluck('id')->toArray()) : [];
        }

        return $productIds;
    }

}
