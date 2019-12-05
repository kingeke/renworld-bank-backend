<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'version' => '1.0'
    ]);
});

//website data
Route::get('/website', 'WebsiteController@index');

//auth routes
Route::group(['prefix' => 'auth'], function () {
    require base_path('routes/api/auth.php');
});


Route::group(['middleware' => 'auth:users'], function () {

    //user dashboard data
    Route::get('/dashboard', 'DashboardController@index');

    //account routes
    require base_path('routes/api/account.php');

    //profile routes
    Route::group(['prefix' => 'profile',], function () {
        require base_path('routes/api/profile.php');
    });

    //transactions routes
    require base_path('routes/api/transaction.php');
});
