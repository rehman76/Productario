<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\User::firstOrCreate([
            'email' => 'admin@bateprecios.com',
        ],[
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@bateprecios.com',
            'password' => Hash::make('prueba123')
        ]);
    }
}
