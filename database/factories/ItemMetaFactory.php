<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\App\ItemMeta::class, function (Faker $faker) {
    return [
        'model' => $faker->name,
        'color' => $faker->colorName,
        'year' => $faker->year
    ];
});
