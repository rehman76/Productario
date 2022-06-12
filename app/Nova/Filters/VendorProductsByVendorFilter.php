<?php

namespace App\Nova\Filters;

use App\Vendor;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class VendorProductsByVendorFilter extends Filter
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
        return $query->where('vendor_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $vendors = Vendor::all();
        $vendorsArray = collect();

        foreach ($vendors as $vendor)
        {
            $vendorsArray->put($vendor->name, $vendor->id);
        }
        return $vendorsArray;
    }

    public function name()
    {
        return 'Vendor';
    }
}
