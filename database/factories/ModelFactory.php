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

$locations = ["Kathmandu, Bhaktapur, Lalitpur"];

$factory->define(Employer::class, function (Faker $faker) {
    return [
        'name'     => "ABC Company",
        'address'  => "Thamel, Kathmandu",
        'email'    => 'employer@test.com',
        'phone'    =>  "+977-01692284",
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
        'location' => "Kathmandu",
        "qualification" => "Bachelors in IT",
        "experience" => '2-3 years',
        "salary" => '70000',
        'expiry_date' => '2021-05-05'
    ];
});


$factory->define(Jobseeker::class, function (Faker $faker) {
    return [
        'first_name'     => $faker->firstName,
        'last_name'     => $faker->lastName,
        'email'    => 'jobseeker@test.com',
        'password' => Hash::make('12345'),
        "gender" => "male",
        'phone' => '977-9860536208',
        'address'  => $faker->address,
    ];
});
