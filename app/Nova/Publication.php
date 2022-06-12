<?php

namespace App\Nova;

use App\Nova\Fields\DateTime;
use App\Nova\Filters\ConnectionPublicationFilter;
use App\Nova\Filters\ContainsEan;
use App\Nova\Filters\LoadPoroductsByConnectorFilter;
use App\Nova\Filters\ProductDate;
use App\Nova\Filters\ProductErrors;
use App\Nova\Filters\ProductLinked;
use App\Nova\Filters\ProductName;
use App\Nova\Filters\ProductNoteFilter;
use App\Nova\Filters\ProductPrice;
use App\Nova\Filters\ProductPriceVariation;
use App\Nova\Filters\ProductQuantity;
use App\Nova\Filters\ProductSKU;
use App\Nova\Filters\ProductStatus;
use App\Nova\Filters\ProductStockVariation;
use App\Nova\Filters\ProductType;
use App\Nova\Filters\PublicationWithDiscountFilter;
use App\Nova\Filters\PublicationByVendors;
use App\Nova\Metrics\ConnectedProductsMetrics;
use App\Nova\Metrics\ConnectorConnectedProduct;
use App\Nova\Metrics\ProductPriceTrend;
use App\Nova\Metrics\ProductStockMetrics;
use App\Nova\Metrics\ProductStockTrend;
use App\Nova\Metrics\ProductWinnerVendorMetrics;
use App\Nova\Metrics\PublicationsCount;
use App\Nova\Metrics\PublicationsPerDayMetrics;
use App\Rules\MlaValidation;
use App\VendorProduct as VendorProductModel;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use NovaButton\Button;
use ZiffMedia\NovaSelectPlus\SelectPlus;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;
use Laravel\Nova\Fields\MorphMany;
use PalauaAndSons\TagsField\Tags;
use Orlyapps\NovaMultilineText\MultilineText;

class Publication extends Resource
{

    public static $priority = 1;
    public static $group = 'Publicaciones';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
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
        'id', 'name', 'ean', 'sku', 'mla', 'tiendanube_id'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->hideFromIndex(),
            Images::make('Avatar', 'avatar') // second parameter is the media collection name
            ->conversionOnDetailView('thumb') // conversion used on the model's view
            ->conversionOnForm('thumb') // conversion used to display the image on the model's form
            ->conversionOnIndexView('thumb') // conversion used to display the image on index form
                ->fullSize(), // full size column
            Boolean::make('Enlazado', function () {
                if ($this->vendorproducts()->exists() == 1){
                    return true;
                } else{
                    return false;
                }
            }),

            Boolean::make('active')->hideWhenCreating(),
            Boolean::make('status')->showOnIndex()->showOnDetail()->hideWhenCreating()->hideWhenUpdating(),
            Boolean::make('ML','mla_status'),
            Boolean::make('Tiendanube','tiendanube_status'),
            Text::make('SKU')->rules('required')->creationRules('unique:publications,sku')->updateRules('unique:publications,sku,' . $this->id),
            Text::make('MLA', 'mla')->rules('nullable', new MlaValidation($request->resourceId)),
            MultilineText::make('Categories', function () {
                return $this->categories->map(function ($category) {
                    return $category->getHierarchy();
                })->toArray();
            })->onlyOnDetail()->highlightFirst(false),
            Text::make('Tipo', 'type', function () {
                return ucfirst($this->type);
            })->onlyOnDetail(),
            Images::make('Images', 'product_images') // second parameter is the media collection name
            ->conversionOnDetailView('thumb') // conversion used on the model's view
            ->conversionOnForm('thumb') // conversion used to display the image on the model's form
            ->fullSize() // full size column
            // validation rules for the collection of images
            ->hideFromIndex(),
            Text::make('Title', 'name')->rules('required', 'max:60'),
            Number::make('Precio', 'price')->sortable()->readonly(),
            Number::make('Price Variation %', 'price_variation')->showOnDetail()->sortable()->showOnIndex()->hideWhenCreating()->hideWhenUpdating(),
            Number::make('Precio Mínimo', 'minimum_price')->hideFromIndex()->step(0.01),

