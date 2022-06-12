<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 10/03/2021
 * Time: 6:29 PM
 */

namespace App\Nova\Fields;


class DateTime extends \Laravel\Nova\Fields\DateTime
{
    /**
     * Create a new field.
     *
     * @param  string $name
     * @param  string|null $attribute
     * @param  mixed|null $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->sortable()->format('DD/MM HH:mm');
    }
}