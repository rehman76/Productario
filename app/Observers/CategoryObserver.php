<?php

namespace App\Observers;

use App\Category;
use App\Connectors\TiendanubeConnector;
use Illuminate\Support\Facades\Log;

class CategoryObserver
{
    /**
     * Handle the category "created" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
        //
    }

    /**
     * Handle the category "updated" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {

        if ($category->google_shopping_category)
        {
            $tiendanubeConnector = new TiendanubeConnector();

            $tiendanubeConnector->updateCategory($category->tiendanube_category_id, [
                "name" => [
                    'en' => $category->name,
                    'es' => $category->name,
                ],
                "google_shopping_category" => $category->google_shopping_category
            ]);
        }
    }

    /**
     * Handle the category "deleted" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the category "restored" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        //
    }

    /**
     * Handle the category "force deleted" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
        //
    }
}
