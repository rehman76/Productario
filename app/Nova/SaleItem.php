<?php

namespace App\Nova;

use App\Services\Constants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaButton\Button;


class SaleItem extends Resource
{
    public static $displayInNavigation = false;
    public static $globallySearchable = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\SaleItem::class;

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
        return false;
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
        'id',  'mla_id'
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
            ID::make('ID', 'id')->sortable(),
            Text::make('Mla', 'mla_id'),
            Text::make('Name', 'title'),
            Number::make('Qty', 'qty'),
            Number::make('ML commission', 'sale_fee'),
            Number::make('Unit Price', 'unit_price'),
            Number::make('Full Unit Price', 'full_unit_price'),
            Button::make('Publication')->link(config('app.url').'/dash/resources/publications/' . $this->publication_id)->canSee(function () {
                return isset($this->publication_id);
            }),
            Button::make('Vendor Product')->link(config('app.url').'/dash/resources/vendor-products/' . $this->vendor_product_id)->canSee(function () {
                return isset($this->vendor_product_id);
            }),
            Button::make('Vendor')->link(config('app.url').'/dash/resources/vendors/' . $this->vendor_id)->canSee(function () {
                return isset($this->vendor_id);
            }),
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
        return [];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if($request->user()->isVendor())
            return $query->where('vendor_id', $request->user()->vendor_id);

    }
}
