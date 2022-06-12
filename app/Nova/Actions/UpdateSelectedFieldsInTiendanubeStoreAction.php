<?php

namespace App\Nova\Actions;

use App\Connectors\TiendanubeConnector;
use App\Services\HelperService;
use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

use Silvanite\NovaFieldCheckboxes\Checkboxes;

class UpdateSelectedFieldsInTiendanubeStoreAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Update Fields in Tiendanube';

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

        if (!$product->tiendanube_id)
        {
          return Action::danger('Publication not linked with tiendanube store');
        }

        $teiendanubeConnector = new TiendanubeConnector();
        $selectedFields= [];

        try {

            if (in_array('description', $fields->update_field_list))
            {
                $description =  HelperService::convertStingToNewLineHtmlTag($product->description);

                $selectedFields['description'] = [
                    'en' => $description,
                    'es' => $description,
                ];
            }

            if (in_array('title', $fields->update_field_list))
            {
                $selectedFields['name'] = [
                    'en' => $product->name,
                    'es' => $product->name,
                ];
            }

            if (in_array('images', $fields->update_field_list))
            {
                HelperService::updateTnPublicationImage($product);

            }

            if ($selectedFields)
            {
                $teiendanubeConnector->update($product->tiendanube_id, $selectedFields);
            }


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
                Checkboxes::make('Choose fields to update', 'update_field_list')->options([
                    'title' => 'Title',
                    'images' => 'Images',
                    'description' => 'Description',
                ])->withoutTypeCasting()->rules('required'),
        ];
    }
}
