<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title'        => $faker->sentence(),
        'content'      => $faker->paragraph(),
        'author_id'    => factory(App\User::class)->create()->id,
        'is_published' => $faker->boolean(),
        'published_on' => $faker->dateTime(),
    ];
});
