<?php

namespace App\Jobs;

use App\Category;
use App\Connectors\MercadolibreConnector;
use App\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MapMLAPublicationWithMLCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mercadolibreConnector;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $publication;

    public function __construct($publication = null)
    {
        $this->publication = $publication;
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

        if ($this->publication)
        {
            $this->attachCategoryToPublication($this->publication);
        } else {
            $this->getPublication();
        }

    }

    public function getPublication()
    {
        Publication::all()->whereNotNull('mla')->map(function ($publication){
            MapMLAPublicationWithMLCategory::dispatch($publication);
        });
    }

    public function attachCategoryToPublication($publication)
    {
        $response = $this->mercadolibreConnector->getProduct($publication->mla);

        if($response->successful())
        {
            $category = Category::where('mla_category_id', $response['category_id'])
                ->first();

            if(isset($category))
            {
//
//                        $publication->categories()
//                                    ->detach();
//
                $publication->categories()
                    ->attach($category['id']);
            }
        }
    }
}
