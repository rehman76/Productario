<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

use DigitalCreative\RangeInputFilter\RangeInputFilter;

class ProductPriceVariation extends RangeInputFilter {

    public function apply(Request $request, $query, $value)
    {
        if ($value['from'] || $value['to'])
        {
            return $query->whereBetween('price_variation', [$value['from'], $value['to']]);
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