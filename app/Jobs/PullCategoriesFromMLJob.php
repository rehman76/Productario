<?php

namespace App\Jobs;

use App\Category;
use App\Connectors\MercadolibreConnector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullCategoriesFromMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * This job is to Pull categories from ML Store and
     * Add them to database , we are fetching complete Path of categories
     * like each child of the parent
     * @return void
     */

    protected $category;
    protected $mercadolibreConnector;

    public function __construct($category=null)
    {
        $this->category = $category;
        $this->queue = 'categories';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->mercadolibreConnector = new MercadolibreConnector();

        if ($this->category)
        {
            $this->getChildCategories();
        } else {
            $this->getParentCategories();
        }

    }

    public function getParentCategories()
    {
        $parentCategories = $this->mercadolibreConnector->getMlaParentCategories();

        foreach ($parentCategories as $parentCategory)
        {
            $category = Category::firstOrCreate([
                'name' => $parentCategory['name'],
                'mla_category_id' => $parentCategory['id'],
            ]);

            PullCategoriesFromMLJob::dispatch($category);
        }
    }

    public function getChildCategories()
    {
        $mlaCategory = $this->mercadolibreConnector->getCategory($this->category->mla_category_id);

        if ($mlaCategory)
        {
            foreach ($mlaCategory['children_categories'] as $childrenCategory)
            {
                $category = Category::firstOrCreate(['name' => $childrenCategory['name'], 'mla_category_id' => $childrenCategory['id']], [
                    'name' => $childrenCategory['name'],
                    'mla_category_id' => $childrenCategory['id'],
                    'parent_id' => $this->category->id
                ]);

                PullCategoriesFromMLJob::dispatch($category);
            }
        }
    }
}
