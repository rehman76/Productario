<?php

namespace App\Nova\Metrics;

use App\Publication;
use App\Vendor;
use App\VendorProduct;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ProductWinnerVendorMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
          $winnerVendorsStats = [];
          $productWinnersId = Publication::whereNotNull('winner_vendor_product_id')->pluck('winner_vendor_product_id')->toArray();
          if($request->user()->vendor) {
              $winnerVendors = VendorProduct::whereIn('id', $productWinnersId)->where('vendor_id', $request->user()->vendor['id'])->get()->groupBy('vendor_id');
          }else{
              $winnerVendors = VendorProduct::whereIn('id', $productWinnersId)->get()->groupBy('vendor_id');
          }
            foreach ($winnerVendors as $vendorId => $vendorProducts )
            {
                $vendor = Vendor::find($vendorId);
                $winnerVendorsStats[$vendor->name] = $vendorProducts->count();
            }

       return $this->result($winnerVendorsStats);
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
        return 'product-winner-vendor-metrics';
    }

    public function name()
    {
        if(!Auth::user()->isVendor()) {
            return 'Products by Winners';
        }
            return 'Products Winners';
    }
}
