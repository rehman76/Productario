<?php

namespace App\Console\Commands;

use App\Jobs\ImportVendorProductsJob;
use App\Services\VendorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportVendorProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import_vendor_products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $vendors = $this->vendorService->all();
        $importFrequencyGlobal =  nova_get_setting('import_frequency');
        foreach ($vendors as $vendor)
        {
            $importFrequency = $importFrequencyGlobal ?? $vendor->import_frequency;
            if ($importFrequency && (!$vendor->last_imported_at ||
                    Carbon::now()->greaterThanOrEqualTo(
                        Carbon::parse($vendor->last_imported_at)->addMinutes($importFrequency))))
            {
                dispatch(new ImportVendorProductsJob($vendor->name));
                $vendor->last_imported_at = Carbon::now();
                $vendor->save();
            }
        }
    }
}
