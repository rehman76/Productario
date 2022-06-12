<?php

namespace App\Policies;

class SaleLogPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'sale logs';
}
