<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use DigitalCreative\RangeInputFilter\RangeInputFilter;

class ProductQuantity extends RangeInputFilter
{

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
        if ($value['from'] || $value['to'])
        {
            return $query->whereBetween('quantity', [$value['from'], $value['to']]);
        }
        return null;
    }

    public function options(Request $request): array
    {
        return [
            'dividerLabel' => 'hasta',
        ];
    }
}
