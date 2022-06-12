<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * DollarRate facade.
 *
 * @author Theodore Yaosin <theodoreyaosin@outlook.com>
 *
 * @method static float get()
 */
class DollarRate extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return \App\Services\DollarRateService::class;
    }
}
