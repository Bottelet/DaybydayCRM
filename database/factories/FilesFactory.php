<?php

/** @var Factory $factory */

use App\Models\File;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

<<<<<<< Updated upstream
$factory->define(File::class, static function (Faker $faker) {
=======
$factory->define(File::class, function (Faker $faker) {
>>>>>>> Stashed changes
    return [
        //
    ];
});
