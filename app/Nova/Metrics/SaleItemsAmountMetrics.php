<?php

namespace App\Nova\Metrics;

use App\SaleItem;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class SaleItemsAmountMetrics extends Trend
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
            return $this->sumByDays($request, SaleItem::class,'vendor_product_cost');
        }else{
            return $this->sumByDays($request, SaleItem::where('vendor_id', $request->user()->vendor_id),'vendor_product_cost');
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
            7 => '7 Días',
            15 => '15 Días',
            30 => '30 Días',
            60 => '60 Días',
            90 => '90 Días',
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
        return 'amount';
    }

    public function name()
    {
        return 'Amount';
    }
}
