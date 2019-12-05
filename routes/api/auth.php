<?php

//sign up
Route::post('/sign-up', 'AuthController@register');

//login
Route::post('/login', 'AuthController@login');

//log out
Route::post('/log-out', 'AuthController@logOut');
