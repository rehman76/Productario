<?php

namespace App\Policies;

class TnCheckoutUrlPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'tn checkout urls';
}
