<?php

namespace App\Policies;

class ErrorLogPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'error logs';
}
