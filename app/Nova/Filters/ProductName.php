<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Reedware\NovaTextFilter\TextFilter;

class ProductName extends TextFilter
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
        return $query->where('name', 'like', "%{$value}%");
    }

}
