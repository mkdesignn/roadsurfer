<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\App\Campervan::class, function (Faker $faker) {
    return [
        'make' => $faker->name,
        'model' => $faker->name,
        'color' => $faker->colorName,
        'year' => $faker->year
    ];
});
