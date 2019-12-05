<?php

use App\Account;
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

        factory(Account::class)->create(['user_id' => $user->id]);
    }
}
