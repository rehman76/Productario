<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Filters\Filter;

class LoadPoroductsByConnectorFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value=='connected_mla')
        {
            return $query->whereNotNull('mla');
        }
        if ($value=='not_connected_mla')
        {
            return $query->whereNull('mla');
        }

        if ($value=='connected_tiendanube')
        {
            return $query->whereNotNull('tiendanube_id');
        }

        if ($value=='not_connected_tiendanube')
        {
            return $query->whereNull('tiendanube_id');
        }
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'ML Connected' => 'connected_mla',
            'Not ML Connected' => 'not_connected_mla',
            'Tiendanube Connected' => 'connected_tiendanube',
            'Not Tiendanube Connected' => 'not_connected_tiendanube'
        ];
    }

    public function name()
    {
        return 'Connector';
    }
}
