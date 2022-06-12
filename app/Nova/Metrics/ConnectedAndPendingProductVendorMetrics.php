<?php

namespace App\Nova\Metrics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ConnectedAndPendingProductVendorMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $vendorId = $request->user()->isVendor() ? $request->user()->vendor_id : $request->resourceId;
        $connectedCount = DB::select('select COUNT(*) as count from publication_vendor_product where vendor_product_id in (select id from `vendor_products` where `vendor_id`= '.$vendorId.' and deleted_at is null)')[0]->count;
        $notConnectedCount = DB::select('select COUNT(*) as count from vendor_products where id not in (select vendor_product_id from  publication_vendor_product) and vendor_id='.$vendorId.' and deleted_at is null')[0]->count;


        return $this->result([
            'Connected' => $connectedCount,
            'Pending' => $notConnectedCount,
        ])->colors([
            'Connected' => '#27AE60',
            'Pending' => '#99A3A4'
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
        return 'product-stats-for-each-vendor-metrics';
    }

    public function name()
    {
        return 'Products Connected/Pending';
    }
}