            Number::make('Descuento', 'discount') //##bug: Agrega "%" incluso si no hay nada, y tambien en el form. Lo mismo pasa con price y sale_price
            ->min(0)
                ->max(100)
                ->sortable()
                ->hideFromIndex(), //No estoy seguro si esto deberia ser ->hideFromIndex() quizas (depende si es un calculated field o no)

            Number::make('En oferta', 'sale_price')->sortable()->readonly(),

            Number::make('Stock', 'quantity')->readonly(),
            Number::make('Stock Variation %', 'quantity_variation')->showOnDetail()->sortable()->showOnIndex()->hideWhenCreating()->hideWhenUpdating(),

            Number::make('Stock Minimo', 'min_quantity')->hideFromIndex(),
            Textarea::make('Notes', 'notes'),
            Number::make('Markup')->hideFromIndex()->min(1)->step(0.01),
            Text::make('EAN')->hideFromIndex()->nullable(),
            Select::make('IVA')->options([
                '0' => '21%',
                '1' => '10.5%',
            ])->hideFromIndex(),
            Text::make('Created By', function(){
                return $this->user();
            })->showOnIndex()->showOnDetail()->canSee(function ($request) {
                return $request->user()->isSuperAdmin() || $request->user()->isManager();;
            }),
            Number::make('Otros impuestos', 'other_taxes')->nullable()->hideFromIndex(),
            Markdown::make('Descripcion', 'description'),
            File::make('Archivo adjunto', 'attachment')->nullable(),
            DateTime::make('Creado', 'created_at')->readonly()->sortable(),
            DateTime::make('Actualizado', 'updated_at')->readonly()->sortable(),
            // Heading::make('Links'),

            KeyValue::make('Attributes')->rules('json'),

            Button::make('Ver en Tienda')->link($this->tiendanube_product_url)
                ->visible($this->tiendanube_id != null && $this->tiendanube_id != '')->onlyOnDetail(),

            Button::make('Ver en Woo')->link($this->woo_product_url)
                ->visible($this->woo_product_url != null && $this->woo_product_url != '')->onlyOnDetail(),

            Button::make('Ver en ML')->link("https://articulo.mercadolibre.com.ar/MLA-" . substr($this->mla, 3))->visible($this->mla != null && $this->mla != '')->onlyOnDetail(),

            /* MLA  Premium Publication Section **/
            new Panel('MLA Premium Publication', $this->premiumProduct()->exists() ? [
                Number::make('Precio', function (){
                    return $this->premiumProduct->price;
                })->onlyOnDetail(),
                Text::make('MLA', function(){
                    return $this->premiumProduct->mla_id;
                })->onlyOnDetail(),
                Button::make('Ver en ML')->link("https://articulo.mercadolibre.com.ar/MLA-" . substr($this->premiumProduct->mla_id, 3))->onlyOnDetail(),
            ] : []),


            BelongsToMany::make('En Proveedores', 'vendorproducts', VendorProduct::class),
            /* Vendor Products Selection **/
            SelectPlus::make('Productos', 'vendorproducts', VendorProduct::class)
                ->ajaxSearchable(function ($search) {
                    return VendorProductModel::doesntHave('publications')
                        ->where(function ($query) use($search) {
                        return $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('sku', 'LIKE', "%{$search}%");
                    })->limit(10);
                })->label(function ($vendorProduct) {
                    return $vendorProduct->vendor->name . ' - ' . $vendorProduct->name . ' - ' . $vendorProduct->sku;
                })->hideFromIndex(),  // HACE QUE MUESTRE TAMBIEN EL SKU O SEA ( NOMBRE  + SKU DEL VENDOR PRODUCT)


