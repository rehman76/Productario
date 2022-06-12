<?php

namespace App\Console\Commands;

use App\Connectors\TiendanubeConnector;
use App\Publication;
use Illuminate\Console\Command;

class DeleteTiendanubeProductsCommad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete_tiendanube';

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
        $publications = Publication::whereNotNull('tiendanube_id')->whereNull('winner_vendor_product_id')->get();

        foreach ($publications as $publication)
        {
            (new TiendanubeConnector())->deleteProduct($publication->tiendanube_id);
            $publication->tiendanube_id = null;
            $publication->save();
        }
    }
}
