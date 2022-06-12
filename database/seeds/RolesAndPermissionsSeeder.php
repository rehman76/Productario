<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $collection = collect([
            'vendors',
            'products',
            'publications',
            'bundles',
            'categories',
            'users',
            'connectors',
            'sales',
            'users',
            'vendor product logs',
            'sync logs',
            'sale logs',
            'publication logs',
            'error logs',
            'tn checkout urls'
        ]);

        $collection->each(function ($item, $key) {
            // create permissions for each collection item
            Permission::updateOrCreate(['group' => $item, 'name' => 'view ' . $item]);
            Permission::updateOrCreate(['group' => $item, 'name' => 'manage ' . $item]);
            Permission::updateOrCreate(['group' => $item, 'name' => 'delete ' . $item]);
        });

        // Create a Super-Admin Role and assign all Permissions
        $role = Role::updateOrCreate(['name' => 'super-admin']);
        if($role) {
            $role->givePermissionTo(Permission::all());
        }

        $user = \App\User::where('email', 'admin@bateprecios.com')->first();
        if(!$user->hasRole('super-admin'))
            $user->assignRole('super-admin');

    }
}
