<?php

namespace App\Providers;
use App\BundlePublication;
use App\Observers\BundlePublicationObserver;
use App\Observers\PublicationObserver;
use App\Observers\VendorProductObserver;
use App\VendorProduct;
use App\Publication;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Publication::observe(PublicationObserver::class);
        BundlePublication::observe(BundlePublicationObserver::class);
        VendorProduct::observe(VendorProductObserver::class);
        JsonResource::withoutWrapping();
    }
}
