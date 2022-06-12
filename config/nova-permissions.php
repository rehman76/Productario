<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model class
    |--------------------------------------------------------------------------
    */

    'user_model' => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Nova User resource tool class
    |--------------------------------------------------------------------------
    */

    'user_resource' => 'App\Nova\User',

    /*
    |--------------------------------------------------------------------------
    | The group associated with the resource
    |--------------------------------------------------------------------------
    */

    'role_resource_group' => 'Other',

    /*
    |--------------------------------------------------------------------------
    | Database table names
    |--------------------------------------------------------------------------
    | When using the "HasRoles" trait from this package, we need to know which
    | table should be used to retrieve your roles. We have chosen a basic
    | default value but you may easily change it to any table you like.
    */

    'table_names' => [
        'roles' => 'roles',

        'role_permission' => 'role_permission',

        'role_user' => 'role_user',

        'users' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Permissions
    |--------------------------------------------------------------------------
    */

    'permissions' => [

        //Vendor Resource Permissions
        'view vendor' => [
            'display_name' => 'View vendor',
            'description'  => 'can view vendor',
            'group'        => 'Vendor',
        ],

        'create vendor' => [
            'display_name' => 'create vendor',
            'description'  => 'can create vendor',
            'group'        => 'Vendor',
        ],

        'edit vendor' => [
            'display_name' => 'Edit vendor',
            'description'  => 'can edit vendor',
            'group'        => 'Vendor',
        ],

        'delete vendor' => [
            'display_name' => 'Delete vendor',
            'description'  => 'can delete vendor',
            'group'        => 'Vendor',
        ],

        //Vendor Product Resource Permissions

        'view vendor products' => [
            'display_name' => 'View vendor products',
            'description'  => 'can view vendor products',
            'group'        => 'Vendor Product',
        ],

        'create vendor products' => [
            'display_name' => 'create vendor products',
            'description'  => 'can create vendor products',
            'group'        => 'Vendor Product',
        ],

        'edit vendor products' => [
            'display_name' => 'Edit vendor products',
            'description'  => 'can edit vendor products',
            'group'        => 'Vendor Product',
        ],

        'delete vendor products' => [
            'display_name' => 'Delete vendors',
            'description'  => 'can delete vendor products',
            'group'        => 'Vendor Product',
        ],


        //Publication Permissions

        'view publication' => [
            'display_name' => 'View publication',
            'description'  => 'can view publication',
            'group'        => 'Publication',
        ],

        'create publication' => [
            'display_name' => 'create publication',
            'description'  => 'can create publication',
            'group'        => 'Publication',
        ],

        'edit publication' => [
            'display_name' => 'Edit publication',
            'description'  => 'can edit publication',
            'group'        => 'Publication',
        ],

        'delete publication' => [
            'display_name' => 'Delete publication',
            'description'  => 'can delete publication',
            'group'        => 'Publication',
        ],

        //Bundles Permissions

        'view bundle' => [
            'display_name' => 'View bundle',
            'description'  => 'can view bundle',
            'group'        => 'Bundle',
        ],

        'create bundle' => [
            'display_name' => 'Create bundle',
            'description'  => 'can create bundle',
            'group'        => 'Bundle',
        ],

        'edit bundle' => [
            'display_name' => 'Edit bundle',
            'description'  => 'can edit bundle',
            'group'        => 'Bundle',
        ],

        'delete bundle' => [
            'display_name' => 'Delete publication',
            'description'  => 'can delete publication',
            'group'        => 'Bundle',
        ],

        //Categories Permissions

        'view categories' => [
            'display_name' => 'View categories',
            'description'  => 'can view categories',
            'group'        => 'Categories',
        ],

        'create categories' => [
            'display_name' => 'Create categories',
            'description'  => 'can create bundle',
            'group'        => 'Categories',
        ],

        'edit categories' => [
            'display_name' => 'Edit categories',
            'description'  => 'can edit categories',
            'group'        => 'Categories',
        ],

        'delete categories' => [
            'display_name' => 'Delete categories',
            'description'  => 'can delete categories',
            'group'        => 'Categories',
        ],

        //Users

        'view users' => [
            'display_name' => 'View users',
            'description'  => 'can view users',
            'group'        => 'Users',
        ],

        'create users' => [
            'display_name' => 'Create users',
            'description'  => 'can create users',
            'group'        => 'Users',
        ],

        'edit users' => [
            'display_name' => 'Edit users',
            'description'  => 'can edit users',
            'group'        => 'Users',
        ],

        'delete users' => [
            'display_name' => 'Delete users',
            'description'  => 'can delete users',
            'group'        => 'Users',
        ],

        //Connectors

        'view connectors' => [
            'display_name' => 'View connectors',
            'description'  => 'can view connectors',
            'group'        => 'Connectors',
        ],

        'create connectors' => [
            'display_name' => 'Create connectors',
            'description'  => 'can create connectors',
            'group'        => 'Connectors',
        ],

        'edit connectors' => [
            'display_name' => 'Edit Connectors',
            'description'  => 'can edit connectors',
            'group'        => 'Connectors',
        ],

        'delete connectors' => [
            'display_name' => 'Delete connectors',
            'description'  => 'can delete connectors',
            'group'        => 'Connectors',
        ],

        //Bundle Publications

        'view bundle publications' => [
            'display_name' => 'View publications',
            'description'  => 'can view publications',
            'group'        => 'Bundle Publications',
        ],

        'create bundle publications' => [
            'display_name' => 'Create bundle publications',
            'description'  => 'can create bundle publications',
            'group'        => 'Bundle Publications',
        ],

        'edit bundle publications' => [
            'display_name' => 'Edit bundle publications',
            'description'  => 'can edit bundle publications',
            'group'        => 'Bundle Publications',
        ],

        'delete bundle publications' => [
            'display_name' => 'Delete bundle publications',
            'description'  => 'can delete bundle publications',
            'group'        => 'Bundle Publications',
        ],

        //Roles

        'view roles' => [
            'display_name' => 'View roles',
            'description'  => 'can view roles',
            'group'        => 'Roles',
        ],

        'create roles' => [
            'display_name' => 'Create roles',
            'description'  => 'can create roles',
            'group'        => 'Roles',
        ],

        'edit roles' => [
            'display_name' => 'Edit roles',
            'description'  => 'can edit roles',
            'group'        => 'Roles',
        ],

        'delete roles' => [
            'display_name' => 'Delete roles',
            'description'  => 'can delete roles',
            'group'        => 'Roles',
        ],

        //Sales

        'view sales' => [
            'display_name' => 'View sales',
            'description'  => 'can view sales',
            'group'        => 'Sales',
        ],

        'create sales' => [
            'display_name' => 'Create sales',
            'description'  => 'can create sales',
            'group'        => 'Sales',
        ],

        'edit sales' => [
            'display_name' => 'Edit sales',
            'description'  => 'can edit sales',
            'group'        => 'Sales',
        ],

        'delete sales' => [
            'display_name' => 'Delete sales',
            'description'  => 'can delete sales',
            'group'        => 'Sales',
        ],


    ],
];
