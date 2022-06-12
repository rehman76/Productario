<?php

namespace App\Jobs;

use App\Connectors\TiendanubeConnector;
use App\Services\HelperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class PushDataToCsvFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $connectorCouponRequest;

    protected $randomCouponCode;

    protected $filePath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($connectorCouponRequest, $randomCouponCode, $filePath)
    {
        $this->connectorCouponRequest = $connectorCouponRequest;
        $this->randomCouponCode = $randomCouponCode;
        $this->filePath = $filePath;
        $this->onQueue('connectors_bulk_sync');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tiendanubeConnector = new TiendanubeConnector();
        $response = $this->tiendanubeConnector->generateTnCouponCode($this->randomCouponCode, $this->connectorCouponRequest);

        if($response->successful())
        {
            $this->pushDataToCsv();
        }
    }

    public function pushDataToCsv()
    {
            $f = fopen($this->filePath, 'a');

            if ($f === false)
            {
                die('Error opening the file ' . $this->filePath);
            }


            $data['code'] = $this->randomCouponCode;
            fputcsv($f, $data);


            fclose($f);


    }
}
