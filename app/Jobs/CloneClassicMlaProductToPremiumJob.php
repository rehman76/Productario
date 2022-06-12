<?php

namespace App\Jobs;

use App\Services\ClonePremiumProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloneClassicMlaProductToPremiumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $publication, $clonePremiumProductService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($publication)
    {
        $this->publication = $publication;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->publication->premiumProduct()->exists())
        {
            $this->clonePremiumProductService = new ClonePremiumProductService();
            $response = $this->clonePremiumProductService->clonePremiumProduct($this->publication);
        }
//        if($response['error']) {
//            $this->publication->errorLogs()->create([
//                'body' => $response['message']
//            ]);
//        }


    }

}
