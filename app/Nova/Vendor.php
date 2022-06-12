<?php

namespace App\Nova;


use App\Nova\Metrics\ConnectedAndPendingProductVendorMetrics;
use App\Nova\Metrics\TotalSalesAgainstVendorProductsOnAdminSideMetrics;
use App\Nova\Metrics\TotalSalesItemQuantityForVendorOnAdminSideMetrics;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;

use App\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Boolean;

use Laravel\Nova\Fields\HasMany;

use Sixlive\TextCopy\TextCopy;

class Vendor extends Resource
{
    public static $priority = 1;
    public static $group = 'Vendors';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Vendor::class;

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
        'id', 'name'
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
            Avatar::make('Imagen', 'image'),
            Text::make('Nombre', 'name')->rules('required', 'max:255'),
            Boolean::make('Estado', 'status'),
            Boolean::make('Account Status', 'account_status')->hideFromIndex(),
            Number::make('Telefono', 'phone')->hideFromIndex()->rules('max:9'),
            Text::make('Correo', 'email')->hideFromIndex(),
            Text::make('Direccion', 'address')->hideFromIndex(),
            Currency::make('Cotizacion', 'dollar_rate')->currency('ARS')->required()->nullable(),
            Number::make('Otros Impuestos', 'other_taxes')->hideFromIndex(),
            Number::make('Mark up', 'mark_up')->hideFromIndex()->min(1)->max(100)->step(0.01),
            Select::make('Moneda', 'currency')->options([
                'ARS' => 'Pesos',
                'USD' => 'Dolares',
            ])->default(function ($request){
                return 'ARS';
            }),
            TextCopy::make('Export URL', function (){
                return url('/feeds/'.strtolower(str_replace(' ', '_', $this->name)).'.csv'.'/'.env('VENDOR_PRODUCT_EXPORT_KEY'));
            })->truncate(1)->onlyOnDetail(),
//            Button::make('Export Products')->link(url('/feeds/'.strtolower(str_replace(' ', '_', $this->name)).'.csv'.'/'.env('VENDOR_PRODUCT_EXPORT_KEY')))->onlyOnDetail(),
            Textarea::make('Notes', 'notes'),
            Text::make('Import Frequency', function () {
                return  $this->import_frequency ? $this->import_frequency.' Minutes' : '';
            })->onlyOnDetail(),
            DateTime::make('Last Imported At', 'last_imported_at')->onlyOnDetail(),
            Number::make('Import Frequency', 'import_frequency')
                ->hideFromIndex()->hideFromDetail()->help('Vendor products import Frequency in minutes'),

            DateTime::make('Creado', 'created_at')->onlyOnDetail(),
            DateTime::make('Actualizado', 'updated_at')->onlyOnDetail(),


            //me parece que falta relacionarlo con la tabla usuarios (user_id)

            HasMany::make('Productos', 'vendorproducts', VendorProduct::class), //ojo con esta, no se si esta bien, no es con pivot?
            HasMany::make('Sales', 'saleItems', SaleItem::class)
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
            (new ConnectedAndPendingProductVendorMetrics())->onlyOnDetail(),
            (new TotalSalesAgainstVendorProductsOnAdminSideMetrics())->onlyOnDetail(),
            (new TotalSalesItemQuantityForVendorOnAdminSideMetrics())->onlyOnDetail()
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
        return [];
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
            (new Actions\ImportVendorProducts)->showOnTableRow(),
            (new Actions\Vendor\UploadBatepreciosVendorFileAction)->standalone()
        ];
    }

    public static function label() {
        return 'Vendors';
    }

    public static function singularlabel() {
        return 'Vendor';
    }
}
