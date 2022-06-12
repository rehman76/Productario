<?php

namespace App\Policies;

class BundlePublicationPolicy extends CustomPermissionPolicy
{
    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'bundles';
}
