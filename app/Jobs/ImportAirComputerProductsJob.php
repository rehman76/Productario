<?php

namespace App\Jobs;

use App\Services\AirComputerProductsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportAirComputerProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $groupId;

    protected $vendorInstanceId;

    protected $token;
    protected $isLast;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($groupId, $vendorInstanceId, $token, $isLast)
    {
        $this->groupId = $groupId;
        $this->vendorInstanceId = $vendorInstanceId;
        $this->token = $token;
        $this->isLast = $isLast;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AirComputerProductsService $airComputerProductsService)
    {
        $airComputerProductsService->saveGroupProducts($this->groupId, $this->vendorInstanceId, $this->token, $this->isLast);
    }
}
