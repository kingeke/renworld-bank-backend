<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Account;
use App\Transaction;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'account_id' => function () {
            return factory(Account::class)->create()->id;
        },
        'type' => "Credit",
        'narration' => $faker->sentence,
        'previous_balance' => 0,
        'amount' => 50000,
        'current_balance' => 50000,
    ];
});
