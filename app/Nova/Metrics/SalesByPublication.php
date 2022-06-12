<?php

namespace App\Nova\Metrics;

use App\Publication;
use App\SaleItem;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class SalesByPublication extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->sum($request, SaleItem::where('publication_id',$request->range),'unit_price');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        $publications = collect();

        $salesByPublicationsIds = DB::table('sale_items')->select('publication_id', DB::raw('SUM(unit_price) AS cost'))
            ->groupBy('publication_id')->orderBy('cost', 'DESC')->limit(10)->pluck('publication_id');

        foreach ($salesByPublicationsIds as $salesByPublicationId)
        {
            $publication = Publication::find($salesByPublicationId);
            if(isset($publication))
            {
                $publications->put($publication->id, $publication->name);
            }
        }

        return $publications;

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
        return 'sales-by-publication';
    }
}
