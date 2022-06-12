<?php

namespace App\Nova\Metrics;

use App\Publication;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class AverageProductPrices extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays($request, Publication::class);
//        return $this->averageByMonths($request, Publication::class, 'word_count');
//        return $this->averageByWeeks($request, Publication::class, 'word_count');
//        return $this->averageByDays($request, Publication::class, 'word_count');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
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
        return 'average-product-prices';
    }
}
