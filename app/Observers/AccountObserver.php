<?php

namespace App\Observers;

use App\Account;
use App\Http\Traits\CustomTraits;

class AccountObserver
{
    use CustomTraits;

    /**
     * Handle the account "created" event.
     *
     * @param  \App\Account  $account
     * @return void
     */
    public function creating(Account $account)
    {
        $account_number = $this->generateAccountNumber();

        while ($account->where('account_number', $account_number)->exists()) {
            $account_number = $this->generateAccountNumber();
        }

        $account->account_number = $account_number;
    }

    public function generateAccountNumber()
    {
        return "09" . $this->generateRandomNumber(8);
    }
}
