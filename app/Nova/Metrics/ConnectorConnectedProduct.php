<?php

namespace App\Nova\Metrics;

use App\Publication;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ConnectorConnectedProduct extends Partition
{

    public function __construct($connectorName, $column)
    {
        $this->connectorName = $connectorName;
        $this->column = $column;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->result([
            'Linked' => Publication::whereNotNull($this->column)->count(),
            'Not Linked' => Publication::whereNull($this->column)->count(),
        ])->colors([
            'Linked' => '#27AE60',
            'Not Linked' => '#99A3A4'
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
    public function name()
    {
        return  $this->connectorName. ' Stats';
    }
}
