<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 17/02/2021
 * Time: 4:57 PM
 */

namespace App\Services;


use App\Repositories\CategoryRepository;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function firstOrCreateFromWooCommerce($categories)
    {
        foreach ($categories as $category)
        {
            $this->categoryRepository->firstOrCreate([
               'name' =>  $category->name,
               'woo_category_id' =>  $category->id
            ]);
        }
    }

    public function find($id)
    {
        return $this->categoryRepository->find($id);
    }

    public function firstWithProducts($id)
    {
        return $this->categoryRepository->firstWithProducts($id);
    }

    public function getByWooCategoryIds($ids)
    {
        return $this->categoryRepository->getByWooCategoryIds($ids);
    }

    public function getWithProducts($ids)
    {
        return $this->categoryRepository->getWithProducts($ids);
    }
}