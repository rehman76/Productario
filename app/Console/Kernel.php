<?php

namespace App\Console;

use App\Console\Commands\ImportVendorProductsCommand;
use App\Console\Commands\SyncProductsToWooCommerce;
use App\Console\Commands\FetchLatestDollarRate;
use App\Jobs\PullCategoriesFromMLJob;
use App\PublicationLog;
use App\SyncLog;
use App\VendorProductLog;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FetchLatestDollarRate::class,
        SyncProductsToWooCommerce::class,
        ImportVendorProductsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('command:fetch_latest_dollar_rate')->hourly();
//         $schedule->command('command:export_products_to_woo_commerce')->everyMinute();
         $schedule->command('command:import_vendor_products')->everyMinute();
         $schedule->command('telescope:prune')->daily();

        $schedule->call(function () {
            PublicationLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();
            VendorProductLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();
            SyncLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();
        })->monthly();


        $schedule->call(function () {
            // To fetch latest categories from ML daily
            PullCategoriesFromMLJob::dispatch();
        })->daily();



    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
