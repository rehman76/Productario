<?php

namespace App\Jobs;

use App\Category;
use App\Connectors\TiendanubeConnector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCategoriesInTiendanubeStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $categories, $tiendanubeConnector;
    public function __construct($categories = null)
    {
        $this->onQueue('connector');
        $this->categories = $categories;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->tiendanubeConnector = new TiendanubeConnector();

        if ($this->categories)
        {
            foreach ($this->categories as $category)
            {
                $this->updateCategory($category);
                $this->createCategory($category);
            }
        } else {
                $this->syncAllCategories();
        }
    }

    public function syncAllCategories()
    {
        // divide the sync in sub jobs
        $iteration = 1;
        $categoryCount = Category::count();
        for ($offset =0 ; $offset < $categoryCount ; $offset = $offset+5)
        {
            $categories = Category::offset($offset)->limit(5)->get();
            dispatch((new SyncCategoriesInTiendanubeStoreJob($categories))->delay(now()->addSeconds($iteration*5)));
            $iteration = $iteration + 1;
        }
    }

    public function createCategory($category)
    {
        if (!$category->tiendanube_category_id)
        {
            $tiendanubeCategoryId = $this->tiendanubeConnector->createCategory($category->name);
            $category->tiendanube_category_id = $tiendanubeCategoryId;
            $category->save();
        }
    }

    public function updateCategory($category)
    {
        if ($category->tiendanube_category_id)
        {
            $response = $this->tiendanubeConnector->updateCategory($category->tiendanube_category_id, [
                "name" => [
                    'en' => $category->name,
                    'es' => $category->name,
                ],
                "google_shopping_category" => $category->google_shopping_category
            ]);

            !$response->successful() ? Log::info($category->id . '  '.  $response->body()) : '';
        }
    }
}
