<?php

namespace App\Providers;

use Anaseqal\NovaImport\NovaImport;
use App\BundlePublication;
use App\Category;
use App\Http\View\Composers\NovaNavigationComposer;
use App\Nova\Metrics\ConnectedProductsMetrics;
use App\Nova\Metrics\NotConnectedVendorProductCountMetric;
use App\Nova\Metrics\ProductStockMetrics;
use App\Nova\Metrics\ProductWinnerVendorMetrics;
use App\Nova\Metrics\TotalSalesMetrics;
use App\Nova\Metrics\TrendTotalPublicationsConnectedByCurator;
use App\Nova\Metrics\TrendTotalPublicationsCreatedByCurator;
use App\Nova\Metrics\ValueTotalPublicationsConnectedByCurator;
use App\Nova\Metrics\ValueTotalPublicationsCreatedByCurator;
use App\Nova\Metrics\VendorProductCountMetrics;
use App\Observers\CategoryObserver;
use App\Observers\NovaBundlePublicationObserver;
use App\Observers\NovaPublicationObserver;
use App\Observers\TnCheckoutUrlObserver;
use App\Observers\TnCheckoutUrlPublicationObserver;
use App\Publication;
use App\TnCheckoutUrl;
use App\TnCheckoutUrlPublication;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Laravel\Nova\Element;
use Laravel\Nova\Exceptions\NovaExceptionHandler;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Panel;
use Silvanite\NovaFieldCheckboxes\Checkboxes;
use App\Exceptions\Handler;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Register Nova's custom exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        $this->app->bind(NovaExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        View::composer('nova::resources.navigation', NovaNavigationComposer::class);

        Nova::sortResourcesBy(function ($resource) {
            return $resource::$priority ?? 9999;
        });

        Nova::serving(function () {
            Category::observe(CategoryObserver::class);
            Publication::observe(NovaPublicationObserver::class);
            BundlePublication::observe(NovaBundlePublicationObserver::class);
            TnCheckoutUrlPublication::observe(TnCheckoutUrlPublicationObserver::class);
            TnCheckoutUrl::observe(TnCheckoutUrlObserver::class);
        });

        Nova::report(function ($exception) {
            // Send error report to 3rd party
        });


        Nova::createUserUsing(function ($command) {
            return [
                $command->ask('Name'),
                $command->ask('Email Address'),
                $command->secret('Password'),
            ];
        }, function ($name, $email, $password) {
            (new User)->forceFill([
                'first_name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now()
            ])->save();
        });

        \OptimistDigital\NovaSettings\NovaSettings::addSettingsFields([
            Number::make('IIBB', 'IIBB')->min(0)->max(100)->step(0.01),
            Select::make('IVA', 'IVA')->options([
                '21' => '21%',
                '10.5' => '10.5%',
            ]),
            Number::make('Mark up', 'markup')->min(0)->max(100),
            Number::make('Import Frequency', 'import_frequency'),
            Number::make('Dollar Rate (Pesos)', 'dollar_rate')->min(1)->max(100)->step(0.01)->help('This rate automatically update after one hour. From this <a target="_blank" href="https://bluelytics.com.ar/#!/">site </a>')->readonly(),

            Panel::make('Woo commerce', [
                Number::make('Frequency', 'woo_commerce_frequency')->min(30)
                    ->help('Mention Sync frequency in minutes'),
                Text::make('Last Sync at', 'woo_commerce_last_sync')->readonly(),
                Checkboxes::make('Choose fields to update', 'woo_update_fields')->options([
                    'images' => 'Images',
                    'name' => 'Title',
                    'description' => 'Description',
                    'quantity' => 'Stock',
                    'price' => 'Price',
                    'sale_price' => 'Sale Price',
                ])->withoutTypeCasting(),
            ]),

            Panel::make('Mercadolibre', [
                Number::make('Price Modifier (%)', 'mla_price_modifier')->max(100)->min(-100),
                Number::make('Stock Modifier', 'mla_stock_modifier'),
                Checkboxes::make('Choose fields to update', 'mercadolibre_sync_update_fields')->options([
                    'quantity' => 'Stock',
                    'price' => 'Price',
                    'sku' => 'Sku',
                ])->withoutTypeCasting(),
            ]),
            Panel::make('Tiendanube', [
                Number::make('Price Modifier (%)', 'tiendanube_price_modifier')->max(100)->min(-100),
                Number::make('Stock Modifier', 'tiendanube_stock_modifier'),
                Checkboxes::make('Choose fields to update', 'tiendanube_sync_update_fields')->options([
                    'quantity' => 'Stock',
                    'price' => 'Price',
                    'sku' => 'Sku',
                    'categories' => 'Categories',
                    'description' => 'Description',
                ])->withoutTypeCasting(),
            ]),
            Panel::make('Markup Setting',[
                Number::make('Markup For Premium Product', 'markup_percentage_premium_product'),

            ]),
        ], [
            'woo_update_fields' => 'array',
            'mercadolibre_sync_update_fields' => 'array',
            'tiendanube_sync_update_fields' => 'array',
            'markup_percentage_premium_product' => 'int'
        ]);

    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                'ariel.rajmaliuk@6cmarketing.com',
                'ffrischman@hotmail.com',
                'ary.frischman@bateprecios.com',
                'estefani@bateprecios.com',
                'daniel@bateprecios.com',
                'theodoreyaosin@gmail.com'
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            (new ProductWinnerVendorMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ProductStockMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new ConnectedProductsMetrics())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
            (new TrendTotalPublicationsCreatedByCurator)->canSee(function ($request) {
                return $request->user()->isCurator();
            }),
            (new TotalSalesMetrics)->canSee(function ($request) {
                return $request->user()->isVendor();
            }),
            (new VendorProductCountMetrics)->canSee(function ($request) {
                return $request->user()->isVendor();
            }),
            (new TrendTotalPublicationsConnectedByCurator())->canSee(function ($request) {
                return $request->user()->isCurator();
            }),
            (new ValueTotalPublicationsCreatedByCurator())->canSee(function ($request) {
                return $request->user()->isCurator();
            }),
            (new ValueTotalPublicationsConnectedByCurator())->canSee(function ($request) {
                return $request->user()->isCurator();
            }),
            (new NotConnectedVendorProductCountMetric())->canSee(function ($request) {
                return $request->user()->isCurator();
            }),

        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [

        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        //Here we are checking that If user has has some vendor then don't show settings like stuff else show everything
        if(!Auth::user()->hasAnyPermission(['view vendor products','View publication'])) {
            return [
                (new \OptimistDigital\NovaSettings\NovaSettings)->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (new \Beyondcode\TinkerTool\Tinker())->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (new \Spatie\BackupTool\BackupTool())->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (new NovaImport)->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (\Mirovit\NovaNotifications\NovaNotifications::make())->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (new \Eminiarts\NovaPermissions\NovaPermissions())->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
                (new  \Den1n\NovaQueues\Tool)->canSee(function ($request) {
                    return $request->user()->isSuperAdmin()  || $request->user()->isDeveloper();
                }),
            ];
        }else{
            return [

            ];
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
