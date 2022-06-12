<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\VendorProduct;
use Illuminate\Support\Str;

$factory->define(VendorProduct::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
        'vendor_id' => 1,
        'sku' => Str::random(8),
        'status' => 1,
        'ean' => Str::random(8),
        'price' => $faker->randomNumber(3),
        'calculated_retail_price' => $faker->randomNumber(3),
        'quantity' => $faker->randomNumber(3),
        'min_quantity' => $faker->randomNumber(2),
        'discount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 20),
        'iva' => $faker->randomElement($array = array (10.5,21)),
        'currency' => 'USD',
      ];
});