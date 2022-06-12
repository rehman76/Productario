<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Pktharindu\NovaPermissions\Traits\ValidatesPermissions;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
         'App\User' => 'App\Policies\UserPolicy',
         'App\Vendor' => 'App\Policies\VendorPolicy',
         'App\VendorProduct' => 'App\Policies\VendorProductPolicy',
         'App\Publication' => 'App\Policies\PublicationPolicy',
         'App\Category' => 'App\Policies\CategoryPolicy',
         'App\Connector' => 'App\Policies\ConnectorPolicy',
         'App\VendorProductLog' => 'App\Policies\VendorProductLogPolicy',
         'App\PublicationLog' => 'App\Policies\PublicationLogPolicy',
         'App\SyncLog' => 'App\Policies\SyncLogPolicy',
         'App\SaleLog' => 'App\Policies\SaleLogPolicy',
         'App\BundlePublication' => 'App\Policies\BundlePublicationPolicy',
         'App\Sale' => 'App\Policies\SalePolicy',
         'App\TnCheckoutUrl' => 'App\Policies\TnCheckoutUrlPolicy',
         'App\ErrorLog' => 'App\Policies\ErrorLogPolicy',
//         \Pktharindu\NovaPermissions\Role::class => \App\Policies\RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        foreach (config('nova-permissions.permissions') as $key => $permissions) {
            Gate::define($key, function (User $user) use ($key) {
                if ($this->nobodyHasAccess($key)) {
                    return true;
                }

                return $user->hasPermissionTo($key);
            });
        }

    }
}
