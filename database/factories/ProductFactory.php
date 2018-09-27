<?php

use Faker\Generator as Faker;

$factory->define(App\Model\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'price' => $faker->numberBetween(1, 1000),
        'quantity' => $faker->numberBetween(1, 10),
    ];
});
