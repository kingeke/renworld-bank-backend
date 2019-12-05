<?php

namespace Tests\Feature;

use App\Account;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test if users can get dashboard data
     *
     * @return void
     */
    public function testUsersCanGetDashboardData()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create(['user_id' => $user->id]);

        factory(Transaction::class, 4)->create(['account_id' => $account->id]);

        $this->actingAs($user, 'users')->get('/api/dashboard')->assertJsonStructure([
            'status',
            'transactions',
            'accounts',
            'balance',
            'recent_transactions'
        ])->assertStatus(200);
    }
}
