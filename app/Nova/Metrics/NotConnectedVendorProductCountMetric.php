<?php

namespace App\Nova\Metrics;

use App\VendorProduct;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NotConnectedVendorProductCountMetric extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */

    public function calculate(NovaRequest $request)
    {
        if($request->user()->isCurator() || $request->user()->isSuperAdmin())
        {
            return $this->result(VendorProduct::doesntHave('publications')->count());
        } else
            {
               return $this->result(VendorProduct::doesntHave('publications')->where('vendor_id', $request->user()->vendor_id)->count());
            }

    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [

        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'not-connected-vendor-product-count-metric';
    }

    public function name()
    {
        return 'Not Connected Vendor Products';
    }
}
