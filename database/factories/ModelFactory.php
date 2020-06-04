<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Employer;
use App\Models\Job;
use App\Models\Jobseeker;
use Illuminate\Support\Facades\Hash;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Employer::class, function (Faker $faker) {
    return [
        'name'     => "ABC Company",
        'address'  => "Thamel, Kathmandu",
        'email'    => $faker->unique()->email,
        'phone'    =>  "+977-XXXXXXXXXX",
        'password' => Hash::make('12345'),
    ];
});

$factory->define(Job::class, function (Faker $faker) {
    return [
        'title'     => "Web Developer",
        'slug' => $faker->unique()->randomNumber(5),
        'category'    => "IT/Computing",
        'type' => 'Full Time',
        'level' => 'Senior Level',
        'description' => "lorem ipsum",
        "qualification" => "Bachelors in IT",
        "experience" => '2-3 years',
        "salary" => 'Rs. 70,000',
        'expiry_date' => "2020-12-12"
    ];
});


$factory->define(Jobseeker::class, function (Faker $faker) {
    return [
        'first_name'     => $faker->firstName,
        'last_name'     => $faker->lastName,
        'email'    => $faker->unique()->email,
        'password' => Hash::make('12345'),
        "gender" => "male",
        'phone' => '977-XXXXXXXXXX',
        'address'  => $faker->address,
    ];
});