            /* Vendor Products End **/
            BelongsToMany::make('Categorías', 'categories', Category::class),
            SelectPlus::make('Categorías', 'categories', Category::class)->ajaxSearchable(function ($search){
                return \App\Category::doesntHave('subcategory')
                    ->where(function ($query) use($search) {
                        return $query->where('name', 'LIKE', "%{$search}%");
                    })->limit(10);
            })->hideFromIndex(),

            HasMany::make('Historial de cambios', 'publicationLogs', PublicationLog::class),
            HasMany::make('Sync logs', 'syncLogs', SyncLog::class),
            HasMany::make('Sale Item', 'saleItems',   \App\Nova\SaleItem::class),
            BelongsTo::make("Ganador",
                'vendorproductwinner', VendorProduct::class)->onlyOnDetail(),
            MorphMany::make('Error Logs', 'errorLogs', ErrorLog::class)->readonly(),
            Number::make('Connections',function (){
                return $this->vendorProductsConnectedPublication();
            })->showOnDetail()->showOnIndex(),
            Tags::make('Tags'),
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
        return [
            (new PublicationsCount)->help('Esta es la cantidad de productos registrados')->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new PublicationsPerDayMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ProductWinnerVendorMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ProductStockMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ConnectedProductsMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ConnectorConnectedProduct('ML', 'mla'))->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ConnectorConnectedProduct('Tendanube', 'tiendanube_id'))->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ProductPriceTrend)->width('1/2')->onlyOnDetail()->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ProductStockTrend())->width('1/2')->onlyOnDetail()->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new ProductQuantity,
            new ProductType,
            new ProductPrice,
            new ProductDate,
            new ProductStatus,
            new ProductLinked,
            new LoadPoroductsByConnectorFilter,
            new ProductErrors,
            new ProductName,
            new ProductSKU,
            new ProductNoteFilter,
            new PublicationWithDiscountFilter,
            new ProductPriceVariation,
            new ProductStockVariation,
            new ContainsEan,
            new ConnectionPublicationFilter,
            new PublicationByVendors,
            // new ProductDiscount,
            // new ProductCategory
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
//            new Actions\ProductWooImport,
            (new Actions\GeneratePublicationSkuAction())->onlyOnDetail(),
            Actions\MapAllPublicationWithMercadolibreStoreAction::make()->standalone(),
            Actions\BulkClonePublicationForPremiumProduct::make()->standalone(),
            Actions\BulkSyncAllPublicationToWooCommerce::make()->standalone(),
            Actions\BulkStoreSyncAction::make()->standalone(),
            new Actions\MapSelectedProductsWithMercadolibreStoreAction,
            new Actions\SyncSelectedProductsMercadolibreStoreAction,
            (new Actions\ClonePublicationForPremiumProduct())->onlyOnDetail()->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            new Actions\SyncSelectedProductToTiendanubeStoreAction,
            new Actions\SyncSelectedProductsToWooCommerceStoreAction,
            new Actions\EvaluatePublicationAction,
            (new Actions\MakePublicationsInActiveAction())->onlyOnIndex(),
            resolve(Actions\PullPublicationDataFromMLStoreAction::class)->onlyOnDetail(),
            resolve(Actions\UpdateSelectedFieldsInTiendanubeStoreAction::class)->onlyOnDetail(),
            Actions\DownloadPublicationsInExcel::make()->standalone()->onlyOnIndex(),
        ];
    }

    public static function label()
    {
        return 'Publicaciones';
    }

    public static function singularlabel()
    {
        return 'Publicacion';
    }

    public function subtitle()
    {
        if (is_null($this->sale_price)) {
            return "SKU: {$this->sku} - Stock: {$this->quantity} - \${$this->price}";
        } else {
            return "SKU: {$this->sku} - Stock: {$this->quantity} - Oferta: \${$this->sale_price}";
        }

    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('is_bundle', false);
    }
}
