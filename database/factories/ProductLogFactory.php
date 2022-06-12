<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PublicationLog;
use Faker\Generator as Faker;

$factory->define(PublicationLog::class, function (Faker $faker) {
    return [
        'price' => $faker->randomNumber(3),
        'sale_price' => $faker->randomNumber(2),
        'quantity' => $faker->randomNumber(2),
        'updated_at' => $faker->dateTimeBetween('-1 years', 'now'),
        'created_at' => $faker->dateTimeBetween('-1 years', 'now')        
    ];
});
