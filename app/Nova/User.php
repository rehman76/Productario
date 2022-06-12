<?php

namespace App\Nova;

use App\Nova\Metrics\TrendTotalPublicationsConnectedByCurator;
use App\Nova\Metrics\TrendTotalPublicationsCreatedByCurator;
use App\Nova\Metrics\ValueTotalPublicationsConnectedByCurator;
use App\Nova\Metrics\ValueTotalPublicationsCreatedByCurator;
use Illuminate\Http\Request;
use Jeffbeltran\SanctumTokens\SanctumTokens;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\MorphToMany;
use Saumini\Count\RelationshipCount;
use Laravel\Nova\Fields\BelongsToMany;

class User extends Resource
{
    public static $group = 'Configuracion';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'first_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $globallySearchable = false;

    public static $search = [
        'id', 'first_name', 'email',
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

            SanctumTokens::make()->hideAbilities(),
            Gravatar::make()->maxWidth(50),

            Text::make('Nombre', 'first_name') //esto lo tuve que pasar a "name" porque si no, no corria, pero debia ser first_name
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Apellido', 'last_name')
                ->sortable(),
            DateTime::make('Last Login At','last_login_at')->sortable(),

            Text::make('Correo', 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Contraseña', 'Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),

//            RadioButton::make('User Type')
//                ->options([
//                    0 => 'Admin User',
//                    1 => 'Vendor',
//                ])
//                ->default(0) // optionals
//                ->marginBetween() // optional
//                ->skipTransformation(), // optional

            BelongsTo::make("Vendor",
                'vendor', Vendor::class)->nullable(),

            RelationshipCount::make('Publication Count', 'publications'),
            HasMany::make('Publications', 'publications'),
            HasMany::make('Vendor Products Attached/Detached', 'connectedProducts', PublicationVendorProductLinkedLog::class),

            MorphToMany::make('Roles', 'roles', \Eminiarts\NovaPermissions\Nova\Role::class),
            MorphToMany::make('Permissions', 'permissions', \Eminiarts\NovaPermissions\Nova\Permission::class),
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
            (new TrendTotalPublicationsCreatedByCurator())->onlyOnDetail(),
            (new TrendTotalPublicationsConnectedByCurator())->onlyOnDetail(),
            (new ValueTotalPublicationsCreatedByCurator())->onlyOnDetail(),
            (new ValueTotalPublicationsConnectedByCurator())->onlyOnDetail(),
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
        return [];
    }

    public static function label() {
        return 'Usuarios';
    }
}
