<?php

namespace App\Policies;

class VendorPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'vendors';
}
