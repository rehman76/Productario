<?php

namespace App\Nova\Metrics;

use App\Vendor;
use App\SaleItem;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class SalesByVendorPartitionMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $vendorGroupings = DB::table('sale_items')->get()->groupBy('vendor_id');
        $salesByVendor = [];

        foreach ($vendorGroupings as $vendorId=>$salesItem)
        {
            $vendor = Vendor::find($vendorId);

            if(isset($vendor))
            {
                $salesByVendor[$vendor->name] = $salesItem->count();
            }

        }

        return $this->result($salesByVendor);
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
        return 'sales-by-vendor-partition-metrics';
    }

    public function name()
    {
        return "Sales By Vendor";
    }
}
