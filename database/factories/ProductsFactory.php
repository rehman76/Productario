<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Publication;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Publication::class, function (Faker $faker) {
    return [
      'name' => $faker->name(),
      'sku' => Str::random(8),
      'ean' => Str::random(8),
      'price' => $faker->randomNumber(3),
      'quantity' => $faker->randomNumber(2),
      'woo_id' => $faker->randomNumber(4),
      'mla' => $faker->randomNumber(8)
    ];
});
