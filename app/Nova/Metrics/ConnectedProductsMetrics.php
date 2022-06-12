<?php

namespace App\Nova\Metrics;

use App\Publication;
use App\VendorProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ConnectedProductsMetrics extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $connectedProducts = DB::table('publication_vendor_product')->get()->groupBy('publication_id')->count();
        $NonConnectedProducts = Publication::count()-$connectedProducts;
        if($request->user()->vendor) {
            $activeProductIds = Publication::where('status','=',1)->pluck('id')->toArray();
            $vendorProductIds = DB::table('publication_vendor_product')->whereIn('publication_id', $activeProductIds)->pluck('vendor_product_id')->toArray();
            $activePublicationProducts = VendorProduct::whereIn('id', $vendorProductIds)->where('vendor_id', $request->user()->vendor['id'])->count();
            return $this->result([
                'Connected'=>$activePublicationProducts]);
        }else {
            return $this->result([
                'Connected' => $connectedProducts,
                'Not Connected' => $NonConnectedProducts,
            ])->colors([
                'Connected' => '#27AE60',
                'Not Connected' => '#99A3A4'
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
        return 'connected-non-connected';
    }

    public function name()
    {
        if(!Auth::user()->isVendor()) {
            return 'Connected vs No Connected';
        }
        return 'Linked';
    }
}
