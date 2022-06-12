<?php


namespace App\Providers;


use Illuminate\Support\Facades\Route;
use PalauaAndSons\TagsField\Http\Middleware\Authorize;

class TagsFieldServiceProvider extends \PalauaAndSons\TagsField\TagsFieldServiceProvider
{
    /**
     * Register the field's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/palauaandsons/nova-tags-field')
            ->group(base_path().'/vendor/palauaandsons/nova-tags-field/routes/api.php');
    }
}
