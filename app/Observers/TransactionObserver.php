<?php

namespace App\Observers;

use App\Http\Traits\CustomTraits;
use App\Transaction;

class TransactionObserver
{
    use CustomTraits;
    /**
     * Handle the transaction "created" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function creating(Transaction $transaction)
    {
        $transaction_ref = $this->generateTransactionRef();

        while ($transaction->where('transaction_ref', $transaction_ref)->exists()) {
            $transaction_ref = $this->generateTransactionRef();
        }

        $transaction->transaction_ref = $transaction_ref;
    }

    public function generateTransactionRef()
    {
        return config('website.transaction_syntax') . now()->format('Ymd') . $this->generateRandomNumber(5) . $this->generateRandomCharacter(5);
    }
}
