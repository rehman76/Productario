<?php

namespace Tests\Feature;

use App\Publication;
use App\Services\ProductService;
use App\Vendor;
use App\VendorProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function create_new_publication()
    {
        factory(Publication::class)->create();

        $this->assertCount(1, Publication::all());
    }

    /** @test **/
    public function attach_vendor_products_to_publication()
    {
        $publication = $this->setDatabaseData();
        $this->assertCount(3, $publication->vendorproducts()->get());
    }

    /** @test **/
    public function get_valid_vendor_products_that_are_connected_with_publications()
    {
        $publication = $this->setDatabaseData();

         Vendor::where('name', 'ELIT')->update([
             'status' => 1
         ]);

        $this->assertCount(1, $publication->enabledVendorProducts()->get());
    }

    /** @test **/
    public function evaluate_vendor_products_for_winner_if_any_vendor_product_have_stock_and_less_price()
    {
        $publication = $this->setDatabaseData();

        Vendor::where('name', 'Air Computers')->update([
            'status' => 1
        ]);
        Vendor::where('name', 'ELIT')->update([
            'status' => 1
        ]);
        Vendor::where('name', 'Stylus')->update([
            'status' => 1
        ]);

        $vendorProduct1 = factory(VendorProduct::class)->create(['vendor_id' => Vendor::where('name', 'ELIT')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 80 ]);

        $publication->vendorproducts()->attach($vendorProduct1->id);

        ProductService::winnerVendorProductEvaluation($publication);

        $this->assertNotNull($publication->winner_vendor_product_id);
        $this->assertTrue($publication->status ? true: false);

    }

    public function setDatabaseData()
    {
        $this->seed();

        $vendorProduct1 = factory(VendorProduct::class)->create(['vendor_id' => Vendor::where('name', 'ELIT')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 80 ]);
        $vendorProduct2 = factory(VendorProduct::class)->create(['vendor_id' =>  Vendor::where('name', 'Stylus')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 70]);
        $vendorProduct3 = factory(VendorProduct::class)->create(['vendor_id' =>  Vendor::where('name', 'Air Computers')->first()->id,
            'quantity' => 0,'calculated_retail_price' => 100
        ]);

        $publication = factory(Publication::class)->create();
        $publication->status= 0;
        $publication->winner_vendor_product_id= $vendorProduct2->id;
        $publication->save();

        $publication->vendorproducts()->attach($vendorProduct1->id);
        $publication->vendorproducts()->attach($vendorProduct2->id);
        $publication->vendorproducts()->attach($vendorProduct3->id);

        return $vendorProduct1->publications()->first();
    }









}
