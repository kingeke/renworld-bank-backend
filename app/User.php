<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Account;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token', 'id'];

    protected $appends = ['accounts'];

    public function getAccountsAttribute()
    {
        return $this->accounts()->where('active', true)->get()->toArray();
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function accounts()
    {
        return $this->hasMany('App\Account');
    }

    public function transactions()
    {
        return $this->hasManyThrough('App\Transaction', 'App\Account');
    }

    /** 
     * Debit the user's account
     * 
     * @param \App\Account $account,
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function debit_account(Account $account, $data)
    {
        return $this->update_account($account, [
            'type' => 'Debit',
            'narration' => $data['narration'],
            'amount' => $data['amount'],
        ]);
    }

    /** 
     * Credit the user's account
     * 
     * @param \App\Account $account,
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function credit_account(Account $account, $data)
    {
        return $this->update_account($account, [
            'type' => 'Credit',
            'narration' => $data['narration'],
            'amount' => $data['amount'],
        ]);
    }

    /** 
     * Make changes to the user's account and create a transaction
     * 
     * @param \App\Account $account,
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function update_account(Account $account, $data)
    {
        $account = $account->refresh();

        $previous_balance = $account->balance;

        $account->update(['balance' => ($data['type'] == 'Debit' ? $previous_balance - $data['amount'] : $previous_balance + $data['amount'])]);

        $account->transactions()->create([
            'method' => $data['method'] ?? "Web",
            'type' => $data['type'],
            'narration' => $data['narration'],
            'previous_balance' => $previous_balance,
            'amount' => $data['amount'],
            'current_balance' => $account->refresh()->balance,
        ]);

        return true;
    }
}
