<?php

namespace App\Nova;


use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

use App\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Boolean;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;

use NovaButton\Button;
use Signifly\Nova\Fields\ProgressBar\ProgressBar;
use Sixlive\TextCopy\TextCopy;

class WooSyncActionLog extends Resource
{
    public static $group = 'Logs';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\WooSyncActionLog::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = '';

    /**
     * The columns that should be searched.
     *
     * @var array
     */

    public static $globallySearchable = false;

    public static $displayInNavigation = false;

    public static $search = [];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            DateTime::make('Started At', 'started_at'),
            Text::make('Status', 'status'),
            ProgressBar::make('Progress', 'progress_percentage') ->options([
                'fromColor' => '#FFEA82',
                'toColor' => '#40BF55',
                'animateColor' => true,
            ]),
            Text::make('Message', 'message'),
            DateTime::make('Ended At', 'ended_at'),
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
        return [

        ];
    }

    public static function label() {
        return 'Woo Sync Logs';
    }

    public static function singularlabel() {
        return 'Woo Sync Log';
    }

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
}
