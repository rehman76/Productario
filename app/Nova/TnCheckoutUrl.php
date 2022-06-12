<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Sixlive\TextCopy\TextCopy;

class TnCheckoutUrl extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $globallySearchable = false;

    public static $model = \App\TnCheckoutUrl::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
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
            Boolean::make('Active', 'is_active')->hideWhenCreating(),
            Text::make('Title', 'title')->rules('required', 'max:60'),
            TextCopy::make('URL', 'url')->showOnIndex()->showOnDetail()->hideWhenCreating()->hideWhenUpdating(),
            Number::make('Clicks', 'clicks')->showOnIndex()->showOnDetail()->hideWhenCreating()->hideWhenUpdating(),
            Number::make('Discount Percentage', 'discount_percentage')->min(0)->max(100)->step(0.01),
            Text::make('Contact Name', 'contact_name')->hideFromIndex()->rules('max:60'),
            Text::make('Contact Last Name', 'contact_last_name')->hideFromIndex()->rules('max:60'),
            Text::make('Contact Email', 'contact_email')->hideFromIndex()->rules('nullable','email', 'max:60'),
            KeyValue::make('Get Params', 'params')->rules('json')->actionText('Add New'),
            HasMany::make('Publication', 'tnCheckoutUrlPublications',
                \App\Nova\TnCheckoutUrlPublication::class)
                ->inline()
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
        return [];
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
        return [];
    }
}
