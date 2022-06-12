<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 17/02/2021
 * Time: 4:58 PM
 */

namespace App\Repositories;


use App\Category;

class CategoryRepository
{
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function firstOrCreate($data)
    {
        $this->category->firstOrCreate([
            'name' => $data['name']
        ], $data);
    }

    public function find($id)
    {
        return $this->category->find($id);
    }

    public function firstWithProducts($id)
    {
        return $this->category->where('id', $id)->with(['publications'])->first();
    }

    public function getWithProducts($ids)
    {
        return $this->category->whereIn('id', $ids)->with(['publications'])->get();
    }

    public function getByWooCategoryIds($ids)
    {
        return $this->category->whereIn('woo_category_id', $ids)->get();
    }
}
