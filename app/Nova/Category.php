<?php

namespace App\Nova;

use App\Nova\Actions\SyncCategoriesWithTiendanubeStoreAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use App\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Panel;
use NovaButton\Button;

class Category extends Resource
{
    public static $group = 'Publicaciones';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Category::class;

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
       'id', 'name',
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
            Text::make('Nombre', 'name'),
            Text::make('Woo Commerce Id', 'woo_category_id')->onlyOnDetail(),

            new Panel('Google Shopping Category', [
                Text::make('Id', 'google_shopping_category')->hideFromIndex(),
                Button::make('Categories List')->link('https://www.google.com/basepages/producttype/taxonomy-with-ids.es-ES.txt')->hideFromIndex(),
            ]),

            DateTime::make('Creado', 'created_at')->exceptOnForms(),
            DateTime::make('Actualizado', 'updated_at')->exceptOnForms(),

            BelongsToMany::make('Productos', 'publications', Publication::class)
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
            SyncCategoriesWithTiendanubeStoreAction::make()->standalone(),
        ];
    }

    public static function label() {
        return 'Categorias';
    }
}
