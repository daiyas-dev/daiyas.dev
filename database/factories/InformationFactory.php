<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Information;
use Faker\Generator as Faker;

$factory->define(Information::class, function (Faker $faker) {
    return [
        'info_title' => '件名:' . $faker->word(),
        'info_memo' => 'お知らせ内容:' . $faker->realText(),
        'user_id' => $faker->numberBetween(1, 20),
        'start_date' => now(),
        'end_date' => now()
    ];
});
