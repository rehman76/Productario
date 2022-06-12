<?php

use App\Vendor;
use App\VendorProduct;
use Illuminate\Database\Seeder;

class VendorProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(VendorProduct::class, 3)->create(['vendor_id' => Vendor::where('name', 'ELIT')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 80 ]);
        factory(VendorProduct::class, 2)->create(['vendor_id' =>  Vendor::where('name', 'Stylus')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 70]);
         factory(VendorProduct::class, 2)->create(['vendor_id' =>  Vendor::where('name', 'Air Computers')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 100
        ]);
    }
}
