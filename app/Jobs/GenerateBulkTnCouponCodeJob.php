<?php

namespace App\Jobs;

use App\ConnectorCouponRequest;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateBulkTnCouponCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $connectorCouponRequest;

    protected $connector;

    protected $fields;

    public $tries = 0;

    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fields, $connector)
    {
        $this->connector = $connector;
        $this->fields = $fields;
        $this->onQueue('connectors_bulk_sync');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($timestamp = Cache::get('tn-remaining-time-retry')) {
            return $this->release(
                $timestamp - time()
            );
        }

        $relativeFilePath  = 'csvs/'.time().'_coupon_codes.csv';
        $absolutePath = Storage::path($relativeFilePath);
        Storage::put($relativeFilePath, '');

        $connectorCouponRequestData['connector_id'] = $this->connector->id;
        $connectorCouponRequestData['prefix'] = $this->fields->prefix;
        $connectorCouponRequestData['number_of_coupons'] = $this->fields->no_of_coupon_codes;
        $connectorCouponRequestData['file_path'] = $relativeFilePath;
        $connectorCouponRequestData['discount_percentage'] = $this->fields->discount_percentage;
        $connectorCouponRequestData['max_usage'] = $this->fields->max_usage;

        $this->connectorCouponRequest = ConnectorCouponRequest::create($connectorCouponRequestData);

        $header = ['Coupon Codes'];


        $f = fopen($absolutePath, 'w');

        if ($f === false)
        {
            die('Error opening the file ' . $absolutePath);
        }

        fputcsv($f, $header);

        fclose($f);

            if (isset($this->connectorCouponRequest))
            {
                for ($i = 0; $i < $this->connectorCouponRequest['number_of_coupons']; $i++) {
                    $randomCouponCode = $this->fields->prefix.'-'.rand();
                    PushDataToCsvFileJob::dispatch($this->connectorCouponRequest, $randomCouponCode, $absolutePath);
                }

            }


    }

}
