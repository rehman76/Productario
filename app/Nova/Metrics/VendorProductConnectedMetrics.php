<?php

namespace App\Nova\Metrics;

use App\VendorProduct;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class VendorProductConnectedMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {

        $connectedProducts = DB::table('publication_vendor_product')->get()->groupBy('vendor_product_id')->count();
        $nonConnectedProducts = VendorProduct::count()-$connectedProducts;

        return $this->result([
            'Connected' => $connectedProducts,
            'Not Connected' => $nonConnectedProducts,
        ])->colors([
            'Connected' => '#27AE60',
            'Not Connected' => '#99A3A4'
        ]);
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
        return 'vendor-product-connected-metrics';
    }

    public function name()
    {
        return 'Connected vs No Connected';
    }
}
