<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

use DigitalCreative\RangeInputFilter\RangeInputFilter;

class PublicationWithDiscountFilter extends Filter {
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
        if($value == 1) {
            return $query->whereNotNull('discount');
        }
        else{
            return $query->whereNull('discount');
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
            'Discounted' => 1,
            'Not Discounted' => 0
        ];
    }

    public function name()
    {
        return 'Discount';
    }

}