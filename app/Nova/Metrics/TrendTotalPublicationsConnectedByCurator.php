<?php

namespace App\Nova\Metrics;

use App\PublicationVendorProductConnectionLog;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\Value;

class TrendTotalPublicationsConnectedByCurator extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        if(Auth::user()->isCurator()) {
            return $this->countByDays($request, PublicationVendorProductConnectionLog::where('user_id', Auth::user()->id));
        } else {
            return $this->countByDays($request, PublicationVendorProductConnectionLog::where('user_id', $request->segment(3)));
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
        return 'total-publication-connected-counts';
    }

    public function name()
    {
        return 'Trend Vendor Products Attached/Detached';
    }

}
