<?php

namespace App\Nova;

use App\Nova\Fields\DateTime;
use App\Nova\Metrics\SaleItemsAmountMetrics;
use App\Nova\Metrics\TotalSalesMetrics;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;


class SaleItemResourceVendor extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\SaleItem::class;

    public static $group = 'Sales';

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
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

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
        'id',
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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Name', 'title'),
            Text::make('Quantity','qty'),
            Text::make('Price','vendor_product_cost'),
            DateTime::make('Date','created_at')
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
            (new TotalSalesMetrics()),
            (new SaleItemsAmountMetrics()),

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

            (new DownloadExcel)->only('id', 'title' ,'qty', 'vendor_product_cost', 'created_at')
            ->withHeadings('ID', 'Name' ,'Quantity', 'Vendor Product Cost', 'Created At'
            ),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if($request->user()->isVendor())
            return $query->where('vendor_id', $request->user()->vendor_id);

    }

    public static function label()
    {
        return 'Sale Items';
    }

    public static function availableForNavigation(Request $request)
    {
        if($request->user()->isSuperAdmin() ||
            $request->user()->isCurator() || $request->user()->isManager())
        {
            return false;
        }else{
            return true;
        }
    }
}
