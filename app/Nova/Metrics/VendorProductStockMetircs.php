<?php

namespace App\Nova\Metrics;

use App\VendorProduct;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class VendorProductStockMetircs extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $stock = VendorProduct::where('quantity', '>', 0)->count();
        $noStock = VendorProduct::where('quantity', 0)->count();

        return $this->result([
            'Stock' => $stock,
            'No Stock' => $noStock,
        ])->colors([
            'Stock' => '#F7DC6F',
            'No Stock' => '#CB4335'
        ]);;
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
    public function name()
    {
        return 'Stock vs No Stock';
    }
}
