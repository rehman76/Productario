<?php

namespace App\Policies;

class ConnectorPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'connectors';
}
