<?php

namespace App\Jobs;

use App\Services\Constants;
use App\WooSyncActionLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportFromWooCommerce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $fields, $createdById;

    public function __construct($fields, $actionById)
    {
        $this->fields = $fields;
        $this->createdById = $actionById;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       /**** Send Notification to all users ***/
        app('App\Services\WooCommerceService')->wooNotification([
            'title' => 'Woo Commerce Import Started',
            'message' => 'The import process has been started'
        ]);

        /**** Log action ***/
       $wooActionLog =  WooSyncActionLog::create([
           'created_by' => $this->createdById,
            'started_at' =>  Carbon::now(),
            'status' => Constants::WooActionRunningStatus,
            'progress_percentage' => 0
       ]);
        app('App\Services\WooCommerceService')->dispatchImportJobsAsFilters($this->fields, $wooActionLog);
    }
}
