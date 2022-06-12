<?php

use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = factory(App\Publication::class, 10)
           ->create()
           ->each(function ($product) {
                $product->productlogs()->createMany(factory(App\PublicationLog::class, 5)->make()->toArray());
            });
    }
}
