<?php

use Illuminate\Database\Seeder;

class ConnectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Connector::firstOrCreate([
            'connector' => 'WooCommerce',
        ], [
            'connector' => 'WooCommerce'
        ]);
    }
}
