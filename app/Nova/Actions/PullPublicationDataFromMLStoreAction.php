<?php

namespace App\Nova\Actions;

use App\Category;
use App\Connectors\MercadolibreConnector;
use App\Services\HelperService;
use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

use Silvanite\NovaFieldCheckboxes\Checkboxes;

class PullPublicationDataFromMLStoreAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Pull data from ML';

    protected $terminatingText = 'PUEDE RETIRAR EN NUESTRO LOCAL A LAS 24 horas hábiles DESPUÉS DE CONCRETADA LA COMPRA';

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService =  $productService;
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $product = $models->first();

        if (!$product->mla)
        {
          return Action::danger('Publication not linked with ML store');
        }

        $mercadolibreConnector = new MercadolibreConnector();


        try {

            if (in_array('description', $fields->pull_fields))
            {
                $mercadolibreProductDescription = $mercadolibreConnector->getProductDescription($product->mla)->throw();
                $product->description =  Str::before($mercadolibreProductDescription['plain_text'], $this->terminatingText);
            }

            if (count($fields->pull_fields) > 1 || !in_array('description', $fields->pull_fields))
            {
                $mercadolibreProduct = $mercadolibreConnector->getProduct($product->mla)->throw();


                if (in_array('category', $fields->pull_fields))
                {
                    $category = Category::where('mla_category_id', $mercadolibreProduct['category_id'])
                        ->first();

                    if(isset($category) && !$product->categories()->whereNotNull('mla_category_id'))
                    {
                        $product->categories()
                            ->attach($category['id']);
                    }
                }

                if (in_array('attributes', $fields->pull_fields))
                {
                    $product->attributes = HelperService::createAttributesColumnFormat($mercadolibreProduct['attributes']);
                }

                if (in_array('title', $fields->pull_fields))
                {
                    $product->name = $mercadolibreProduct['title'];
                }

                if (in_array('images', $fields->pull_fields))
                {
                    $this->productService->syncProductImages($mercadolibreProduct['pictures'], $product, true, 'secure_url');
                }
            }

            $product->save();

        }catch (RequestException $exception)
        {
            return Action::danger($exception->response['message']);
        }

    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
                Checkboxes::make('Choose fields to pull', 'pull_fields')->options([
                    'title' => 'Title',
                    'images' => 'Images',
                    'description' => 'Description',
                    'attributes' => 'Attributes',
                    'category' => 'Category',
                ])->withoutTypeCasting()->rules('required'),
        ];
    }
}
