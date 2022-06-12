<?php

use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            App\Vendor::firstOrCreate([
                'name' => 'ELIT',
            ], [
                'name' => 'ELIT',
                'currency' => 'USD',
                ]);
            App\Vendor::firstOrCreate([
                'name' => 'Grupo Nucleo',
            ],[
                'name' => 'Grupo Nucleo',
                'currency' => 'ARS',
            ]);
            App\Vendor::firstOrCreate([
                'name' => 'Stylus',
            ],[
                'name' => 'Stylus',
                'currency' => 'USD',
            ]);

            App\Vendor::firstOrCreate([
                'name' => 'Air Computers',
            ],[
                'name' => 'Air Computers',
                'currency' => 'USD',
            ]);

            App\Vendor::firstOrCreate([
                'name' => 'BatePrecios',
            ],[
                'name' => 'BatePrecios',
                'currency' => 'USD',
            ]);

            App\Vendor::firstOrCreate([
                'name' => 'ARG Seguridad',
            ],[
                'name' => 'ARG Seguridad',
                'currency' => 'USD',
            ]);

            App\Vendor::firstOrCreate([
                'name' => 'MasNet',
            ],[
                'name' => 'MasNet',
                'currency' => 'USD',
            ]);

            App\Vendor::firstOrCreate([
                'name' => 'Ceven ',
            ],[
                'name' => 'Ceven',
                'currency' => 'USD',
            ]);
            App\Vendor::firstOrCreate([
                'name' => 'Stenfar ',
            ],[
                'name' => 'Stenfar',
                'currency' => 'ARS',
            ]);
            App\Vendor::firstOrCreate([
                'name' => 'Micro Global',
            ],[
                'name' => 'Micro Global',
                'currency' => 'ARS',
            ]);


    }
}
