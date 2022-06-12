<?php

namespace App\Nova\Metrics;

use App\SaleItem;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class TotalSalesMetrics extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        if($request->user()->isSuperAdmin())
        {
            return $this->countByMonths($request, SaleItem::where('sale_id', $request->resourceId));
        }else{
            return $this->countByMonths($request, SaleItem::where('vendor_id', $request->user()->vendor_id));
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
            30 => __('30 Days'),
            60 => __('60 Days'),
            90 => __('90 Days'),
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
        return 'total-sales';
    }

    public function name()
    {
        return 'Total Sales';
    }
}
