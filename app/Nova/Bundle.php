<?php

namespace App\Nova;

use App\Nova\Fields\DateTime;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaButton\Button;
use Outhebox\NovaHiddenField\HiddenField;
use Spatie\MediaLibrary\InteractsWithMedia;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class Bundle extends Resource
{
    use InteractsWithMedia;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $group = 'Publicaciones';
    public static $priority = 2;
    public static $model = \App\Publication::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'ean', 'sku'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */

    public function fields(Request $request)
    {
        // Or however you want to build the link
        return [
            ID::make()->hideFromIndex(),
            Images::make('Avatar', 'avatar')
                // second parameter is the media collection name
                ->conversionOnDetailView('thumb') // conversion used on the model's view
                ->conversionOnForm('thumb') // conversion used to display the image on the model's form
                ->conversionOnIndexView('thumb') // conversion used to display the image on index form
                ->fullSize(), // full size column
            Boolean::make('status'),
            Boolean::make('ML','mla_status'),
            Boolean::make('Tiendanube','tiendanube_status'),
            HiddenField::make('Is Bundle', 'is_bundle')
                ->defaultValue(1)->hideFromIndex()
                ->hideFromDetail(),
            Text::make('SKU')->rules('required')->creationRules('unique:publications,sku')->updateRules('unique:publications,sku,' . $this->id),
            Text::make('MLA', 'mla'),
            Text::make('Tipo', 'type', function () {
                return ucfirst($this->type);
            })->onlyOnDetail(),
            Images::make('Images', 'product_images') // second parameter is the media collection name
            ->conversionOnDetailView('thumb') // conversion used on the model's view
            ->conversionOnForm('thumb') // conversion used to display the image on the model's form
            ->fullSize() // full size column
            // validation rules for the collection of images
            ->onlyOnDetail(),
            Text::make('Title', 'name')->rules('required', 'max:60'),
            Number::make('Precio', 'price')->sortable()->readonly(),
            Number::make('Descuento', 'discount') //##bug: Agrega "%" incluso si no hay nada, y tambien en el form. Lo mismo pasa con price y sale_price
            ->min(0)
                ->max(100)
                ->sortable()
                ->hideFromIndex(), //No estoy seguro si esto deberia ser ->hideFromIndex() quizas (depende si es un calculated field o no)

            Number::make('En oferta', 'sale_price')->sortable()->readonly(),
            Number::make('quantity', 'quantity')->readonly(),
            Number::make('Stock Minimo', 'min_quantity')->hideFromIndex(),
            Textarea::make('Notes', 'notes'),
            Number::make('Markup')->hideFromIndex()->min(1)->max(100)->step(0.01),
            Select::make('IVA')->options([
                '0' => '21%',
                '1' => '10.5%',
            ])->hideFromIndex(),
            Number::make('Otros impuestos', 'other_taxes')->nullable()->hideFromIndex(),
            Markdown::make('Descripcion', 'description'),
            File::make('Archivo adjunto', 'attachment')->nullable(),
            DateTime::make('Creado', 'created_at')->onlyOnDetail(),
            DateTime::make('Actualizado', 'updated_at')->readonly()->sortable(),
            Button::make('Ver en Tienda')->link($this->tiendanube_product_url)
                ->visible($this->tiendanube_id != null && $this->tiendanube_id != '')->onlyOnDetail(),
            Button::make('Ver en ML')->link("https://articulo.mercadolibre.com.ar/MLA-" . substr($this->mla, 3))->visible($this->mla != null && $this->mla != '')->onlyOnDetail(),
            BelongsToMany::make('Categorías', 'categories', Category::class),
            SelectPlus::make('Categorías', 'categories', Category::class)
                ->hideFromIndex(),
            HasMany::make('Historial de cambios', 'publicationLogs', PublicationLog::class),
            HasMany::make('Sync logs', 'syncLogs', SyncLog::class),
            HasMany::make('Bundle Product', 'BundlePublications',
                \App\Nova\BundlePublication::class)
                ->inline()
        ];
    }


    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */


    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new Actions\EvaluateBundleAction(),
            new Actions\SyncSelectedProductsMercadolibreStoreAction,
            new Actions\SyncSelectedProductToTiendanubeStoreAction,
            resolve(Actions\PullPublicationDataFromMLStoreAction::class)->onlyOnDetail(),
            resolve(Actions\UpdateSelectedFieldsInTiendanubeStoreAction::class)->onlyOnDetail(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('is_bundle', true);
    }

    public static function label()
    {
        return 'Bundles';
    }


}
