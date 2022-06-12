<?php

namespace App\Jobs;

use App\Services\Constants;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportWooCommerceProductsPerPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $filters, $totalPages, $wooActionLog;

    public function __construct($filters, $totalPages, $wooActionLog)
    {
        $this->filters = $filters;
        $this->totalPages = $totalPages;
        $this->wooActionLog = $wooActionLog;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('App\Services\WooCommerceService')->importProductsBasedOnFilters($this->filters);

        app('App\Services\WooCommerceService')->wooSyncLog($this->wooActionLog, [
            'progress_percentage' => $this->filters['page']/$this->totalPages
        ]);


        /*** Notification & Log on Process Completion ***/
        if ($this->totalPages== $this->filters['page'])
        {
            app('App\Services\WooCommerceService')->wooNotification([
                'title' => 'Woo Commerce Import Completed',
                'message' => 'The process completed execution. Publication images might take time to show'
            ]);

            app('App\Services\WooCommerceService')->wooSyncLog($this->wooActionLog, [
                'ended_at' => Carbon::now(),
                'progress_percentage' => 1,
                'message' => 'Successfully Completed',
                'status' => Constants::WooActionFinishStatus
            ]);
        }
    }
}
