<?php

namespace Tests\Feature;

use App\Account;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test if users can view accounts
     * 
     * @return void
     */
    public function testUsersCanViewAllAccounts()
    {
        $user = factory(User::class)->create();

        factory(Account::class, 2)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'users')->get('/api/accounts')->assertJson([
            'status' => 'success',
            'accounts' => $user->accounts()->latest()->withCount('transactions')->paginate(20)->toArray()
        ])->assertStatus(200);
    }

    /**
     * Test if users can view an account
     * 
     * @return void
     */
    public function testUsersCanViewAnAccount()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'users')->get("/api/account/$account->account_number")->assertJson([
            'status' => 'success',
            'account' => $account->toArray()
        ])->assertStatus(200);
    }

    /**
     * Test if users can not view another persons account
     * 
     * @return void
     */
    public function testUsersCanNotViewAnotherPersonsAccount()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create();

        $this->actingAs($user, 'users')->get("/api/account/$account->account_number")->assertJson([
            'status' => 'error',
            'message' => 'Account not found'
        ])->assertStatus(404);
    }

    /**
     * Test if users can successfully create an account
     *
     * @return void
     */
    public function testUsersCanCreateAnAccount()
    {
        $data = $this->data();

        $user = factory(User::class)->create();

        $this->actingAs($user, 'users')->post('/api/accounts/create', $data)->assertJson([
            'status' => 'success',
            'message' => 'Account created successfully.'
        ])->assertStatus(200);

        $this->assertDatabaseHas('accounts', ['account_type' => $data['account_type'], 'user_id' => $user->id]);
    }

    /**
     * Test if users can close/open an account
     * 
     * @return void
     */
    public function testUsersCanCloseOrOpenAnAccount()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user, 'users')->put("/api/account/$account->account_number")->assertJson([
            'status' => 'success',
            'message' => 'Account updated successfully.'
        ])->assertStatus(200);

        $account = $account->refresh();

        $this->assertFalse($account->active);

        $this->actingAs($user, 'users')->put("/api/account/$account->account_number")->assertJson([
            'status' => 'success',
            'message' => 'Account updated successfully.'
        ])->assertStatus(200);

        $account = $account->refresh();

        $this->assertTrue($account->active);
    }

    /**
     * Test if users can fund their account
     * 
     * @return void
     */
    public function testUsersCanFundTheirAccount()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        $prev_balance = $account->balance;

        $amount = 50000;

        $data = [
            'amount' => $amount
        ];

        $this->actingAs($user, 'users')->post("/api/account/$account->account_number", $data)->assertJson([
            'status' => 'success',
            'message' => 'Account funded successfully.'
        ])->assertStatus(200);

        $this->assertEquals($account->refresh()->balance, $prev_balance + $amount);
    }

    /**
     * Test if users cannot initiate a transfer without correct password
     * 
     * @return void
     */
    public function testUsersCanNotInitiateATransferWithAWrongPassword()
    {
        $user = factory(User::class)->create();

        $data = $this->transfer_data($user);

        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['password' => 's']))->assertJson([
            'status' => 'error',
            'message' => 'Invalid password.'
        ])->assertStatus(403);
    }

    /**
     * Test if users can initiate a transfer to their own accounts
     * 
     * @return void
     */
    public function testUsersCanInitiateATransferToOwnAccount()
    {
        $user = factory(User::class)->create();

        $data = $this->transfer_data($user);

        $this->actingAs($user, 'users')->post("/api/account/", $data)->assertJson([
            'status' => 'success',
            'message' => 'Transfer successful'
        ])->assertStatus(200);
    }

    /**
     * Test if users can initiate a transfer to others
     * 
     * @return void
     */
    public function testUsersCanInitiateATransferToOthers()
    {
        $user = factory(User::class)->create();

        $data = array_merge($this->transfer_data($user), [
            'to_account' => 'others',
            'bank_name' => 'Access Bank',
            'account_name' => $this->faker->name,
            'narration' => $this->faker->sentence,
            'account_number' => '2099312312'
        ]);

        $this->actingAs($user, 'users')->post("/api/account/", $data)->assertJson([
            'status' => 'success',
            'message' => 'Transfer successful'
        ])->assertStatus(200);
    }

    /**
     * Validation errors
     *
     * @return void
     */
    public function testInitiateTransferValidations()
    {
        $user = factory(User::class)->create();

        $data = $this->transfer_data($user);

        //password required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['password' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The password field is required.'
        ])->assertStatus(422);

        //from account required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['from_account' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The from account field is required.'
        ])->assertStatus(422);

        //to account required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['to_account' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The to account field is required.'
        ])->assertStatus(422);

        //amount required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['amount' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The amount field is required.'
        ])->assertStatus(422);

        //amount numeric
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, ['amount' => 'sad']))->assertJson([
            'status' => 'error',
            'message' => 'The amount must be a number.'
        ])->assertStatus(422);

        //bank name required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The bank name field is required when to account is others.'
        ])->assertStatus(422);

        //account name required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others',
            'bank_name' => 'Access Bank'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The account name field is required when to account is others.'
        ])->assertStatus(422);

        //account name min
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others',
            'bank_name' => 'Access Bank',
            'account_name' => 'sa'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The account name must be at least 3 characters.'
        ])->assertStatus(422);

        //account number required
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others',
            'bank_name' => 'Access Bank',
            'account_name' => 'asfa'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The account number field is required when to account is others.'
        ])->assertStatus(422);

        //account number numeric
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others',
            'bank_name' => 'Access Bank',
            'account_name' => 'asfa',
            'account_number' =>  'ss'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The account number must be a number.'
        ])->assertStatus(422);

        //account number digits between 10
        $this->actingAs($user, 'users')->post("/api/account/", array_merge($data, [
            'to_account' => 'others',
            'bank_name' => 'Access Bank',
            'account_name' => 'asfa',
            'account_number' =>  '123'
        ]))->assertJson([
            'status' => 'error',
            'message' => 'The account number must be between 10 and 10 digits.'
        ])->assertStatus(422);
    }

    /**
     * Validation errors
     *
     * @return void
     */
    public function testAccountValidations()
    {
        $data = $this->data();

        $user = factory(User::class)->create();

        //account_type field required
        $this->actingAs($user, 'users')->post('/api/accounts/create', array_merge($data, ['account_type' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The account type field is required.'
        ])->assertStatus(422);

        //balance must be a number
        $this->actingAs($user, 'users')->post('/api/accounts/create', array_merge($data, ['balance' => 'amount']))->assertJson([
            'status' => 'error',
            'message' => 'The balance must be a number.'
        ])->assertStatus(422);
    }

    public function data()
    {
        return [
            'account_type' => Str::random(10)
        ];
    }

    public function transfer_data($user)
    {
        return [
            'from_account' => factory(Account::class)->create(['user_id' => $user->id])->account_number,
            'to_account' => factory(Account::class)->create(['user_id' => $user->id])->account_number,
            'amount' => 5000,
            'password' => 'password'
        ];
    }
}
