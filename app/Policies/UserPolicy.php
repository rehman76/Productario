<?php

namespace App\Policies;

class UserPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'users';
}
