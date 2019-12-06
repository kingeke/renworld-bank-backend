<?php

namespace Tests\Feature;

use App\Account;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test if users can view transactions
     * 
     * @return void
     */
    public function testUsersCanViewTransactions()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        factory(Transaction::class, 2)->create([
            'account_id' => $account->id
        ]);

        factory(Transaction::class, 2)->create([
            'account_id' => $account->id,
            'type' => 'Debit'
        ]);

        $this->actingAs($user, 'users')->get('/api/transactions')->assertJson([
            'status' => 'success',
            'transactions' => $account->transactions()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(20)->toArray()
        ])->assertStatus(200);
    }

    /**
     * Test if users can view a transaction
     * 
     * @return void
     */
    public function testUsersCanViewTransaction()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        $transaction = factory(Transaction::class)->create([
            'account_id' => $account->id
        ]);

        $this->actingAs($user, 'users')->get("/api/transaction/$transaction->transaction_ref")->assertJson([
            'status' => 'success',
            'transaction' => $transaction->toArray()
        ])->assertStatus(200);
    }

    /**
     * Test if users cannot view other people's transactions
     * 
     * @return void
     */
    public function testUsersCanNotViewOtherPeoplesAccounts()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create();

        factory(Transaction::class)->create(['account_id' => $account->id]);

        $response = $this->actingAs($user, 'users')->get("/api/transactions?account_number=$account->account_number")->assertJson([
            'status' => 'success',
            'transactions' => []
        ])->assertStatus(200);

        $this->assertCount(0, $response->decodeResponseJson()['transactions']['data']);
    }

    /**
     * Test if users cannot view other people's transaction
     * 
     * @return void
     */
    public function testUsersCanNotViewOtherPeoplesTransaction()
    {
        $user = factory(User::class)->create();

        $transaction = factory(Transaction::class)->create();

        $this->actingAs($user, 'users')->get("/api/transaction/$transaction->transaction_ref")->assertJson([
            'status' => 'error',
            'message' => 'Transaction not found'
        ])->assertStatus(404);
    }
}
