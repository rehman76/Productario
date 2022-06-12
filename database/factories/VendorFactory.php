<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Vendor;
use Faker\Generator as Faker;

$factory->define(Vendor::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
        'status' => true,
        'dollar_rate' => $faker->randomFloat($nbMaxDecimals = 2, $min = 60, $max = 70),
        'currency' => 'USD'
    ];
});
