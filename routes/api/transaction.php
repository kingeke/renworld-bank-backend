<?php

Route::group(['prefix' => 'transactions'], function () {

    //view all transactions
    Route::get('/', 'TransactionController@index');
});

Route::group(['prefix' => 'transaction/{transaction_ref}'], function () {

    //view a single transaction
    Route::get('/', 'TransactionController@show');
});
