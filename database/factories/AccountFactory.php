<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Account;
use App\User;
use Faker\Generator as Faker;

$factory->define(Account::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'account_type' => 'Savings',
        'balance' => 50000
    ];
});
