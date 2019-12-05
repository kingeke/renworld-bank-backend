<?php

Route::group(['prefix' => 'accounts'], function () {

    //view all accounts
    Route::get('/', 'AccountController@index');

    //create a new account
    Route::post('/create', 'AccountController@create');
});

Route::group(['prefix' => 'account'], function () {

    //get an account
    Route::get('/{account_number}', 'AccountController@show');

    //fund account
    Route::post('/{account_number}', 'AccountController@fund_account');

    //close or open an account
    Route::put('/{account_number}', 'AccountController@update_account');

    //transfer money to any account
    Route::post('/', 'AccountController@initiate_transfer');
});
