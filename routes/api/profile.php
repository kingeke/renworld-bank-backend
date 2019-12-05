<?php

//view logged in user profile
Route::get('/', 'ProfileController@user');

//update user profile
Route::put('/update-profile', 'ProfileController@updateProfile');

//change user password
Route::put('/change-password', 'ProfileController@changePassword');
