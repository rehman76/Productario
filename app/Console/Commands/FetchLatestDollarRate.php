<?php

namespace App\Console\Commands;

use App\Notifications\GeneralNotification;
use App\User;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class FetchLatestDollarRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fetch_latest_dollar_rate';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {

            $response = Http::get('https://api.bluelytics.com.ar/v2/latest')->throw();

            if ($response['oficial']['value_sell'])
            {
                nova_set_setting_value('dollar_rate', (float)$response['oficial']['value_sell']);
            }
        } catch (RequestException $exception){
            Notification::send(User::role('super-admin')->get(), new GeneralNotification('Fetch Dollar Rate API Counter Error', '' ));
            Log::info($exception->getMessage());
        }

    }
}
