<?php

use Faker\Generator as Faker;

$factory->define(App\Spending::class, function (Faker $faker) {
    return [
        'concept'     => $faker->text,
        'amount'      => $faker->numberBetween(1000, 20000),
        'category_id' => 1,
        'telegram_id' => $faker->randomNumber(),
    ];
});
