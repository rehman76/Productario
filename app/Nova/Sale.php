<?php

namespace App\Nova;

use App\Nova\Fields\DateTime;
use App\Nova\Metrics\SaleItemsAmountMetrics;
use App\Nova\Metrics\SaleItemsQuantityMetrics;
use App\Nova\Metrics\SalesByPublication;
use App\Nova\Metrics\SalesByVendorPartitionMetrics;
use App\Nova\Metrics\TotalSalesMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use NovaButton\Button;

class Sale extends Resource
{

    public static $group = 'Sales';

    public static $displayInNavigation = true;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Sale::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'order_id'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return false
     */

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }


    public function fields(Request $request)
    {
        return [
            Text::make('ORDER ID', 'order_id', function () {
                return '#' . ($this->order_id);
            }),
            Text::make('Status', 'status')->required(),
            Number::make('Total Amount', 'total_amount'),
            Number::make('Shipping Bonification', 'shipping_cost'),
            Number::make('Total Taxes', 'taxes'),
            Text::make('Vendor Cost', function () {
                return  $this->SaleHasItems()->sum('vendor_product_cost');
            }),
            Number::make('ML Commissions', 'ml_commissions'),
            Number::make('Total Expense', 'expense'),
            Number::make('Total Profit', 'profit'),
            KeyValue::make('Attributes')->rules('json'),
            DateTime::make('Order Date', 'date_created'),
            Button::make('Ver Shipping Label')->link($this->sale_label)->canSee(function () {
                return isset($this->sale_label);
            })->onlyOnDetail(),
            new Panel('Buyer', [HasOne::make('Buyer', 'SaleHasBuyer',   \App\Nova\SaleBuyer::class),]),
            new Panel('Order Items', [HasMany::make('Items', 'SaleHasItems', \App\Nova\SaleItem::class)]),
            new Panel('Payments', [HasMany::make('Payments', 'SaleHasPayment', \App\Nova\SalePayment::class)]),
            new Panel('Sale logs', [HasMany::make('Sale logs', 'saleLogs', \App\Nova\SaleLog::class)]),
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
            (new SaleItemsAmountMetrics()),
            (new SaleItemsQuantityMetrics()),
            (new TotalSalesMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            })->onlyOnDetail(),
            (new SalesByVendorPartitionMetrics()),
            (new SalesByPublication())

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
        return [];
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
            (new Actions\ResendOrderToMicroGlobal())->onlyOnDetail()
        ];
    }

    public static function label()
    {
        return 'Ventas';
    }

    public function subtitle()
    {
        return "Creado: {$this->created_at} - Actualizado: {$this->updated_at}";
    }

    public function title()
    {
        return "{$this->first_name} {$this->last_name}: #{$this->id}";
    }

    public static function availableForNavigation(Request $request)
    {
        if($request->user()->isVendor())
        {
            return false;
        }else{
            return true;
        }
    }
}
