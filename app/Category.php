<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table    = "categories";

    protected $hidden = ['pivot'];

    protected $with = ['parentCategory'];

    protected $fillable = [
        'name','woo_category_id','tiendanube_category_id','google_shopping_category', 'updated_at',
        'created_at', 'parent_id', 'mla_category_id'
    ];

    public function getHierarchy()
    {
        $category = $this;

        $type = $category->parentCategory ? '(MLA)' : '(Custom)';

        $hierarchy = $category->name . ' ' . $type;

        while ($category->parentCategory) {
            $hierarchy = $category->parentCategory->name . ' > ' . $hierarchy;
            $category = $category->parentCategory;
        }

        return $hierarchy;
    }


    public function publications()
    {
        return $this->belongsToMany('App\Publication', 'publication_category');
    }

    public function subcategory()
    {
        return $this->hasMany('App\Category', 'parent_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo('App\Category', 'parent_id', 'id');
    }

    public function getRootCategoryAndPath()
    {
        $category = $this;
        $path = [];
        array_push($path, $category->id);

        while ($category->parentCategory) {
            $category = $category->parentCategory;
            array_push($path, $category->id);
        }

        return $category->id != $this->id ? [
            'path' => $path,
            'rootCategory' => $category,
        ] : null;
    }

}
