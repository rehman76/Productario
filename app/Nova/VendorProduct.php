<?php

namespace App\Nova;

use App\Nova\Filters\ContainsEan;
use App\Nova\Filters\ProductDate;
use App\Nova\Filters\ProductPrice;
use App\Nova\Filters\ProductPriceVariation;
use App\Nova\Filters\ProductQuantity;
use App\Nova\Filters\ProductStockVariation;
use App\Nova\Filters\VendorProductLinkedFilter;
use App\Nova\Filters\VendorProductsByVendorFilter;
use App\Nova\Metrics\ConnectedAndPendingProductVendorMetrics;
use App\Nova\Metrics\NotConnectedVendorProductCountMetric;
use App\Nova\Metrics\VendorProductPriceTrend;
use App\Nova\Metrics\VendorProductAveragePriceMetrics;
use App\Nova\Metrics\VendorProductConnectedMetrics;
use App\Nova\Metrics\VendorProductStockMetircs;
use App\Nova\Metrics\VendorProductStockTrend;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use App\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Fields\BelongsTo;


class VendorProduct extends Resource
{

    public static $group = 'Vendors';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\VendorProduct::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return $this->name;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'sku'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Producto', 'name'),
            Text::make('SKU'),
            Boolean::make('Estado', 'status')->default(function ($request) {
                return true;
            })->showOnIndex()->showOnDetail()->hideWhenUpdating()->hideWhenCreating(),
            Boolean::make('Activa', 'active')->showOnDetail()->canSee(function ($request) {
                return !$request->user()->isVendor();
            }),
            Images::make('Image', 'vendor_product_images') // second parameter is the media collection name
            ->fullSize() // full size column
            ->hideFromIndex(),
            Trix::make('Descripcion', 'description'),
            Text::make('EAN')->hideFromIndex(),
            Select::make('IVA')->options([
                '0' => '21%',
                '1' => '10.5%',
            ])->onlyOnDetail(),
            //HABRIA QUE AGREGAR UN CALCULATED FIELD PARA PRICE Y SALE PRICE.. Y EL RESTO ONLYONDETAIL()
            Number::make('Otros impuestos','other_taxes')->onlyOnDetail()->nullable(),
            Text::make('COSTO', function () {
                return '$'.$this->price;
            })->showOnDetail()->showOnIndex()->canSee(function ($request) {
                return !$request->user()->isCurator();
            }),
            Text::make('COSTO Variation', function () {
                return $this->price_variation. '%';
            })->showOnDetail()->showOnIndex()->canSee(function ($request) {
                return !$request->user()->isCurator();
            }),
            Text::make('Calculado Retail Price', function (){
                return $this->getCalculatedRetailPrice();
            })->sortable()->showOnDetail()->showOnIndex(),
            Text::make('Connected By', function (){
                if($latestUser = $this->latestUser())
                {
                    return "<a href='/dash/resources/users/{$latestUser['id']}' class='no-underline dim text-primary font-bold'>{$latestUser['first_name']} {$latestUser['last_name']}</a>";
                }

                return null;
            })->asHtml()->showOnIndex()->showOnDetail()->canSee(function ($request) {
                return $request->user()->isSuperAdmin() || $request->user()->isManager();
            }),
            Number::make('Descuento', 'discount')->onlyOnDetail()->canSee(function ($request) {
                return !$request->user()->isCurator();
            }),
            Text::make('Link', function () {
                $link = $this->link;
                return $link ?  "<a href='{$link}'>Link al Proveedor</a>" : '';
            })->asHtml()->onlyOnDetail(),
            Number::make('Stock', 'quantity')->rules('required')->sortable(),
            Text::make('Stock Variation', function () {
                return $this->quantity_variation. '%';
            })->showOnDetail()->showOnIndex()->canSee(function ($request) {
                return !$request->user()->isCurator();
            }),
            Number::make('Stock Minimo', 'min_quantity')->onlyOnDetail()->canSee(function ($request) {
                return !$request->user()->isCurator();
            }),
            Text::make('Connected Publication', function (){
                $publication = $this->publication();
                if(isset($publication))
                {
                    return "<a href='/dash/resources/publications/{$publication['id']}' class='no-underline dim text-primary font-bold'>{$publication['name']}</a>";
                }
            })->asHtml()->onlyOnDetail(),

            Textarea::make('Notes', 'notes'),
            DateTime::make('Creado', 'created_at')->onlyOnDetail()->sortable(),
            DateTime::make('Ultima actualizacion', 'updated_at')->exceptOnForms()->sortable(),
            BelongsTo::make('Vendor', 'vendor', Vendor::class)->searchable(),
            HasMany::make('Publication Logs','vendorProductLogs',VendorProductLog::class),
            HasMany::make('Connection Logs','vendorConnectionLogs',PublicationVendorProductLinkedLog::class)
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new VendorProductConnectedMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ConnectedAndPendingProductVendorMetrics())->canSee(function ($request) {
                return $request->user()->isVendor();
            }),
            (new VendorProductAveragePriceMetrics())->help('Average value from calculated retail price')->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new VendorProductStockMetircs())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new NotConnectedVendorProductCountMetric())->canSee(function ($request) {
                return $request->user()->isSuperAdmin() || $request->user()->isVendor();
            }),
            (new VendorProductPriceTrend)->width('1/2')->onlyOnDetail(),
            (new VendorProductStockTrend())->width('1/2')->onlyOnDetail(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new ProductQuantity,
            new ProductPrice,
            new ProductDate,
            new VendorProductLinkedFilter,
            new ProductPriceVariation,
            new ProductStockVariation,
            new VendorProductsByVendorFilter,
            new ContainsEan,

        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new Actions\ActiveOrInActiveVendorProducts
        ];
    }

    public static function label() {
        return 'Products';
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if($request->user()->isVendor())
            return $query->where('vendor_id', $request->user()->vendor_id);

    }
}
