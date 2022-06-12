<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(UserSeeder::class);
         $this->call(VendorSeeder::class);
         $this->call(RolesAndPermissionsSeeder::class);
         $this->call(PublicationMarketingImagesSeeder::class);
         $this->call(ConnectorSeeder::class);
    }
}
