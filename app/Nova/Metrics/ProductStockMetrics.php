<?php

namespace App\Nova\Metrics;

use App\Publication;
use App\Vendor;
use App\VendorProduct;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ProductStockMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $stock = Publication::where('quantity', '>', 0)->count();
        $noStock = Publication::where('quantity', 0)->count();
        $winnerVendorsStats = [];

        if($request->user()->vendor) {
            $winnerVendors = VendorProduct::where('vendor_id', $request->user()->vendor['id'])->where('status',1)->get()->groupBy('vendor_id');
            if(isset($winnerVendorsStats)) {
                foreach ($winnerVendors as $vendorId => $vendorProducts) {
                    $vendor = Vendor::find($vendorId);
                    $winnerVendorsStats[$vendor->name] = $vendorProducts->count();
                }
                return $this->result($winnerVendorsStats);
            }else{
                return $this->result($winnerVendorsStats);
            }
        }else{
            return $this->result([
                'Stock' => $stock,
                'No Stock' => $noStock,
            ])->colors([
                'Stock' => '#F7DC6F',
                'No Stock' => '#CB4335'
            ]);
        }
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
        return 'stock-products';
    }

    public function name()
    {
        if(!Auth::user()->isVendor()) {
            return 'Stock vs No Stocks';
        }
        return 'Active Products';
    }
}
