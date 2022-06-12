<?php

namespace App\Nova\Metrics;

use App\Publication;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class ValueTotalPublicationsCreatedByCurator extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        if (Auth::user()->isCurator())
        {
            return $this->count($request, Publication::where('created_by', Auth::user()->id))->format('0,0');
        }

        if (Auth::user()->isSuperAdmin())
        {
            return $this->count($request, Publication::where('created_by', $request->segment(3)))->format('0,0');

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
            365 => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
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
        return 'value-total-publications-created-by-curator';
    }

    public function name()
    {
        return 'Publication Created Count';
    }
}
