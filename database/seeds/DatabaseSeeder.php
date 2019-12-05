<?php

use App\Account;
use App\Transaction;
use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->create([
            'name' => 'Dummy User',
            'email' => 'user@email.com'
        ]);

        $account = factory(Account::class)->create(['user_id' => $user->id]);

        factory(Transaction::class, 10)->create(['account_id' => $account->id]);

        factory(Account::class, 5)->create(['user_id' => $user->id]);
    }
}
